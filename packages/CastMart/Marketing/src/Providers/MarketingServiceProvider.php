<?php

namespace CastMart\Marketing\Providers;

use Illuminate\Support\ServiceProvider;
use CastMart\Marketing\Services\MarketingService;
use CastMart\Marketing\Services\EmailMarketingService;
use CastMart\Marketing\Services\NotificationService;
use CastMart\Marketing\Console\Commands\ProcessAbandonedCarts;
use CastMart\Marketing\Console\Commands\ProcessBirthdayEmails;
use CastMart\Marketing\Console\Commands\ProcessWinBackEmails;

class MarketingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Views
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'castmart-marketing');
        
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php');

        // Console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessAbandonedCarts::class,
                ProcessBirthdayEmails::class,
                ProcessWinBackEmails::class,
            ]);

            // Config publish
            $this->publishes([
                __DIR__ . '/../Config/marketing.php' => config_path('castmart-marketing.php'),
            ], 'castmart-marketing-config');

            // Views publish
            $this->publishes([
                __DIR__ . '/../../Resources/views' => resource_path('views/vendor/castmart-marketing'),
            ], 'castmart-marketing-views');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/marketing.php',
            'castmart-marketing'
        );

        // MarketingService singleton
        $this->app->singleton(MarketingService::class, function ($app) {
            return new MarketingService();
        });

        // EmailMarketingService singleton
        $this->app->singleton(EmailMarketingService::class, function ($app) {
            return new EmailMarketingService();
        });

        // NotificationService singleton
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });

        $this->app->alias(MarketingService::class, 'marketing');
    }
}
