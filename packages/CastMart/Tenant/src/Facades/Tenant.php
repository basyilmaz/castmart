<?php

namespace CastMart\Tenant\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void set(\CastMart\Tenant\Models\Tenant $tenant)
 * @method static \CastMart\Tenant\Models\Tenant|null current()
 * @method static int|null id()
 * @method static bool check()
 * @method static bool forceScope()
 * @method static void enableForceScope()
 * @method static \CastMart\Tenant\Models\Tenant|null findBySubdomain(string $subdomain)
 * @method static \CastMart\Tenant\Models\Tenant|null findByDomain(string $domain)
 * @method static \CastMart\Tenant\Models\Tenant create(array $data)
 * @method static \CastMart\Tenant\Models\TenantSubscription createSubscription(\CastMart\Tenant\Models\Tenant $tenant, string $plan, string $billingCycle = 'monthly')
 * @method static void activateSubscription(\CastMart\Tenant\Models\TenantSubscription $subscription, string $paymentId)
 * @method static void suspend(\CastMart\Tenant\Models\Tenant $tenant, string $reason = null)
 * @method static void activate(\CastMart\Tenant\Models\Tenant $tenant)
 * @method static void clearTenantCache(\CastMart\Tenant\Models\Tenant $tenant)
 * @method static array getUsageStats(\CastMart\Tenant\Models\Tenant $tenant)
 * @method static array checkLimit(\CastMart\Tenant\Models\Tenant $tenant, string $feature)
 *
 * @see \CastMart\Tenant\Services\TenantManager
 */
class Tenant extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tenant';
    }
}
