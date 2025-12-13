<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Trendyol\Services\TrendyolService;

class SyncStockCommand extends Command
{
    protected $signature = 'trendyol:sync-stock {--account= : Belirli hesap ID}';
    protected $description = 'CastMart stok ve fiyatlarını Trendyol\'a senkronize et';

    public function handle()
    {
        $accountId = $this->option('account');

        $accounts = $accountId
            ? MarketplaceAccount::where('id', $accountId)->get()
            : MarketplaceAccount::marketplace('trendyol')->active()->get();

        if ($accounts->isEmpty()) {
            $this->error('Aktif Trendyol hesabı bulunamadı.');
            return 1;
        }

        foreach ($accounts as $account) {
            $this->info("Hesap işleniyor: {$account->name}");

            try {
                $service = new TrendyolService($account);
                
                $listings = MarketplaceListing::where('account_id', $account->id)
                    ->whereNotNull('product_id')
                    ->with('product')
                    ->get();

                if ($listings->isEmpty()) {
                    $this->warn("  → Eşleştirilmiş ürün yok.");
                    continue;
                }

                $items = [];
                foreach ($listings as $listing) {
                    if ($listing->product) {
                        $items[] = [
                            'barcode' => $listing->external_id,
                            'quantity' => $listing->product->totalQuantity(),
                            'salePrice' => $listing->product->price,
                            'listPrice' => $listing->product->price,
                        ];
                    }
                }

                if (empty($items)) {
                    $this->warn("  → Güncellenecek ürün yok.");
                    continue;
                }

                $result = $service->updateInventory($items);

                if (isset($result['batchRequestId'])) {
                    $this->info("  → " . count($items) . " ürün güncellendi. Batch: {$result['batchRequestId']}");
                } else {
                    $this->warn("  → Güncelleme başarısız: " . json_encode($result));
                }
            } catch (\Exception $e) {
                $this->error("  → Hata: {$e->getMessage()}");
            }
        }

        return 0;
    }
}
