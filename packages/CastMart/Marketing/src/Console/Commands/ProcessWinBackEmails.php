<?php

namespace CastMart\Marketing\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketing\Services\EmailMarketingService;

class ProcessWinBackEmails extends Command
{
    protected $signature = 'marketing:win-back';
    protected $description = 'İnaktif müşterilere win-back emaili gönder';

    public function handle(EmailMarketingService $service): int
    {
        $this->info('Win-back emailleri işleniyor...');

        $count = $service->processWinBackEmails();

        $this->info("Gönderilen email sayısı: {$count}");

        return Command::SUCCESS;
    }
}
