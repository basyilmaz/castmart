<?php

use App\Http\Middleware\EncryptCookies;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Webkul\Core\Http\Middleware\SecureHeaders;
use Webkul\Installer\Http\Middleware\CanInstall;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Remove the default Laravel middleware that prevents requests during maintenance mode. There are three
         * middlewares in the shop that need to be loaded before this middleware. Therefore, we need to remove this
         * middleware from the list and add the overridden middleware at the end of the list.
         *
         * As of now, this has been added in the Admin and Shop providers. I will look for a better approach in Laravel 11 for this.
         */
        $middleware->remove(PreventRequestsDuringMaintenance::class);

        /**
         * Remove the default Laravel middleware that converts empty strings to null. First, handle all nullable cases,
         * then remove this line.
         */
        $middleware->remove(ConvertEmptyStringsToNull::class);

        $middleware->append(SecureHeaders::class);
        $middleware->append(CanInstall::class);

        /**
         * Add the overridden middleware at the end of the list.
         */
        $middleware->replaceInGroup('web', BaseEncryptCookies::class, EncryptCookies::class);

        $middleware->trustProxies('*');
    })
    ->withSchedule(function (Schedule $schedule) {
        // =====================================================
        // TRENDYOL SCHEDULED TASKS
        // =====================================================
        
        // Sipariş senkronizasyonu - Her 5 dakikada bir
        $schedule->command('trendyol:sync-orders')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/trendyol-sync-orders.log'));

        // Müşteri soruları senkronizasyonu - Her 15 dakikada bir
        $schedule->command('trendyol:sync-questions')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/trendyol-sync-questions.log'));

        // BuyBox durumu kontrolü - Her 30 dakikada bir
        $schedule->command('trendyol:check-buybox')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/trendyol-buybox.log'));

        // Akıllı uyarı oluşturma - Her saat başı
        $schedule->command('trendyol:generate-alerts')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/trendyol-alerts.log'));

        // Otomatik fiyat kuralları uygulama - Her 30 dakikada bir
        $schedule->command('trendyol:apply-price-rules')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/trendyol-price-rules.log'));

        // Stok kontrolü - Her saat başı
        $schedule->command('trendyol:check-stock')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/trendyol-stock.log'));

        // Günlük rapor oluşturma - Her gün saat 09:00'da
        $schedule->command('trendyol:daily-report')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/trendyol-daily-report.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
