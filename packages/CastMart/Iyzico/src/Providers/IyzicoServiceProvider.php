<?php

namespace CastMart\Iyzico\Providers;

use Illuminate\Support\ServiceProvider;

class IyzicoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        
        // Views
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'iyzico');
        
        // Config publish
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../Config/iyzico.php' => config_path('iyzico.php'),
            ], 'iyzico-config');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/iyzico.php',
            'iyzico'
        );

        // Payment methods config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/paymentmethods.php',
            'payment_methods'
        );
    }
}
