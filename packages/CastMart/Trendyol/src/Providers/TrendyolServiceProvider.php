<?php

namespace CastMart\Trendyol\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use CastMart\Trendyol\Services\TrendyolService;
use CastMart\Trendyol\Services\TrendyolScraperService;
use CastMart\Trendyol\Services\IntelligenceService;
use CastMart\Trendyol\Console\Commands\SyncOrdersCommand;
use CastMart\Trendyol\Console\Commands\SyncQuestionsCommand;
use CastMart\Trendyol\Console\Commands\SyncStockCommand;
use CastMart\Trendyol\Console\Commands\ImportProductsCommand;
use CastMart\Trendyol\Console\Commands\CheckBuyboxCommand;
use CastMart\Trendyol\Console\Commands\CheckStockCommand;
use CastMart\Trendyol\Console\Commands\ApplyPriceRulesCommand;
use CastMart\Trendyol\Console\Commands\GenerateAlertsCommand;
use CastMart\Trendyol\Console\Commands\DailyReportCommand;


class TrendyolServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'trendyol');
        $this->loadTranslationsFrom(__DIR__ . '/../../Resources/lang', 'trendyol');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php');

        if ($this->app->runningInConsole()) {
            // Artisan komutlarını kaydet
            $this->commands([
                SyncOrdersCommand::class,
                SyncQuestionsCommand::class,
                SyncStockCommand::class,
                ImportProductsCommand::class,
                CheckBuyboxCommand::class,
                CheckStockCommand::class,
                ApplyPriceRulesCommand::class,
                GenerateAlertsCommand::class,
                DailyReportCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../../config/trendyol.php' => config_path('trendyol.php'),
            ], 'trendyol-config');

            // Scheduled tasks
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                
                // Sipariş senkronizasyonu - her 5 dakika
                $schedule->command('trendyol:sync-orders')
                    ->everyFiveMinutes()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-sync-orders.log'));

                // Soru senkronizasyonu - her 15 dakika
                $schedule->command('trendyol:sync-questions')
                    ->everyFifteenMinutes()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-sync-questions.log'));

                // Stok senkronizasyonu - saatte 1 kez
                $schedule->command('trendyol:sync-stock')
                    ->hourly()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-sync-stock.log'));

                // BuyBox kontrolü - her 30 dakika
                $schedule->command('trendyol:check-buybox')
                    ->everyThirtyMinutes()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-check-buybox.log'));

                // Stok durumu kontrolü - günde 2 kez
                $schedule->command('trendyol:check-stock')
                    ->twiceDaily(9, 18)
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-check-stock.log'));

                // Uyarı oluşturma - her saat
                $schedule->command('trendyol:generate-alerts')
                    ->hourly()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-generate-alerts.log'));

                // Fiyat kuralları uygulama - her 2 saatte
                $schedule->command('trendyol:apply-price-rules')
                    ->everyTwoHours()
                    ->withoutOverlapping()
                    ->appendOutputTo(storage_path('logs/trendyol-apply-price-rules.log'));
            });
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/trendyol.php',
            'trendyol'
        );

        $this->app->singleton(TrendyolScraperService::class, function ($app) {
            return new TrendyolScraperService();
        });
    }
}

