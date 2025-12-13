<?php

namespace CastMart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CastMart\Tenant\Traits\BelongsToTenant;

class MarketplaceAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'marketplace',
        'name',
        'credentials',
        'is_active',
        'last_sync_at',
        'sync_settings',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'sync_settings' => 'array',
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    public function listings(): HasMany
    {
        return $this->hasMany(MarketplaceListing::class, 'account_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MarketplaceOrder::class, 'account_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CustomerQuestion::class, 'account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMarketplace($query, string $marketplace)
    {
        return $query->where('marketplace', $marketplace);
    }
}

