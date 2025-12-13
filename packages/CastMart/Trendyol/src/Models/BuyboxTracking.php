<?php

namespace CastMart\Trendyol\Models;

use Illuminate\Database\Eloquent\Model;
use CastMart\Marketplace\Models\MarketplaceAccount;

class BuyboxTracking extends Model
{
    protected $table = 'trendyol_buybox_tracking';

    protected $fillable = [
        'marketplace_account_id',
        'product_sku',
        'barcode',
        'our_price',
        'competitor_price',
        'competitor_seller',
        'status',
        'win_chance',
        'last_checked_at',
    ];

    protected $casts = [
        'our_price' => 'decimal:2',
        'competitor_price' => 'decimal:2',
        'last_checked_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }

    public function scopeWon($query)
    {
        return $query->where('status', 'won');
    }

    public function scopeLost($query)
    {
        return $query->where('status', 'lost');
    }

    public function scopeAtRisk($query)
    {
        return $query->where('status', 'risk');
    }

    public function getPriceDifference(): float
    {
        if (!$this->competitor_price) {
            return 0;
        }
        return $this->our_price - $this->competitor_price;
    }

    public function getPriceDifferencePercent(): float
    {
        if (!$this->competitor_price || $this->competitor_price == 0) {
            return 0;
        }
        return (($this->our_price - $this->competitor_price) / $this->competitor_price) * 100;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'won' => '✓ Kazanıldı',
            'lost' => '✗ Kaybedildi',
            'risk' => '⚠ Risk',
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'won' => 'green',
            'lost' => 'red',
            'risk' => 'yellow',
            default => 'gray',
        };
    }
}
