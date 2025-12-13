<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Trendyol\Services\IntelligenceService;
use CastMart\Trendyol\Services\TrendyolService;

class CheckStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'trendyol:check-stock {--threshold=5 : Kritik stok eşiği}';

    /**
     * The console command description.
     */
    protected $description = 'Trendyol ürünlerinin stok durumunu kontrol et ve kritik olan ürünler için uyarı oluştur';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = (int) $this->option('threshold');
        $this->info("📦 Stok kontrolü başlatılıyor (eşik: {$threshold} adet)...");

        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();

        if ($accounts->isEmpty()) {
            $this->warn('Aktif Trendyol hesabı bulunamadı.');
            return 0;
        }

        foreach ($accounts as $account) {
            $this->processAccount($account, $threshold);
        }

        $this->info('✅ Stok kontrolü tamamlandı.');
        return 0;
    }

    protected function processAccount(MarketplaceAccount $account, int $threshold)
    {
        $this->line("📦 Hesap işleniyor: {$account->name}");

        try {
            $service = new TrendyolService($account);
            $intelligenceService = new IntelligenceService($account);

            // Trendyol'dan ürünleri çek
            $products = $service->getProducts();

            if (empty($products)) {
                $this->line("  ⚠️ Ürün bulunamadı.");
                return;
            }

            $criticalCount = 0;

            foreach ($products as $product) {
                $sku = $product['stockCode'] ?? $product['sku'] ?? null;
                $stock = $product['quantity'] ?? 0;

                if (!$sku) continue;

                // Stok kritik seviyenin altındaysa
                if ($stock <= $threshold && $stock > 0) {
                    $criticalCount++;

                    // Günlük ortalama satış tahmini (gerçek implementasyonda hesaplanacak)
                    $dailySales = $this->estimateDailySales($sku);

                    // Uyarı oluştur
                    $intelligenceService->createStockCriticalAlert($sku, $stock, $dailySales);

                    $daysLeft = $dailySales > 0 ? round($stock / $dailySales, 1) : '∞';
                    $this->line("  ⚠️ {$sku}: {$stock} adet kaldı (~{$daysLeft} gün)");
                }
            }

            $this->line("  📊 Kritik stoklu ürün sayısı: {$criticalCount}");

        } catch (\Exception $e) {
            $this->error("  ❌ Hata: " . $e->getMessage());
        }
    }

    protected function estimateDailySales(string $sku): float
    {
        // Gerçek implementasyonda son 30 günlük satış ortalaması hesaplanacak
        // Şimdilik simüle edilmiş veri
        return rand(5, 20) / 10; // 0.5 - 2.0 arası
    }
}
