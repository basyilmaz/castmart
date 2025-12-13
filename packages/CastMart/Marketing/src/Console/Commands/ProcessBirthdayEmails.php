<?php

namespace CastMart\Marketing\Console\Commands;

use Illuminate\Console\Command;
use CastMart\Marketing\Services\EmailMarketingService;

class ProcessBirthdayEmails extends Command
{
    protected $signature = 'marketing:birthday-emails';
    protected $description = 'Bugün doğum günü olan müşterilere email gönder';

    public function handle(EmailMarketingService $service): int
    {
        $this->info('Doğum günü emailleri işleniyor...');

        $count = $service->processBirthdayEmails();

        $this->info("Gönderilen email sayısı: {$count}");

        return Command::SUCCESS;
    }
}
