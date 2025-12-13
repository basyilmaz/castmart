<?php

namespace CastMart\Marketplace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerQuestion extends Model
{
    protected $fillable = [
        'account_id',
        'product_id',
        'external_question_id',
        'external_product_id',
        'question_text',
        'answer_text',
        'status',
        'asked_at',
        'answered_at',
    ];

    protected $casts = [
        'asked_at' => 'datetime',
        'answered_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ANSWERED = 'answered';

    public function account(): BelongsTo
    {
        return $this->belongsTo(MarketplaceAccount::class, 'account_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\Product::class, 'product_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
