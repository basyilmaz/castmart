<?php

namespace CastMart\Marketplace\Providers;

use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'marketplace');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/marketplace.php' => config_path('marketplace.php'),
            ], 'marketplace-config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/marketplace.php',
            'marketplace'
        );
    }
}
