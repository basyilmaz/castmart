<?php

namespace Tests\Feature\CastMart\Marketing;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use CastMart\Marketing\Models\Coupon;
use CastMart\Marketing\Services\MarketingService;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected MarketingService $marketingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->marketingService = new MarketingService();
    }

    /** @test */
    public function it_can_create_a_coupon()
    {
        $coupon = Coupon::create([
            'code' => 'TEST20',
            'name' => 'Test Kupon',
            'type' => 'percentage',
            'value' => 20,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('coupons', [
            'code' => 'TEST20',
            'type' => 'percentage',
            'value' => 20,
        ]);
    }

    /** @test */
    public function it_can_validate_a_valid_coupon()
    {
        Coupon::create([
            'code' => 'VALID10',
            'name' => 'Geçerli Kupon',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $result = $this->marketingService->validateCoupon('VALID10', null, 500);

        $this->assertTrue($result['valid']);
        $this->assertEquals(50, $result['discount']); // 500 * 10%
    }

    /** @test */
    public function it_rejects_invalid_coupon_code()
    {
        $result = $this->marketingService->validateCoupon('INVALID123', null, 500);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('bulunamadı', $result['message']);
    }

    /** @test */
    public function it_rejects_inactive_coupon()
    {
        Coupon::create([
            'code' => 'INACTIVE',
            'name' => 'Pasif Kupon',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => false,
        ]);

        $result = $this->marketingService->validateCoupon('INACTIVE', null, 500);

        $this->assertFalse($result['valid']);
    }

    /** @test */
    public function it_rejects_expired_coupon()
    {
        Coupon::create([
            'code' => 'EXPIRED',
            'name' => 'Süresi Dolmuş',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $result = $this->marketingService->validateCoupon('EXPIRED', null, 500);

        $this->assertFalse($result['valid']);
    }

    /** @test */
    public function it_respects_minimum_order_amount()
    {
        Coupon::create([
            'code' => 'MIN100',
            'name' => 'Minimum 100 TL',
            'type' => 'fixed',
            'value' => 20,
            'min_order_amount' => 100,
            'is_active' => true,
        ]);

        $result = $this->marketingService->validateCoupon('MIN100', null, 50);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Minimum', $result['message']);
    }

    /** @test */
    public function it_calculates_fixed_discount_correctly()
    {
        Coupon::create([
            'code' => 'FIXED50',
            'name' => '50 TL İndirim',
            'type' => 'fixed',
            'value' => 50,
            'is_active' => true,
        ]);

        $result = $this->marketingService->validateCoupon('FIXED50', null, 200);

        $this->assertTrue($result['valid']);
        $this->assertEquals(50, $result['discount']);
    }

    /** @test */
    public function it_respects_max_discount_amount()
    {
        Coupon::create([
            'code' => 'MAX30',
            'name' => 'Max 30 TL',
            'type' => 'percentage',
            'value' => 20,
            'max_discount_amount' => 30,
            'is_active' => true,
        ]);

        $result = $this->marketingService->validateCoupon('MAX30', null, 500);

        $this->assertTrue($result['valid']);
        $this->assertEquals(30, $result['discount']); // 100 olması gerekirdi ama max 30
    }

    /** @test */
    public function it_generates_unique_coupon_codes()
    {
        $code1 = Coupon::generateCode();
        $code2 = Coupon::generateCode();

        $this->assertNotEquals($code1, $code2);
        $this->assertStringStartsWith('CM', $code1);
    }

    /** @test */
    public function coupon_api_endpoint_works()
    {
        Coupon::create([
            'code' => 'APITEST',
            'name' => 'API Test',
            'type' => 'percentage',
            'value' => 15,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/marketing/coupons/validate', [
            'code' => 'APITEST',
            'subtotal' => 200,
        ]);

        $response->assertOk()
            ->assertJson([
                'valid' => true,
            ]);
    }
}
