<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class ProductionHealthCheck extends Command
{
    protected $signature = 'health:check 
                            {--slack= : Slack webhook URL for notifications}
                            {--json : Output as JSON}';
                            
    protected $description = 'Production ortam sağlık kontrolü';

    protected array $checks = [];
    protected bool $allPassed = true;

    public function handle(): int
    {
        $this->info('🔍 Production Health Check başlatılıyor...');
        $this->newLine();

        // Database
        $this->checkDatabase();
        
        // Redis
        $this->checkRedis();
        
        // Queue
        $this->checkQueue();
        
        // Storage
        $this->checkStorage();
        
        // External Services
        $this->checkExternalServices();
        
        // SSL
        $this->checkSsl();
        
        // Disk Space
        $this->checkDiskSpace();
        
        // Memory
        $this->checkMemory();

        // Results
        $this->displayResults();

        // Slack notification
        if ($this->option('slack') && !$this->allPassed) {
            $this->notifySlack();
        }

        return $this->allPassed ? 0 : 1;
    }

    protected function checkDatabase(): void
    {
        $this->info('📊 Database kontrolü...');
        
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $time = round((microtime(true) - $start) * 1000, 2);
            
            $this->addCheck('Database Connection', true, "{$time}ms");
            
            // Table count
            $tables = DB::select('SHOW TABLES');
            $this->addCheck('Database Tables', true, count($tables) . ' tablo');
            
        } catch (\Exception $e) {
            $this->addCheck('Database Connection', false, $e->getMessage());
        }
    }

    protected function checkRedis(): void
    {
        $this->info('🔴 Redis kontrolü...');
        
        try {
            $start = microtime(true);
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            $time = round((microtime(true) - $start) * 1000, 2);
            
            if ($value === 'ok') {
                $this->addCheck('Redis Connection', true, "{$time}ms");
            } else {
                $this->addCheck('Redis Connection', false, 'Değer okunamadı');
            }
        } catch (\Exception $e) {
            $this->addCheck('Redis Connection', false, $e->getMessage());
        }
    }

    protected function checkQueue(): void
    {
        $this->info('📥 Queue kontrolü...');
        
        try {
            $failed = DB::table('failed_jobs')->count();
            $pending = DB::table('jobs')->count();
            
            $this->addCheck('Queue Failed Jobs', $failed < 100, "{$failed} başarısız job");
            $this->addCheck('Queue Pending Jobs', $pending < 1000, "{$pending} bekleyen job");
            
        } catch (\Exception $e) {
            $this->addCheck('Queue Check', false, $e->getMessage());
        }
    }

    protected function checkStorage(): void
    {
        $this->info('💾 Storage kontrolü...');
        
        $storagePath = storage_path('app/public');
        
        if (is_writable($storagePath)) {
            $this->addCheck('Storage Writable', true);
        } else {
            $this->addCheck('Storage Writable', false, 'Yazılabilir değil');
        }

        // Storage link
        $publicStorage = public_path('storage');
        if (is_link($publicStorage)) {
            $this->addCheck('Storage Link', true);
        } else {
            $this->addCheck('Storage Link', false, 'Symlink yok');
        }
    }

    protected function checkExternalServices(): void
    {
        $this->info('🌐 External servis kontrolü...');
        
        // Trendyol API
        try {
            $response = Http::timeout(5)->get('https://api.trendyol.com/sapigw/');
            $this->addCheck('Trendyol API', $response->status() < 500, "HTTP {$response->status()}");
        } catch (\Exception $e) {
            $this->addCheck('Trendyol API', false, 'Erişilemiyor');
        }

        // iyzico API
        try {
            $response = Http::timeout(5)->get('https://api.iyzipay.com/');
            $this->addCheck('iyzico API', $response->status() < 500, "HTTP {$response->status()}");
        } catch (\Exception $e) {
            $this->addCheck('iyzico API', false, 'Erişilemiyor');
        }
    }

    protected function checkSsl(): void
    {
        $this->info('🔐 SSL kontrolü...');
        
        $url = config('app.url');
        
        if (str_starts_with($url, 'https://')) {
            try {
                $response = Http::timeout(5)->get($url);
                $this->addCheck('SSL Certificate', $response->successful(), 'HTTPS aktif');
            } catch (\Exception $e) {
                $this->addCheck('SSL Certificate', false, $e->getMessage());
            }
        } else {
            $this->addCheck('SSL Certificate', false, 'HTTPS kullanılmıyor');
        }
    }

    protected function checkDiskSpace(): void
    {
        $this->info('💿 Disk alanı kontrolü...');
        
        $freeBytes = disk_free_space('/');
        $totalBytes = disk_total_space('/');
        $usedPercentage = round((1 - ($freeBytes / $totalBytes)) * 100, 1);
        $freeGb = round($freeBytes / 1024 / 1024 / 1024, 1);
        
        $this->addCheck('Disk Space', $usedPercentage < 90, "Kullanım: {$usedPercentage}%, Boş: {$freeGb}GB");
    }

    protected function checkMemory(): void
    {
        $this->info('🧠 Memory kontrolü...');
        
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 1);
        
        $this->addCheck('PHP Memory', true, "Kullanım: {$memoryUsage}MB, Limit: {$memoryLimit}");
    }

    protected function addCheck(string $name, bool $passed, string $detail = ''): void
    {
        $this->checks[] = [
            'name' => $name,
            'passed' => $passed,
            'detail' => $detail,
        ];

        if (!$passed) {
            $this->allPassed = false;
        }

        $icon = $passed ? '✅' : '❌';
        $status = $passed ? 'OK' : 'FAIL';
        $detailStr = $detail ? " ({$detail})" : '';
        
        $this->line("  {$icon} {$name}: {$status}{$detailStr}");
    }

    protected function displayResults(): void
    {
        $this->newLine();
        
        $passed = collect($this->checks)->where('passed', true)->count();
        $total = count($this->checks);
        
        if ($this->allPassed) {
            $this->info("✅ Tüm kontroller başarılı! ({$passed}/{$total})");
        } else {
            $this->error("❌ Bazı kontroller başarısız! ({$passed}/{$total})");
        }

        if ($this->option('json')) {
            $this->newLine();
            $this->line(json_encode([
                'all_passed' => $this->allPassed,
                'checks' => $this->checks,
                'timestamp' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT));
        }
    }

    protected function notifySlack(): void
    {
        $webhookUrl = $this->option('slack');
        
        $failedChecks = collect($this->checks)
            ->where('passed', false)
            ->map(fn($c) => "• {$c['name']}: {$c['detail']}")
            ->join("\n");

        try {
            Http::post($webhookUrl, [
                'text' => "🚨 *CastMart Health Check Failed*\n\n{$failedChecks}",
            ]);
            $this->info('📢 Slack bildirimi gönderildi.');
        } catch (\Exception $e) {
            $this->error('Slack bildirimi gönderilemedi.');
        }
    }
}
