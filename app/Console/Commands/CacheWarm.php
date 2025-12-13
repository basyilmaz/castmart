<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class CacheWarm extends Command
{
    protected $signature = 'cache:warm';
    protected $description = 'Cache warming - sık kullanılan verileri önceden yükle';

    public function handle(CacheService $cacheService): int
    {
        $this->info('Cache warming başlıyor...');

        $warmed = $cacheService->warmCache();

        foreach ($warmed as $item) {
            $this->line("✓ {$item} cache'lendi");
        }

        $this->info('Cache warming tamamlandı: ' . count($warmed) . ' öğe');

        return Command::SUCCESS;
    }
}
