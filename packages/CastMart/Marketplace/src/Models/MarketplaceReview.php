<?php

namespace CastMart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceReview extends Model
{
    protected $table = 'marketplace_reviews';

    protected $fillable = [
        'account_id',
        'product_id',
        'external_product_id',
        'rating',
        'comment',
        'reviewer_name',
        'has_purchase',
        'review_date',
        'scraped_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'has_purchase' => 'boolean',
        'review_date' => 'date',
        'scraped_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'account_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\Product::class, 'product_id');
    }

    /**
     * Hesap için ortalama puanı hesapla
     */
    public static function getAverageRatingForAccount(int $accountId): float
    {
        $avg = static::where('account_id', $accountId)->avg('rating');
        return $avg ? round($avg, 2) : 0.0;
    }

    /**
     * Son 30 günün ortalama puanı
     */
    public static function getRecentAverageRating(int $accountId, int $days = 30): float
    {
        $avg = static::where('account_id', $accountId)
            ->where('created_at', '>=', now()->subDays($days))
            ->avg('rating');
        return $avg ? round($avg, 2) : 0.0;
    }

    public function scopePositive($query)
    {
        return $query->where('rating', '>=', 4);
    }

    public function scopeNegative($query)
    {
        return $query->where('rating', '<=', 2);
    }
}
