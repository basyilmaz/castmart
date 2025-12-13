<?php

namespace CastMart\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $table = 'loyalty_transactions';

    protected $fillable = [
        'loyalty_account_id',
        'type', // earn, redeem, bonus, referral, adjustment
        'points',
        'balance_after',
        'description',
        'order_id',
        'expires_at',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * İlişkili hesap
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(LoyaltyAccount::class, 'loyalty_account_id');
    }

    /**
     * İlişkili sipariş
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class);
    }

    /**
     * İşlem tipi metni
     */
    public function getTypeTextAttribute(): string
    {
        $types = [
            'earn' => 'Kazanım',
            'redeem' => 'Harcama',
            'bonus' => 'Bonus',
            'referral' => 'Referans',
            'adjustment' => 'Düzeltme',
            'expire' => 'Süre Dolumu',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Scope: Kazanımlar
     */
    public function scopeEarnings($query)
    {
        return $query->where('points', '>', 0);
    }

    /**
     * Scope: Harcamalar
     */
    public function scopeRedemptions($query)
    {
        return $query->where('points', '<', 0);
    }
}
