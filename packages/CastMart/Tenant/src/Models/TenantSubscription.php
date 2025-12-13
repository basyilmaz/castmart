<?php

namespace CastMart\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    protected $table = 'tenant_subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan',
        'billing_cycle',
        'price',
        'currency',
        'status',
        'payment_method',
        'payment_id',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * İlişkili tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Abonelik aktif mi?
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    /**
     * Abonelik iptal edildi mi?
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    /**
     * Kalan gün sayısı
     */
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->ends_at) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    /**
     * Plan bilgilerini getir
     */
    public function getPlanDetailsAttribute(): array
    {
        $plans = config('castmart-tenant.plans');
        return $plans[$this->plan] ?? [];
    }

    /**
     * Durum metni
     */
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'active' => 'Aktif',
            'cancelled' => 'İptal Edildi',
            'expired' => 'Süresi Doldu',
            'pending' => 'Ödeme Bekliyor',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Scope: Aktif abonelikler
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('ends_at', '>', now());
    }

    /**
     * Scope: Süresi yaklaşanlar
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }
}
