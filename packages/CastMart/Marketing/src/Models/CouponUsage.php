<?php

namespace CastMart\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    protected $table = 'coupon_usages';

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'order_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    /**
     * İlişkili kupon
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * İlişkili müşteri
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Customer\Models\Customer::class);
    }

    /**
     * İlişkili sipariş
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class);
    }
}
