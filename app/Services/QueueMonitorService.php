<?php

namespace App\Services;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class QueueMonitorService
{
    /**
     * Tüm kuyrukların durumunu al
     */
    public function getQueueStatus(): array
    {
        $queues = config('queue.queues', ['default', 'high', 'low', 'emails', 'notifications']);
        $status = [];

        foreach ($queues as $queue) {
            $status[$queue] = [
                'size' => $this->getQueueSize($queue),
                'failed' => $this->getFailedCount($queue),
                'processing' => $this->getProcessingCount($queue),
            ];
        }

        return $status;
    }

    /**
     * Kuyruk boyutunu al
     */
    public function getQueueSize(string $queue): int
    {
        $connection = config('queue.default');

        if ($connection === 'redis') {
            return Redis::llen("queues:{$queue}");
        }

        if ($connection === 'database') {
            return \DB::table(config('queue.connections.database.table', 'jobs'))
                ->where('queue', $queue)
                ->whereNull('reserved_at')
                ->count();
        }

        return 0;
    }

    /**
     * Başarısız job sayısı
     */
    public function getFailedCount(?string $queue = null): int
    {
        $query = \DB::table(config('queue.failed.table', 'failed_jobs'));
        
        if ($queue) {
            $query->where('queue', $queue);
        }

        return $query->count();
    }

    /**
     * İşlenen job sayısı
     */
    public function getProcessingCount(string $queue): int
    {
        $connection = config('queue.default');

        if ($connection === 'redis') {
            return Redis::llen("queues:{$queue}:reserved");
        }

        if ($connection === 'database') {
            return \DB::table(config('queue.connections.database.table', 'jobs'))
                ->where('queue', $queue)
                ->whereNotNull('reserved_at')
                ->count();
        }

        return 0;
    }

    /**
     * Tüm kuyrukları temizle
     */
    public function clearQueue(string $queue): int
    {
        $connection = config('queue.default');
        $count = 0;

        if ($connection === 'redis') {
            $count = Redis::llen("queues:{$queue}");
            Redis::del("queues:{$queue}");
        }

        if ($connection === 'database') {
            $count = \DB::table(config('queue.connections.database.table', 'jobs'))
                ->where('queue', $queue)
                ->delete();
        }

        return $count;
    }

    /**
     * Başarısız job'ları temizle
     */
    public function clearFailedJobs(?string $queue = null): int
    {
        $query = \DB::table(config('queue.failed.table', 'failed_jobs'));
        
        if ($queue) {
            $query->where('queue', $queue);
        }

        return $query->delete();
    }

    /**
     * Başarısız job'ları yeniden kuyruğa ekle
     */
    public function retryFailedJobs(?string $queue = null, int $limit = 100): int
    {
        $query = \DB::table(config('queue.failed.table', 'failed_jobs'));
        
        if ($queue) {
            $query->where('queue', $queue);
        }

        $failedJobs = $query->limit($limit)->get();
        $retried = 0;

        foreach ($failedJobs as $job) {
            try {
                \Artisan::call('queue:retry', ['id' => [$job->uuid ?? $job->id]]);
                $retried++;
            } catch (\Exception $e) {
                // Skip
            }
        }

        return $retried;
    }

    /**
     * Queue metrikleri (monitoring için)
     */
    public function getMetrics(): array
    {
        $cacheKey = 'queue:metrics';
        
        return Cache::remember($cacheKey, 60, function () {
            $status = $this->getQueueStatus();
            
            $totalSize = 0;
            $totalFailed = 0;
            $totalProcessing = 0;

            foreach ($status as $queueStatus) {
                $totalSize += $queueStatus['size'];
                $totalFailed += $queueStatus['failed'];
                $totalProcessing += $queueStatus['processing'];
            }

            return [
                'total_pending' => $totalSize,
                'total_failed' => $totalFailed,
                'total_processing' => $totalProcessing,
                'queues' => $status,
                'workers' => $this->getWorkerCount(),
                'last_check' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Worker sayısını al (yaklaşık)
     */
    protected function getWorkerCount(): int
    {
        // Redis'te worker heartbeat'lerini say
        if (config('queue.default') === 'redis') {
            $pattern = 'horizon:*:master:*';
            $keys = Redis::keys($pattern);
            return count($keys);
        }

        return 0;
    }

    /**
     * Sağlık kontrolü
     */
    public function healthCheck(): array
    {
        $metrics = $this->getMetrics();
        $issues = [];

        // Çok fazla bekleyen job
        if ($metrics['total_pending'] > 1000) {
            $issues[] = 'High pending job count: ' . $metrics['total_pending'];
        }

        // Çok fazla başarısız job
        if ($metrics['total_failed'] > 100) {
            $issues[] = 'High failed job count: ' . $metrics['total_failed'];
        }

        // Worker yok
        if ($metrics['workers'] === 0 && $metrics['total_pending'] > 0) {
            $issues[] = 'No active workers but pending jobs exist';
        }

        return [
            'healthy' => empty($issues),
            'issues' => $issues,
            'metrics' => $metrics,
        ];
    }
}
