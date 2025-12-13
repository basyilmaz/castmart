<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Trendyol\Services\TrendyolService;

class SyncOrdersCommand extends Command
{
    protected $signature = 'trendyol:sync-orders {--account= : Belirli hesap ID}';
    protected $description = 'Trendyol siparişlerini senkronize et';

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

        $totalSynced = 0;

        foreach ($accounts as $account) {
            $this->info("Hesap işleniyor: {$account->name}");

            try {
                $service = new TrendyolService($account);
                $count = $service->syncOrders();
                $totalSynced += $count;

                $this->info("  → {$count} yeni sipariş senkronize edildi.");
            } catch (\Exception $e) {
                $this->error("  → Hata: {$e->getMessage()}");
            }
        }

        $this->info("Toplam {$totalSynced} sipariş senkronize edildi.");

        return 0;
    }
}
