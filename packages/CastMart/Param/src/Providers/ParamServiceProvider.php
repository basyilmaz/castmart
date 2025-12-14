<?php

namespace CastMart\Param\Providers;

use Illuminate\Support\ServiceProvider;

class ParamServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Config
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/param.php', 'param'
        );

        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Views
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'param');

        // Publish config
        $this->publishes([
            __DIR__ . '/../Config/param.php' => config_path('param.php'),
        ], 'param-config');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('param', function ($app) {
            return new \CastMart\Param\Payment\Param();
        });
    }
}
