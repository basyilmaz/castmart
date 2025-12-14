<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployProduction extends Command
{
    protected $signature = 'deploy:production 
                            {--skip-migrations : Skip database migrations}
                            {--skip-cache : Skip cache generation}
                            {--dry-run : Show what would be done}';
                            
    protected $description = 'Production deployment script';

    public function handle(): int
    {
        $this->info('🚀 CastMart Production Deployment');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('⚠️  DRY RUN MODE - Değişiklik yapılmayacak');
            $this->newLine();
        }

        // 1. Maintenance mode
        $this->step('1. Bakım modunu aktifleştir');
        $this->runCommand('down', ['--render' => 'maintenance']);

        // 2. Clear old caches
        $this->step('2. Cache temizle');
        $this->runCommand('cache:clear');
        $this->runCommand('config:clear');
        $this->runCommand('route:clear');
        $this->runCommand('view:clear');

        // 3. Run migrations
        if (!$this->option('skip-migrations')) {
            $this->step('3. Migration çalıştır');
            $this->runCommand('migrate', ['--force' => true]);
        } else {
            $this->step('3. Migration atlandı');
        }

        // 4. Generate new caches
        if (!$this->option('skip-cache')) {
            $this->step('4. Cache oluştur');
            $this->runCommand('config:cache');
            $this->runCommand('route:cache');
            $this->runCommand('view:cache');
            $this->runCommand('event:cache');
        } else {
            $this->step('4. Cache oluşturma atlandı');
        }

        // 5. Storage link
        $this->step('5. Storage link kontrolü');
        if (!is_link(public_path('storage'))) {
            $this->runCommand('storage:link');
        } else {
            $this->line('  ✓ Storage link mevcut');
        }

        // 6. Queue restart
        $this->step('6. Queue worker yeniden başlat');
        $this->runCommand('queue:restart');

        // 7. Warm up cache
        $this->step('7. Cache ısıtma');
        $this->runCommand('cache:warm');

        // 8. Disable maintenance mode
        $this->step('8. Bakım modunu kapat');
        $this->runCommand('up');

        // 9. Health check
        $this->step('9. Health check');
        $result = Artisan::call('health:check');

        $this->newLine();
        
        if ($result === 0) {
            $this->info('✅ Deployment başarıyla tamamlandı!');
        } else {
            $this->error('⚠️  Deployment tamamlandı ama bazı kontroller başarısız!');
        }

        return $result;
    }

    protected function step(string $message): void
    {
        $this->newLine();
        $this->line("<fg=cyan;options=bold>{$message}</>");
    }

    protected function runCommand(string $command, array $params = []): void
    {
        if ($this->option('dry-run')) {
            $this->line("  → php artisan {$command} " . json_encode($params));
            return;
        }

        $this->line("  → {$command}");
        
        try {
            Artisan::call($command, $params);
            $output = trim(Artisan::output());
            if ($output) {
                collect(explode("\n", $output))->each(fn($line) => $this->line("    {$line}"));
            }
        } catch (\Exception $e) {
            $this->error("  ✗ Hata: {$e->getMessage()}");
        }
    }
}
