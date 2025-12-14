<?php

namespace CastMart\PayTR\Providers;

use Illuminate\Support\ServiceProvider;

class PayTRServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        
        // Views
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'paytr');
        
        // Config publish
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/paytr.php' => config_path('paytr.php'),
            ], 'paytr-config');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/paytr.php',
            'paytr'
        );

        // Payment methods config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/paymentmethods.php',
            'payment_methods'
        );
    }
}
