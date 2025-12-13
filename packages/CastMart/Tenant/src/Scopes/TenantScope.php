<?php

namespace CastMart\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use CastMart\Tenant\Facades\Tenant;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Multi-tenant modu aktif değilse scope uygulanmaz
        if (!config('castmart-tenant.enabled', false)) {
            return;
        }

        // Tenant belirlenmediyse scope uygulanmaz
        if (!Tenant::check()) {
            return;
        }

        // Console komutlarında scope uygulanmaz (opsiyonel)
        if (app()->runningInConsole() && !Tenant::forceScope()) {
            return;
        }

        // Tenant filtresini uygula
        $builder->where($model->getTable() . '.tenant_id', Tenant::id());
    }
}
