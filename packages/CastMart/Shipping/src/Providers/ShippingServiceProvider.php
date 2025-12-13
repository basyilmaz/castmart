<?php

namespace CastMart\Shipping\Providers;

use Illuminate\Support\ServiceProvider;
use CastMart\Shipping\Services\ShippingService;

class ShippingServiceProvider extends ServiceProvider
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
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'castmart-shipping');
        
        // Config publish
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/shipping.php' => config_path('castmart-shipping.php'),
            ], 'castmart-shipping-config');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/shipping.php',
            'castmart-shipping'
        );

        // ShippingService singleton
        $this->app->singleton(ShippingService::class, function ($app) {
            return new ShippingService();
        });
    }
}
