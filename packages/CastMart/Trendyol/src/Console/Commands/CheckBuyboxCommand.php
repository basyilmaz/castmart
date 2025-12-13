<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Trendyol\Services\IntelligenceService;
use CastMart\Trendyol\Services\TrendyolService;
use CastMart\Trendyol\Models\BuyboxTracking;
use CastMart\Trendyol\Models\PriceRule;

class CheckBuyboxCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'trendyol:check-buybox {--account= : Belirli bir hesap ID\'si}';

    /**
     * The console command description.
     */
    protected $description = 'Trendyol ürünlerinin BuyBox durumunu kontrol et ve gerekli aksiyonları al';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 BuyBox kontrolü başlatılıyor...');

        $accountId = $this->option('account');

        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->when($accountId, fn($q) => $q->where('id', $accountId))
            ->get();

        if ($accounts->isEmpty()) {
            $this->warn('Aktif Trendyol hesabı bulunamadı.');
            return 0;
        }

        foreach ($accounts as $account) {
            $this->processAccount($account);
        }

        $this->info('✅ BuyBox kontrolü tamamlandı.');
        return 0;
    }

    protected function processAccount(MarketplaceAccount $account)
    {
        $this->line("📦 Hesap işleniyor: {$account->name}");

        try {
            $service = new TrendyolService($account);
            $intelligenceService = new IntelligenceService($account);

            // Trendyol'dan ürünleri çek
            $products = $service->getProducts();

            // Eğer products sayı ise veya boşsa
            if (!is_array($products) || empty($products)) {
                $this->line("  ⚠️ Ürün bulunamadı veya hatalı veri.");
                return;
            }

            $wonCount = 0;
            $lostCount = 0;
            $riskCount = 0;

            foreach ($products as $product) {
                // Ürün bir dizi değilse atla
                if (!is_array($product)) {
                    continue;
                }
                
                // BuyBox durumunu kontrol et
                $buyboxData = $this->checkProductBuybox($service, $product);


                if ($buyboxData) {
                    // Veritabanını güncelle
                    $intelligenceService->updateBuyboxTracking([$buyboxData]);

                    // Duruma göre sayaçları güncelle
                    $tracking = BuyboxTracking::where('marketplace_account_id', $account->id)
                        ->where('product_sku', $buyboxData['sku'])
                        ->first();

                    if ($tracking) {
                        match ($tracking->status) {
                            'won' => $wonCount++,
                            'lost' => $lostCount++,
                            'risk' => $riskCount++,
                            default => null,
                        };

                        // BuyBox kaybedildiyse uyarı oluştur
                        if ($tracking->status === 'lost') {
                            $intelligenceService->createBuyboxLostAlert(
                                $buyboxData['sku'],
                                $buyboxData['our_price'],
                                $buyboxData['competitor_price']
                            );

                            // Otomatik fiyat kurallarını uygula
                            $this->applyAutoRules($account, $intelligenceService, $tracking);
                        }
                    }
                }
            }

            $this->line("  ✓ Kazanılan: {$wonCount} | ✗ Kaybedilen: {$lostCount} | ⚠ Risk: {$riskCount}");

        } catch (\Exception $e) {
            $this->error("  ❌ Hata: " . $e->getMessage());
        }
    }

    protected function checkProductBuybox(TrendyolService $service, array $product): ?array
    {
        // Ürün bilgilerini al
        $sku = $product['stockCode'] ?? $product['sku'] ?? null;
        $barcode = $product['barcode'] ?? null;
        $ourPrice = $product['salePrice'] ?? $product['listPrice'] ?? 0;

        if (!$sku || !$ourPrice) {
            return null;
        }

        // Rakip fiyatını bul (scraping veya API ile)
        $competitorPrice = $this->getCompetitorPrice($barcode);

        return [
            'sku' => $sku,
            'barcode' => $barcode,
            'our_price' => $ourPrice,
            'competitor_price' => $competitorPrice,
            'competitor_seller' => null, // İleride eklenebilir
        ];
    }

    protected function getCompetitorPrice(?string $barcode): ?float
    {
        // Gerçek implementasyonda scraping veya API kullanılacak
        // Şimdilik simüle edilmiş veri
        if (!$barcode) {
            return null;
        }

        // Rastgele rakip fiyatı simüle et (gerçek implementasyonda kaldırılacak)
        return null;
    }

    protected function applyAutoRules(MarketplaceAccount $account, IntelligenceService $intelligenceService, BuyboxTracking $tracking)
    {
        $rules = PriceRule::where('marketplace_account_id', $account->id)
            ->where('is_active', true)
            ->where('trigger', 'competitor_cheaper')
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($rules as $rule) {
            $newPrice = $intelligenceService->applyPriceRule(
                $rule,
                $tracking->product_sku,
                $tracking->our_price,
                $tracking->competitor_price
            );

            if ($newPrice) {
                $this->line("    💰 Fiyat kuralı uygulandı: {$tracking->product_sku} -> {$newPrice}₺");

                // Gerçek implementasyonda Trendyol API ile fiyat güncellenir
                // $service->updatePrice($tracking->product_sku, $newPrice);

                break; // İlk eşleşen kuralı uygula
            }
        }
    }
}
