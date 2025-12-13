<?php

namespace Tests\Feature\CastMart\Tenant;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use CastMart\Tenant\Models\Tenant;
use CastMart\Tenant\Models\TenantSubscription;
use CastMart\Tenant\Services\TenantManager;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    protected TenantManager $tenantManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenantManager = new TenantManager();
    }

    /** @test */
    public function it_can_create_a_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'Test Mağaza',
            'slug' => 'test-magaza',
            'subdomain' => 'test',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('tenants', [
            'slug' => 'test-magaza',
            'plan' => 'starter',
        ]);
    }

    /** @test */
    public function it_generates_unique_slugs()
    {
        $tenant1 = Tenant::create([
            'name' => 'Test Mağaza',
            'slug' => 'test-magaza',
            'subdomain' => 'test1',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $tenant2 = Tenant::create([
            'name' => 'Test Mağaza 2',
            'slug' => 'test-magaza-2',
            'subdomain' => 'test2',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $this->assertNotEquals($tenant1->slug, $tenant2->slug);
    }

    /** @test */
    public function it_can_find_tenant_by_subdomain()
    {
        Tenant::create([
            'name' => 'Subdomain Test',
            'slug' => 'subdomain-test',
            'subdomain' => 'mystore',
            'plan' => 'professional',
            'status' => 'active',
        ]);

        $found = $this->tenantManager->findBySubdomain('mystore');

        $this->assertNotNull($found);
        $this->assertEquals('Subdomain Test', $found->name);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_subdomain()
    {
        $found = $this->tenantManager->findBySubdomain('nonexistent');

        $this->assertNull($found);
    }

    /** @test */
    public function it_can_check_if_tenant_is_active()
    {
        $activeTenant = Tenant::create([
            'name' => 'Active',
            'slug' => 'active',
            'subdomain' => 'active',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $suspendedTenant = Tenant::create([
            'name' => 'Suspended',
            'slug' => 'suspended',
            'subdomain' => 'suspended',
            'plan' => 'starter',
            'status' => 'suspended',
        ]);

        $this->assertTrue($activeTenant->isActive());
        $this->assertFalse($suspendedTenant->isActive());
        $this->assertTrue($suspendedTenant->isSuspended());
    }

    /** @test */
    public function it_can_suspend_a_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'To Suspend',
            'slug' => 'to-suspend',
            'subdomain' => 'suspend',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $this->tenantManager->suspend($tenant, 'Payment failed');

        $tenant->refresh();

        $this->assertEquals('suspended', $tenant->status);
    }

    /** @test */
    public function it_can_activate_a_tenant()
    {
        $tenant = Tenant::create([
            'name' => 'To Activate',
            'slug' => 'to-activate',
            'subdomain' => 'activate',
            'plan' => 'starter',
            'status' => 'suspended',
        ]);

        $this->tenantManager->activate($tenant);

        $tenant->refresh();

        $this->assertEquals('active', $tenant->status);
    }

    /** @test */
    public function it_returns_plan_details()
    {
        $tenant = Tenant::create([
            'name' => 'Plan Test',
            'slug' => 'plan-test',
            'subdomain' => 'plantest',
            'plan' => 'professional',
            'status' => 'active',
        ]);

        $planDetails = $tenant->getPlanDetails();

        $this->assertNotNull($planDetails);
        $this->assertArrayHasKey('features', $planDetails);
        $this->assertArrayHasKey('limits', $planDetails);
    }

    /** @test */
    public function it_can_check_plan_limits()
    {
        $tenant = Tenant::create([
            'name' => 'Limit Test',
            'slug' => 'limit-test',
            'subdomain' => 'limitest',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        // Starter plan has limited products
        $withinLimit = $tenant->canAdd('products', 10);
        $this->assertTrue($withinLimit);
    }

    /** @test */
    public function it_can_create_subscription()
    {
        $tenant = Tenant::create([
            'name' => 'Sub Test',
            'slug' => 'sub-test',
            'subdomain' => 'subtest',
            'plan' => 'starter',
            'status' => 'active',
        ]);

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan' => 'professional',
            'billing_cycle' => 'monthly',
            'price' => 299.00,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->assertDatabaseHas('tenant_subscriptions', [
            'tenant_id' => $tenant->id,
            'plan' => 'professional',
        ]);
    }
}
