<?php

namespace CastMart\Trendyol\Models;

use Illuminate\Database\Eloquent\Model;
use CastMart\Marketplace\Models\MarketplaceAccount;

class IntelligenceAlert extends Model
{
    protected $table = 'trendyol_intelligence_alerts';

    protected $fillable = [
        'marketplace_account_id',
        'type',
        'category',
        'title',
        'description',
        'product_sku',
        'data',
        'action_type',
        'action_url',
        'is_read',
        'is_dismissed',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(MarketplaceAccount::class, 'marketplace_account_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false);
    }

    public function scopeCritical($query)
    {
        return $query->where('type', 'critical');
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function dismiss()
    {
        $this->update(['is_dismissed' => true]);
    }
}
