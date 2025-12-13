<?php

namespace CastMart\Trendyol\Models;

use Illuminate\Database\Eloquent\Model;
use CastMart\Marketplace\Models\MarketplaceAccount;

class PriceRule extends Model
{
    protected $table = 'trendyol_price_rules';

    protected $fillable = [
        'marketplace_account_id',
        'name',
        'trigger',
        'action',
        'action_value',
        'scope',
        'scope_data',
        'sku_filter',
        'min_price',
        'max_price',
        'is_active',
        'priority',
        'trigger_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'scope_data' => 'array',
        'is_active' => 'boolean',
        'action_value' => 'decimal:2',
        'last_triggered_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }

    public function histories()
    {
        return $this->hasMany(PriceHistory::class, 'rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function incrementTriggerCount()
    {
        $this->increment('trigger_count');
        $this->update(['last_triggered_at' => now()]);
    }

    public function getTriggerLabel(): string
    {
        return match($this->trigger) {
            'competitor_cheaper' => 'Rakip fiyat < Benim fiyat',
            'buybox_lost' => 'BuyBox kaybedildiğinde',
            'stock_low' => 'Stok azaldığında',
            'competitor_stock_zero' => 'Rakip stok bittiğinde',
            'time_based' => 'Belirli zamanda',
            default => $this->trigger,
        };
    }

    public function getActionLabel(): string
    {
        return match($this->action) {
            'match_minus' => 'Rakibe eşitle (-0.01₺)',
            'decrease_percent' => '%' . $this->action_value . ' düşür',
            'increase_percent' => '%' . $this->action_value . ' artır',
            'set_price' => $this->action_value . '₺ sabit fiyat',
            default => $this->action,
        };
    }
}
