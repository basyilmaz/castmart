<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheServiceTest extends TestCase
{
    protected CacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = new CacheService();
    }

    /** @test */
    public function it_can_cache_product_data()
    {
        $productId = 123;
        $productData = ['id' => 123, 'name' => 'Test Ürün'];

        $result = $this->cacheService->getProduct($productId, fn() => $productData);

        $this->assertEquals($productData, $result);
    }

    /** @test */
    public function it_returns_cached_data_on_second_call()
    {
        $productId = 456;
        $callCount = 0;

        $callback = function () use (&$callCount) {
            $callCount++;
            return ['id' => 456, 'name' => 'Cached'];
        };

        // İlk çağrı
        $this->cacheService->getProduct($productId, $callback);
        
        // İkinci çağrı (cache'den gelmeli)
        $this->cacheService->getProduct($productId, $callback);

        $this->assertEquals(1, $callCount);
    }

    /** @test */
    public function it_can_clear_product_cache()
    {
        $productId = 789;
        
        $this->cacheService->getProduct($productId, fn() => ['test' => true]);
        
        $this->cacheService->clearProduct($productId);

        // Cache temizlendikten sonra callback tekrar çalışmalı
        $callCount = 0;
        $this->cacheService->getProduct($productId, function () use (&$callCount) {
            $callCount++;
            return ['new' => true];
        });

        $this->assertEquals(1, $callCount);
    }

    /** @test */
    public function it_can_cache_category_tree()
    {
        $categories = [
            ['id' => 1, 'name' => 'Elektronik'],
            ['id' => 2, 'name' => 'Giyim'],
        ];

        $result = $this->cacheService->getCategoryTree(fn() => $categories);

        $this->assertEquals($categories, $result);
    }

    /** @test */
    public function it_can_cache_settings()
    {
        $settings = ['site_name' => 'CastMart', 'currency' => 'TRY'];

        $result = $this->cacheService->getSettings(fn() => $settings);

        $this->assertEquals($settings, $result);
    }

    /** @test */
    public function it_returns_cache_stats()
    {
        $stats = $this->cacheService->getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('driver', $stats);
    }
}
