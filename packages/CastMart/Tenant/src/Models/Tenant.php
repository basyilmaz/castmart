<?php

namespace CastMart\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    use SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'database',
        'plan',
        'status',
        'owner_id',
        'settings',
        'trial_ends_at',
        'subscription_ends_at',
        'metadata',
    ];

    protected $casts = [
        'settings' => 'array',
        'metadata' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = \Illuminate\Support\Str::slug($tenant->name);
            }
            if (empty($tenant->subdomain)) {
                $tenant->subdomain = $tenant->slug;
            }
        });
    }

    /**
     * Tenant sahibi (admin user)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\Webkul\User\Models\Admin::class, 'owner_id');
    }

    /**
     * Tenant kullanıcıları
     */
    public function users(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    /**
     * Tenant kanalları
     */
    public function channels(): HasMany
    {
        return $this->hasMany(\Webkul\Core\Models\Channel::class, 'tenant_id');
    }

    /**
     * Tenant abonelikleri
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    /**
     * Aktif abonelik
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->latest()
            ->first();
    }

    /**
     * Tam domain URL'si
     */
    public function getUrlAttribute(): string
    {
        if ($this->domain) {
            return 'https://' . $this->domain;
        }
        
        return 'https://' . $this->subdomain . config('castmart-tenant.subdomain_suffix');
    }

    /**
     * Plan bilgilerini getir
     */
    public function getPlanDetailsAttribute(): array
    {
        $plans = config('castmart-tenant.plans');
        return $plans[$this->plan] ?? $plans['starter'];
    }

    /**
     * Plan özelliği kontrol et
     */
    public function hasFeature(string $feature): bool
    {
        $planDetails = $this->plan_details;
        return isset($planDetails['features'][$feature]) 
            && $planDetails['features'][$feature];
    }

    /**
     * Limit kontrol et
     */
    public function checkLimit(string $feature, int $currentCount): bool
    {
        $planDetails = $this->plan_details;
        $limit = $planDetails['features'][$feature] ?? 0;
        
        // -1 = limitsiz
        if ($limit === -1) {
            return true;
        }
        
        return $currentCount < $limit;
    }

    /**
     * Trial devam ediyor mu?
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Abonelik aktif mi?
     */
    public function isSubscriptionActive(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Tenant aktif mi?
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        
        return $this->isOnTrial() || $this->isSubscriptionActive();
    }

    /**
     * Durum metni
     */
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'active' => 'Aktif',
            'suspended' => 'Askıya Alındı',
            'cancelled' => 'İptal Edildi',
            'pending' => 'Onay Bekliyor',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Durum rengi
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'active' => 'success',
            'suspended' => 'warning',
            'cancelled' => 'danger',
            'pending' => 'info',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope: Aktif tenantlar
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Subdomain ile bul
     */
    public function scopeBySubdomain($query, string $subdomain)
    {
        return $query->where('subdomain', $subdomain);
    }

    /**
     * Scope: Domain ile bul
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }
}
