<?php

namespace CastMart\Marketing\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketing\Services\EmailMarketingService;

class ProcessAbandonedCarts extends Command
{
    protected $signature = 'marketing:abandoned-carts';
    protected $description = 'Terk edilen sepetler için hatırlatma emaili gönder';

    public function handle(EmailMarketingService $service): int
    {
        $this->info('Terk edilen sepetler işleniyor...');

        $count = $service->processAbandonedCarts();

        $this->info("Gönderilen email sayısı: {$count}");

        return Command::SUCCESS;
    }
}
