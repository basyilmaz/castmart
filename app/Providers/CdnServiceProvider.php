<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\CdnService;

class CdnServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CdnService::class, function ($app) {
            return new CdnService();
        });

        $this->app->alias(CdnService::class, 'cdn');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Blade Directives
        
        // @cdn('path/to/file.jpg')
        Blade::directive('cdn', function ($expression) {
            return "<?php echo app('cdn')->url({$expression}); ?>";
        });

        // @cdnAsset('css/app.css')
        Blade::directive('cdnAsset', function ($expression) {
            return "<?php echo app('cdn')->assetUrl({$expression}); ?>";
        });

        // @cdnImage('products/image.jpg', ['width' => 300])
        Blade::directive('cdnImage', function ($expression) {
            return "<?php echo app('cdn')->imageUrl({$expression}); ?>";
        });

        // View Composer - CDN URL'i tüm view'lara ekle
        view()->composer('*', function ($view) {
            $view->with('cdnEnabled', config('cdn.enabled', false));
            $view->with('cdnUrl', config('cdn.asset_url'));
        });
    }
}
