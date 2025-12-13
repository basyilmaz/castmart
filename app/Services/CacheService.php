<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache prefix
     */
    protected string $prefix;

    /**
     * Default TTL (1 saat)
     */
    protected int $defaultTtl = 3600;

    public function __construct()
    {
        $this->prefix = config('cache.prefix', 'castmart');
    }

    /*
    |--------------------------------------------------------------------------
    | ÜRÜN CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * Ürün detayını cache'le
     */
    public function getProduct(int $productId, callable $callback)
    {
        $key = $this->key("product:{$productId}");

        return Cache::remember($key, $this->defaultTtl, $callback);
    }

    /**
     * Ürün cache'ini temizle
     */
    public function clearProduct(int $productId): void
    {
        Cache::forget($this->key("product:{$productId}"));
        Cache::forget($this->key("product:{$productId}:variants"));
        Cache::forget($this->key("product:{$productId}:reviews"));
    }

    /**
     * Ürün listesi cache
     */
    public function getProductList(string $cacheKey, int $ttl, callable $callback)
    {
        $key = $this->key("products:{$cacheKey}");

        return Cache::remember($key, $ttl, $callback);
    }

    /*
    |--------------------------------------------------------------------------
    | KATEGORİ CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * Kategori ağacını cache'le
     */
    public function getCategoryTree(callable $callback)
    {
        $key = $this->key('categories:tree');

        return Cache::remember($key, 86400, $callback); // 24 saat
    }

    /**
     * Kategori cache temizle
     */
    public function clearCategories(): void
    {
        Cache::forget($this->key('categories:tree'));
        $this->clearPattern('categories:*');
    }

    /*
    |--------------------------------------------------------------------------
    | SEPET CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * Sepet bilgisi cache
     */
    public function getCart(string $cartId, callable $callback)
    {
        $key = $this->key("cart:{$cartId}");

        return Cache::remember($key, 1800, $callback); // 30 dakika
    }

    /**
     * Sepet cache temizle
     */
    public function clearCart(string $cartId): void
    {
        Cache::forget($this->key("cart:{$cartId}"));
    }

    /*
    |--------------------------------------------------------------------------
    | KULLANICI CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * Kullanıcı oturumu cache
     */
    public function getUserSession(int $userId, callable $callback)
    {
        $key = $this->key("user:{$userId}:session");

        return Cache::remember($key, 3600, $callback);
    }

    /**
     * Kullanıcı wish list cache
     */
    public function getWishlist(int $customerId, callable $callback)
    {
        $key = $this->key("wishlist:{$customerId}");

        return Cache::remember($key, 1800, $callback);
    }

    /**
     * Kullanıcı cache temizle
     */
    public function clearUser(int $userId): void
    {
        Cache::forget($this->key("user:{$userId}:session"));
        Cache::forget($this->key("wishlist:{$userId}"));
    }

    /*
    |--------------------------------------------------------------------------
    | AYAR CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * Site ayarları cache
     */
    public function getSettings(callable $callback)
    {
        $key = $this->key('settings:global');

        return Cache::remember($key, 86400, $callback); // 24 saat
    }

    /**
     * Ayar cache temizle
     */
    public function clearSettings(): void
    {
        Cache::forget($this->key('settings:global'));
    }

    /*
    |--------------------------------------------------------------------------
    | API RESPONSE CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * API response cache
     */
    public function cacheApiResponse(string $endpoint, array $params, int $ttl, callable $callback)
    {
        $hash = md5($endpoint . serialize($params));
        $key = $this->key("api:{$hash}");

        return Cache::remember($key, $ttl, $callback);
    }

    /*
    |--------------------------------------------------------------------------
    | ISTATISTIK CACHE
    |--------------------------------------------------------------------------
    */

    /**
     * Dashboard istatistikleri cache
     */
    public function getDashboardStats(callable $callback)
    {
        $key = $this->key('stats:dashboard');

        return Cache::remember($key, 300, $callback); // 5 dakika
    }

    /**
     * Trendyol istatistikleri cache
     */
    public function getTrendyolStats(callable $callback)
    {
        $key = $this->key('stats:trendyol');

        return Cache::remember($key, 600, $callback); // 10 dakika
    }

    /*
    |--------------------------------------------------------------------------
    | YARDIMCI METODLAR
    |--------------------------------------------------------------------------
    */

    /**
     * Cache key oluştur
     */
    protected function key(string $key): string
    {
        return $this->prefix . ':' . $key;
    }

    /**
     * Pattern ile cache temizle (Redis)
     */
    public function clearPattern(string $pattern): void
    {
        if (config('cache.default') !== 'redis') {
            return;
        }

        try {
            $fullPattern = $this->key($pattern);
            $keys = Redis::keys($fullPattern);

            if (!empty($keys)) {
                Redis::del($keys);
            }
        } catch (\Exception $e) {
            Log::warning('Cache pattern clear failed', ['pattern' => $pattern, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Tüm cache'i temizle
     */
    public function clearAll(): void
    {
        Cache::flush();
    }

    /**
     * Cache istatistikleri
     */
    public function getStats(): array
    {
        if (config('cache.default') !== 'redis') {
            return ['driver' => config('cache.default')];
        }

        try {
            $info = Redis::info();

            return [
                'driver' => 'redis',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_keys' => $info['db0']['keys'] ?? 0,
                'uptime_days' => round(($info['uptime_in_seconds'] ?? 0) / 86400, 1),
            ];
        } catch (\Exception $e) {
            return ['driver' => 'redis', 'error' => $e->getMessage()];
        }
    }

    /**
     * Cache warming (önceden yükleme)
     */
    public function warmCache(): array
    {
        $warmed = [];

        // Kategori ağacı
        try {
            $this->getCategoryTree(fn() => \Webkul\Category\Models\Category::with('children')->whereNull('parent_id')->get());
            $warmed[] = 'categories';
        } catch (\Exception $e) {
            Log::error('Cache warm failed: categories', ['error' => $e->getMessage()]);
        }

        // Site ayarları
        try {
            $this->getSettings(fn() => \Webkul\Core\Models\CoreConfig::all()->pluck('value', 'code'));
            $warmed[] = 'settings';
        } catch (\Exception $e) {
            Log::error('Cache warm failed: settings', ['error' => $e->getMessage()]);
        }

        return $warmed;
    }
}
