<?php

namespace Tests\Feature\CastMart\Trendyol;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use CastMart\Trendyol\Services\TrendyolService;
use CastMart\Trendyol\Models\TrendyolProduct;
use Illuminate\Support\Facades\Http;

class TrendyolServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TrendyolService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new TrendyolService();
        
        // Mock HTTP responses
        Http::fake([
            'api.trendyol.com/*' => Http::response([
                'content' => [],
                'totalElements' => 0,
            ], 200),
        ]);
    }

    /** @test */
    public function it_can_fetch_products_from_trendyol()
    {
        Http::fake([
            '*suppliers/*/products*' => Http::response([
                'content' => [
                    [
                        'barcode' => 'TEST123',
                        'title' => 'Test Ürün',
                        'productMainId' => 'MAIN123',
                        'brandId' => 1000,
                        'categoryId' => 500,
                        'listPrice' => 199.99,
                        'salePrice' => 149.99,
                        'quantity' => 10,
                    ],
                ],
                'totalElements' => 1,
            ], 200),
        ]);

        $result = $this->service->fetchProducts();

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['products']);
        $this->assertEquals('TEST123', $result['products'][0]['barcode']);
    }

    /** @test */
    public function it_can_fetch_orders_from_trendyol()
    {
        Http::fake([
            '*suppliers/*/orders*' => Http::response([
                'content' => [
                    [
                        'orderNumber' => 'TY123456',
                        'status' => 'Created',
                        'lines' => [
                            ['quantity' => 1, 'productName' => 'Test Ürün'],
                        ],
                    ],
                ],
                'totalElements' => 1,
            ], 200),
        ]);

        $result = $this->service->fetchOrders();

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['orders']);
    }

    /** @test */
    public function it_handles_api_errors_gracefully()
    {
        Http::fake([
            '*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $result = $this->service->fetchProducts();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('message', $result);
    }

    /** @test */
    public function it_can_update_product_price()
    {
        Http::fake([
            '*suppliers/*/products/price-and-inventory*' => Http::response([
                'batchRequestId' => 'BATCH123',
            ], 200),
        ]);

        $result = $this->service->updatePrice('TEST123', 199.99, 149.99);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_update_product_stock()
    {
        Http::fake([
            '*suppliers/*/products/price-and-inventory*' => Http::response([
                'batchRequestId' => 'BATCH456',
            ], 200),
        ]);

        $result = $this->service->updateStock('TEST123', 50);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_fetch_customer_questions()
    {
        Http::fake([
            '*suppliers/*/questions*' => Http::response([
                'content' => [
                    [
                        'id' => 12345,
                        'text' => 'Bu ürün orijinal mi?',
                        'creationDate' => time() * 1000,
                        'status' => 'WAITING_FOR_ANSWER',
                    ],
                ],
                'totalElements' => 1,
            ], 200),
        ]);

        $result = $this->service->fetchQuestions();

        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['questions']);
    }

    /** @test */
    public function it_can_answer_customer_question()
    {
        Http::fake([
            '*suppliers/*/questions/*/answer*' => Http::response([], 200),
        ]);

        $result = $this->service->answerQuestion(12345, 'Evet, orijinal üründür.');

        $this->assertTrue($result['success']);
    }
}
