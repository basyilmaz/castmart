<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Trendyol\Services\TrendyolService;

class ImportProductsCommand extends Command
{
    protected $signature = 'trendyol:import-products {--account= : Belirli hesap ID}';
    protected $description = 'Trendyol ürünlerini marketplace_listings tablosuna aktar';

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

        $totalImported = 0;

        foreach ($accounts as $account) {
            $this->info("Hesap işleniyor: {$account->name}");

            try {
                $service = new TrendyolService($account);
                $page = 0;
                $imported = 0;

                do {
                    $products = $service->getProducts(['size' => 50, 'page' => $page]);
                    
                    foreach ($products['content'] ?? [] as $product) {
                        $existing = MarketplaceListing::where('account_id', $account->id)
                            ->where('external_id', $product['barcode'])
                            ->first();

                        if (!$existing) {
                            MarketplaceListing::create([
                                'account_id' => $account->id,
                                'external_id' => $product['barcode'],
                                'external_sku' => $product['stockCode'] ?? $product['barcode'],
                                'status' => $product['approved'] ? 'active' : 'pending',
                                'price' => $product['salePrice'] ?? 0,
                                'stock' => $product['quantity'] ?? 0,
                                'listing_data' => $product,
                            ]);
                            $imported++;
                        }
                    }

                    $page++;
                    $this->info("  → Sayfa {$page} işlendi...");

                } while (($products['page'] ?? 0) < ($products['totalPages'] ?? 0) - 1);

                $totalImported += $imported;
                $this->info("  → {$imported} ürün içe aktarıldı.");

            } catch (\Exception $e) {
                $this->error("  → Hata: {$e->getMessage()}");
            }
        }

        $this->info("Toplam {$totalImported} ürün içe aktarıldı.");

        return 0;
    }
}
