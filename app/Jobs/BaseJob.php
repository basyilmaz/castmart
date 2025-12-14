<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry sayısı
     */
    public int $tries = 3;

    /**
     * Timeout (saniye)
     */
    public int $timeout = 60;

    /**
     * Retry aralığı (saniye)
     */
    public int $backoff = 30;

    /**
     * Unique job süresi (saniye)
     */
    public int $uniqueFor = 300;

    /**
     * Job başarısız olduğunda
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job failed', [
            'job' => static::class,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Kritik hataları bildir
        if (method_exists($this, 'notifyFailure')) {
            $this->notifyFailure($exception);
        }
    }

    /**
     * Job başlamadan önce
     */
    public function middleware(): array
    {
        return [
            new \Illuminate\Queue\Middleware\WithoutOverlapping($this->getUniqueId()),
            new \Illuminate\Queue\Middleware\RateLimited('jobs'),
        ];
    }

    /**
     * Unique ID (override edilebilir)
     */
    protected function getUniqueId(): string
    {
        return static::class;
    }
}
