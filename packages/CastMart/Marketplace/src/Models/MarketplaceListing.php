<?php

namespace CastMart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceListing extends Model
{
    protected $fillable = [
        'account_id',
        'product_id',
        'external_id',
        'external_url',
        'status',
        'rejection_reason',
        'last_sync_at',
        'extra_data',
    ];

    protected $casts = [
        'extra_data' => 'array',
        'last_sync_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PASSIVE = 'passive';

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'account_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\Product::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
