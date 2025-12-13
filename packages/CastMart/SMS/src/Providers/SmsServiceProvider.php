<?php

namespace CastMart\SMS\Providers;

use Illuminate\Support\ServiceProvider;
use CastMart\SMS\Services\SmsService;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php');
        
        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Views
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'castmart-sms');
        
        // Config publish
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/sms.php' => config_path('castmart-sms.php'),
            ], 'castmart-sms-config');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/sms.php',
            'castmart-sms'
        );

        // SmsService singleton
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });

        // Facade alias
        $this->app->alias(SmsService::class, 'sms');
    }
}
