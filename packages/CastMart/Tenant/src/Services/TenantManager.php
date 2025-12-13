<?php

namespace CastMart\Tenant\Services;

use CastMart\Tenant\Models\Tenant;
use CastMart\Tenant\Models\TenantUser;
use CastMart\Tenant\Models\TenantSubscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantManager
{
    protected ?Tenant $currentTenant = null;
    protected bool $forceScope = false;

    /**
     * Mevcut tenant'ı ayarla
     */
    public function set(Tenant $tenant): void
    {
        $this->currentTenant = $tenant;

        // Cache'e kaydet
        if (config('castmart-tenant.cache.enabled')) {
            $this->cacheCurrentTenant();
        }
    }

    /**
     * Mevcut tenant'ı getir
     */
    public function current(): ?Tenant
    {
        return $this->currentTenant;
    }

    /**
     * Tenant ID'sini getir
     */
    public function id(): ?int
    {
        return $this->currentTenant?->id;
    }

    /**
     * Tenant belirlenmiş mi?
     */
    public function check(): bool
    {
        return $this->currentTenant !== null;
    }

    /**
     * Force scope modunu kontrol et
     */
    public function forceScope(): bool
    {
        return $this->forceScope;
    }

    /**
     * Force scope modunu aktif et
     */
    public function enableForceScope(): void
    {
        $this->forceScope = true;
    }

    /**
     * Subdomain ile tenant bul
     */
    public function findBySubdomain(string $subdomain): ?Tenant
    {
        $cacheKey = $this->getCacheKey('subdomain', $subdomain);

        if (config('castmart-tenant.cache.enabled')) {
            return Cache::remember($cacheKey, config('castmart-tenant.cache.ttl'), function () use ($subdomain) {
                return Tenant::bySubdomain($subdomain)->active()->first();
            });
        }

        return Tenant::bySubdomain($subdomain)->active()->first();
    }

    /**
     * Domain ile tenant bul
     */
    public function findByDomain(string $domain): ?Tenant
    {
        $cacheKey = $this->getCacheKey('domain', $domain);

        if (config('castmart-tenant.cache.enabled')) {
            return Cache::remember($cacheKey, config('castmart-tenant.cache.ttl'), function () use ($domain) {
                return Tenant::byDomain($domain)->active()->first();
            });
        }

        return Tenant::byDomain($domain)->active()->first();
    }

    /**
     * Yeni tenant oluştur
     */
    public function create(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $defaults = config('castmart-tenant.defaults');

            $tenant = Tenant::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'subdomain' => $data['subdomain'] ?? Str::slug($data['name']),
                'domain' => $data['domain'] ?? null,
                'plan' => $data['plan'] ?? $defaults['plan'],
                'status' => $defaults['status'],
                'owner_id' => $data['owner_id'] ?? null,
                'settings' => $data['settings'] ?? [],
                'trial_ends_at' => now()->addDays($defaults['trial_days']),
            ]);

            // Sahibi tenant_users'a ekle
            if ($tenant->owner_id) {
                TenantUser::create([
                    'tenant_id' => $tenant->id,
                    'admin_id' => $tenant->owner_id,
                    'role' => 'owner',
                    'is_owner' => true,
                ]);
            }

            // Varsayılan channel oluştur
            $this->createDefaultChannel($tenant);

            return $tenant;
        });
    }

    /**
     * Varsayılan channel oluştur
     */
    protected function createDefaultChannel(Tenant $tenant): void
    {
        try {
            \Webkul\Core\Models\Channel::create([
                'code' => $tenant->slug,
                'name' => $tenant->name,
                'description' => $tenant->name . ' mağazası',
                'timezone' => 'Europe/Istanbul',
                'theme' => 'default',
                'hostname' => $tenant->subdomain . config('castmart-tenant.subdomain_suffix'),
                'default_locale_id' => 1,
                'base_currency_id' => 1,
                'root_category_id' => 1,
                'tenant_id' => $tenant->id,
            ]);
        } catch (\Exception $e) {
            // Channel oluşturma hatası loglanır ama işlem devam eder
            \Illuminate\Support\Facades\Log::warning('Tenant channel oluşturulamadı', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Abonelik oluştur
     */
    public function createSubscription(Tenant $tenant, string $plan, string $billingCycle = 'monthly'): TenantSubscription
    {
        $planDetails = config("castmart-tenant.plans.{$plan}");
        $price = $billingCycle === 'yearly' 
            ? $planDetails['price_yearly'] 
            : $planDetails['price_monthly'];

        $duration = $billingCycle === 'yearly' ? 365 : 30;

        return TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan' => $plan,
            'billing_cycle' => $billingCycle,
            'price' => $price,
            'currency' => 'TRY',
            'status' => 'pending',
            'starts_at' => now(),
            'ends_at' => now()->addDays($duration),
        ]);
    }

    /**
     * Aboneliği aktif et
     */
    public function activateSubscription(TenantSubscription $subscription, string $paymentId): void
    {
        $subscription->update([
            'status' => 'active',
            'payment_id' => $paymentId,
        ]);

        $subscription->tenant->update([
            'subscription_ends_at' => $subscription->ends_at,
        ]);
    }

    /**
     * Tenant'ı askıya al
     */
    public function suspend(Tenant $tenant, string $reason = null): void
    {
        $tenant->update([
            'status' => 'suspended',
            'metadata' => array_merge($tenant->metadata ?? [], [
                'suspended_at' => now()->toIso8601String(),
                'suspend_reason' => $reason,
            ]),
        ]);

        $this->clearTenantCache($tenant);
    }

    /**
     * Tenant'ı aktif et
     */
    public function activate(Tenant $tenant): void
    {
        $tenant->update([
            'status' => 'active',
        ]);

        $this->clearTenantCache($tenant);
    }

    /**
     * Tenant cache'ini temizle
     */
    public function clearTenantCache(Tenant $tenant): void
    {
        Cache::forget($this->getCacheKey('subdomain', $tenant->subdomain));
        
        if ($tenant->domain) {
            Cache::forget($this->getCacheKey('domain', $tenant->domain));
        }
    }

    /**
     * Cache key oluştur
     */
    protected function getCacheKey(string $type, string $value): string
    {
        $prefix = config('castmart-tenant.cache.prefix', 'tenant_');
        return $prefix . $type . '_' . $value;
    }

    /**
     * Mevcut tenant'ı cache'le
     */
    protected function cacheCurrentTenant(): void
    {
        if (!$this->currentTenant) {
            return;
        }

        $ttl = config('castmart-tenant.cache.ttl', 3600);

        Cache::put(
            $this->getCacheKey('subdomain', $this->currentTenant->subdomain),
            $this->currentTenant,
            $ttl
        );
    }

    /**
     * Kullanım istatistikleri
     */
    public function getUsageStats(Tenant $tenant): array
    {
        // Bu metodun içeriği tenant_id sütunu eklendikten sonra doldurulacak
        return [
            'products' => 0,
            'orders_this_month' => 0,
            'users' => TenantUser::where('tenant_id', $tenant->id)->count(),
            'storage_used' => 0,
        ];
    }

    /**
     * Limit kontrolü
     */
    public function checkLimit(Tenant $tenant, string $feature): array
    {
        $stats = $this->getUsageStats($tenant);
        $planDetails = $tenant->plan_details;
        $limit = $planDetails['features'][$feature] ?? 0;

        $featureMap = [
            'max_products' => 'products',
            'max_orders_per_month' => 'orders_this_month',
            'max_users' => 'users',
        ];

        $statKey = $featureMap[$feature] ?? null;
        $current = $statKey ? ($stats[$statKey] ?? 0) : 0;

        return [
            'allowed' => $limit === -1 || $current < $limit,
            'current' => $current,
            'limit' => $limit,
            'remaining' => $limit === -1 ? -1 : max(0, $limit - $current),
        ];
    }
}
