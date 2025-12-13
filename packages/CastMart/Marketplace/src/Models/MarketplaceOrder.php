<?php

namespace CastMart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceOrder extends Model
{
    protected $fillable = [
        'account_id',
        'order_id',
        'external_order_id',
        'external_order_number',
        'package_id',
        'status',
        'cargo_provider',
        'tracking_number',
        'order_data',
        'customer_data',
        'items_data',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'order_data' => 'array',
        'customer_data' => 'array',
        'items_data' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'account_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class, 'order_id');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }
}
