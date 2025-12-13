<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Trendyol\Models\IntelligenceAlert;
use CastMart\Trendyol\Models\BuyboxTracking;
use CastMart\Trendyol\Services\IntelligenceService;

class GenerateAlertsCommand extends Command
{
    protected $signature = 'trendyol:generate-alerts';
    protected $description = 'Akıllı uyarıları analiz et ve oluştur';

    public function handle(): int
    {
        $this->info('🔔 Akıllı uyarı analizi başlatılıyor...');
        
        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();

        if ($accounts->isEmpty()) {
            $this->warn('Aktif Trendyol hesabı bulunamadı.');
            return 0;
        }

        $totalAlerts = 0;

        foreach ($accounts as $account) {
            $this->line("📦 Hesap: {$account->name}");
            $alertsCreated = 0;

            // 1. BuyBox Kaybı Uyarıları
            $alertsCreated += $this->checkBuyboxLoss($account);

            // 2. Kritik Stok Uyarıları
            $alertsCreated += $this->checkCriticalStock($account);

            // 3. Satış Düşüşü Uyarıları
            $alertsCreated += $this->checkSalesDecline($account);

            // 4. Fırsat Uyarıları
            $alertsCreated += $this->checkOpportunities($account);

            // 5. Kar Marjı Uyarıları
            $alertsCreated += $this->checkProfitMargin($account);

            $this->info("   ✅ {$alertsCreated} yeni uyarı oluşturuldu.");
            $totalAlerts += $alertsCreated;
        }

        $this->newLine();
        $this->info("🎉 Toplam {$totalAlerts} uyarı oluşturuldu.");
        
        return 0;
    }

    protected function checkBuyboxLoss(MarketplaceAccount $account): int
    {
        $count = 0;
        
        $lostProducts = BuyboxTracking::where('marketplace_account_id', $account->id)
            ->where('status', 'lost')
            ->where('last_checked_at', '>=', now()->subHours(1))
            ->get();

        foreach ($lostProducts as $product) {
            // Aynı SKU için son 24 saatte uyarı var mı?
            $existingAlert = IntelligenceAlert::where('marketplace_account_id', $account->id)
                ->where('product_sku', $product->product_sku)
                ->where('category', 'buybox')
                ->where('created_at', '>=', now()->subHours(24))
                ->where('is_dismissed', false)
                ->exists();

            if (!$existingAlert) {
                IntelligenceAlert::create([
                    'marketplace_account_id' => $account->id,
                    'type' => 'critical',
                    'category' => 'buybox',
                    'title' => 'BuyBox Kaybedildi: ' . $product->product_sku,
                    'description' => sprintf(
                        'Rakip fiyat: %.2f₺ (Senin fiyatın: %.2f₺)',
                        $product->competitor_price,
                        $product->our_price
                    ),
                    'product_sku' => $product->product_sku,
                    'action_type' => 'update_price',
                    'data' => [
                        'our_price' => $product->our_price,
                        'competitor_price' => $product->competitor_price,
                        'difference' => round($product->our_price - $product->competitor_price, 2),
                    ],
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            $this->line("   → BuyBox kaybı: {$count} uyarı");
        }

        return $count;
    }

    protected function checkCriticalStock(MarketplaceAccount $account): int
    {
        $count = 0;
        
        // Düşük stoklu ürünleri bul
        $listings = MarketplaceListing::where('account_id', $account->id)
            ->whereHas('product', function($q) {
                $q->whereHas('inventories', function($q2) {
                    $q2->where('qty', '<=', 5)->where('qty', '>', 0);
                });
            })
            ->with(['product.inventories'])
            ->get();

        foreach ($listings as $listing) {
            $stock = $listing->product->inventories->sum('qty');
            $sku = $listing->external_id ?? $listing->product->sku ?? 'N/A';
            
            // Aynı SKU için son 24 saatte uyarı var mı?
            $existingAlert = IntelligenceAlert::where('marketplace_account_id', $account->id)
                ->where('product_sku', $sku)
                ->where('category', 'stock')
                ->where('created_at', '>=', now()->subHours(24))
                ->where('is_dismissed', false)
                ->exists();

            if (!$existingAlert) {
                IntelligenceAlert::create([
                    'marketplace_account_id' => $account->id,
                    'type' => $stock <= 2 ? 'critical' : 'warning',
                    'category' => 'stock',
                    'title' => 'Stok Kritik: ' . $sku,
                    'description' => "{$stock} adet kaldı",
                    'product_sku' => $sku,
                    'action_type' => 'update_stock',
                    'data' => ['current_stock' => $stock],
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            $this->line("   → Kritik stok: {$count} uyarı");
        }

        return $count;
    }

    protected function checkSalesDecline(MarketplaceAccount $account): int
    {
        $count = 0;
        
        // Son 7 gün vs önceki 7 gün satış karşılaştırması
        $currentWeekSales = MarketplaceOrder::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
            
        $previousWeekSales = MarketplaceOrder::where('account_id', $account->id)
            ->where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        if ($previousWeekSales > 0) {
            $changePercent = (($currentWeekSales - $previousWeekSales) / $previousWeekSales) * 100;
            
            if ($changePercent <= -30) {
                // Son 24 saatte aynı uyarı var mı?
                $existingAlert = IntelligenceAlert::where('marketplace_account_id', $account->id)
                    ->where('category', 'sales')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->where('is_dismissed', false)
                    ->exists();

                if (!$existingAlert) {
                    IntelligenceAlert::create([
                        'marketplace_account_id' => $account->id,
                        'type' => 'warning',
                        'category' => 'sales',
                        'title' => 'Satışlarda Düşüş Tespit Edildi',
                        'description' => sprintf(
                            'Son 7 gün: %d adet, Önceki 7 gün: %d adet (%.1f%% düşüş)',
                            $currentWeekSales,
                            $previousWeekSales,
                            abs($changePercent)
                        ),
                        'action_type' => 'analyze',
                        'data' => [
                            'current_week' => $currentWeekSales,
                            'previous_week' => $previousWeekSales,
                            'change_percent' => round($changePercent, 1),
                        ],
                    ]);
                    $count++;
                    $this->line("   → Satış düşüşü: {$count} uyarı");
                }
            }
        }

        return $count;
    }

    protected function checkOpportunities(MarketplaceAccount $account): int
    {
        $count = 0;
        
        // Rakip stok tükenen ürünler
        $opportunityProducts = BuyboxTracking::where('marketplace_account_id', $account->id)
            ->where('status', 'won')
            ->whereNotNull('competitor_price')
            ->where('competitor_price', 0) // Rakip stokta yok
            ->get();

        foreach ($opportunityProducts as $product) {
            $existingAlert = IntelligenceAlert::where('marketplace_account_id', $account->id)
                ->where('product_sku', $product->product_sku)
                ->where('type', 'opportunity')
                ->where('created_at', '>=', now()->subHours(48))
                ->where('is_dismissed', false)
                ->exists();

            if (!$existingAlert) {
                IntelligenceAlert::create([
                    'marketplace_account_id' => $account->id,
                    'type' => 'opportunity',
                    'category' => 'buybox',
                    'title' => 'Rakip Stok Tükendi: ' . $product->product_sku,
                    'description' => 'Fiyat artırma fırsatı!',
                    'product_sku' => $product->product_sku,
                    'action_type' => 'review',
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            $this->line("   → Fırsatlar: {$count} uyarı");
        }

        return $count;
    }

    protected function checkProfitMargin(MarketplaceAccount $account): int
    {
        // Kar marjı düşük ürünler için uyarı (geliştirilecek)
        return 0;
    }
}
