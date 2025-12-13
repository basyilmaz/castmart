<?php

namespace CastMart\Tenant\Traits;

use CastMart\Tenant\Scopes\TenantScope;
use CastMart\Tenant\Facades\Tenant;

trait BelongsToTenant
{
    /**
     * Boot trait
     */
    public static function bootBelongsToTenant(): void
    {
        // Global scope ekle
        static::addGlobalScope(new TenantScope);

        // Oluşturulurken otomatik tenant_id ata
        static::creating(function ($model) {
            if (!$model->tenant_id && Tenant::check()) {
                $model->tenant_id = Tenant::id();
            }
        });
    }

    /**
     * Tenant ilişkisi
     */
    public function tenant()
    {
        return $this->belongsTo(\CastMart\Tenant\Models\Tenant::class, 'tenant_id');
    }

    /**
     * Tenant scope'u olmadan sorgu yap
     */
    public static function withoutTenantScope()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }

    /**
     * Belirli bir tenant için filtrele
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId);
    }
}
