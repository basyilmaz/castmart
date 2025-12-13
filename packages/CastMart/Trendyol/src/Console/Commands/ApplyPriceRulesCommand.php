<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Trendyol\Models\PriceRule;
use CastMart\Trendyol\Models\BuyboxTracking;
use CastMart\Trendyol\Services\TrendyolService;
use CastMart\Trendyol\Services\IntelligenceService;

class ApplyPriceRulesCommand extends Command
{
    protected $signature = 'trendyol:apply-price-rules {--dry-run : Sadece simüle et, uygulama}';
    protected $description = 'Aktif fiyat kurallarını uygula ve BuyBox kayıplarını düzelt';

    public function handle(): int
    {
        $this->info('🎯 Fiyat kuralları uygulanıyor...');
        
        $dryRun = $this->option('dry-run');
        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();

        if ($accounts->isEmpty()) {
            $this->warn('Aktif Trendyol hesabı bulunamadı.');
            return 0;
        }

        $totalUpdates = 0;

        foreach ($accounts as $account) {
            $this->line("📦 Hesap: {$account->name}");
            
            $service = new TrendyolService($account);
            $intelligenceService = new IntelligenceService($account);
            
            // Aktif fiyat kurallarını getir
            $rules = PriceRule::where('marketplace_account_id', $account->id)
                ->where('is_active', true)
                ->orderBy('priority', 'desc')
                ->get();

            if ($rules->isEmpty()) {
                $this->line('   → Aktif fiyat kuralı yok.');
                continue;
            }

            // BuyBox kaybedilen ürünleri getir
            $lostProducts = BuyboxTracking::where('marketplace_account_id', $account->id)
                ->where('status', 'lost')
                ->get();

            $this->line("   → {$lostProducts->count()} ürün BuyBox kaybetmiş durumda.");

            $priceUpdates = [];

            foreach ($lostProducts as $product) {
                // En uygun kuralı bul
                $applicableRule = $this->findApplicableRule($rules, $product);
                
                if (!$applicableRule) {
                    continue;
                }

                $newPrice = $intelligenceService->applyPriceRule(
                    $applicableRule,
                    $product->product_sku,
                    $product->our_price,
                    $product->competitor_price
                );

                if ($newPrice && $newPrice >= ($applicableRule->min_price ?? 0)) {
                    // Min/max kontrolü
                    if ($applicableRule->max_price && $newPrice > $applicableRule->max_price) {
                        $newPrice = $applicableRule->max_price;
                    }
                    if ($applicableRule->min_price && $newPrice < $applicableRule->min_price) {
                        $newPrice = $applicableRule->min_price;
                    }

                    $priceUpdates[] = [
                        'barcode' => $product->barcode,
                        'sku' => $product->product_sku,
                        'old_price' => $product->our_price,
                        'new_price' => $newPrice,
                        'rule' => $applicableRule->name,
                    ];

                    $this->line(sprintf(
                        '   ✓ %s: %.2f₺ → %.2f₺ (Kural: %s)',
                        $product->product_sku,
                        $product->our_price,
                        $newPrice,
                        $applicableRule->name
                    ));
                }
            }

            if (!empty($priceUpdates) && !$dryRun) {
                // Trendyol'a fiyat güncellemesi gönder
                $items = array_map(function($update) {
                    return [
                        'barcode' => $update['barcode'],
                        'salePrice' => $update['new_price'],
                        'listPrice' => $update['new_price'],
                    ];
                }, $priceUpdates);

                try {
                    $result = $service->updateInventory($items);
                    $this->info("   ✅ {$account->name}: " . count($priceUpdates) . " ürün fiyatı güncellendi.");
                    $totalUpdates += count($priceUpdates);
                } catch (\Exception $e) {
                    $this->error("   ❌ Hata: " . $e->getMessage());
                }
            } elseif ($dryRun) {
                $this->warn("   ⚠️ Dry-run modu: " . count($priceUpdates) . " fiyat güncellemesi simüle edildi.");
            }
        }

        $this->newLine();
        $this->info("🎉 Toplam {$totalUpdates} ürün fiyatı güncellendi.");
        
        return 0;
    }

    protected function findApplicableRule($rules, BuyboxTracking $product): ?PriceRule
    {
        foreach ($rules as $rule) {
            // SKU filtresi varsa kontrol et
            if ($rule->sku_filter) {
                $skuPatterns = explode(',', $rule->sku_filter);
                $matched = false;
                foreach ($skuPatterns as $pattern) {
                    if (str_contains($product->product_sku, trim($pattern))) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) continue;
            }

            // Kategori filtresi varsa kontrol et (geliştirilecek)
            // ...

            return $rule;
        }

        return null;
    }
}
