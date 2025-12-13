<?php

namespace CastMart\Trendyol\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Trendyol\Services\TrendyolService;

class SyncQuestionsCommand extends Command
{
    protected $signature = 'trendyol:sync-questions {--account= : Belirli hesap ID}';
    protected $description = 'Trendyol müşteri sorularını senkronize et';

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
                $count = $service->syncQuestions();
                $totalSynced += $count;

                $this->info("  → {$count} yeni soru senkronize edildi.");
            } catch (\Exception $e) {
                $this->error("  → Hata: {$e->getMessage()}");
            }
        }

        $this->info("Toplam {$totalSynced} soru senkronize edildi.");

        return 0;
    }
}
