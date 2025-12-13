<?php

namespace CastMart\Tenant\Providers;

use Illuminate\Support\ServiceProvider;
use CastMart\Tenant\Services\TenantManager;
use CastMart\Tenant\Http\Middleware\IdentifyTenant;
use CastMart\Tenant\Http\Middleware\CheckTenantLimits;
use CastMart\Tenant\Console\Commands\CheckExpiredSubscriptions;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        
        // Views
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'castmart-tenant');
        
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        // Middleware'leri router'a ekle
        $router = $this->app['router'];
        $router->aliasMiddleware('tenant', IdentifyTenant::class);
        $router->aliasMiddleware('tenant.limits', CheckTenantLimits::class);

        // Console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CheckExpiredSubscriptions::class,
            ]);
            
            // Config publish
            $this->publishes([
                __DIR__ . '/../Config/tenant.php' => config_path('castmart-tenant.php'),
            ], 'castmart-tenant-config');
            
            // Views publish
            $this->publishes([
                __DIR__ . '/../../Resources/views' => resource_path('views/vendor/castmart-tenant'),
            ], 'castmart-tenant-views');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        // Config merge
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/tenant.php',
            'castmart-tenant'
        );

        // TenantManager singleton
        $this->app->singleton('tenant', function ($app) {
            return new TenantManager();
        });

        $this->app->singleton(TenantManager::class, function ($app) {
            return $app->make('tenant');
        });

        // Facade alias
        $this->app->alias('tenant', TenantManager::class);
    }
}
