<?php

namespace CastMart\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyAccount extends Model
{
    protected $table = 'loyalty_accounts';

    protected $fillable = [
        'customer_id',
        'total_points',
        'available_points',
        'tier',
        'lifetime_points',
        'referral_code',
        'referred_by',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'available_points' => 'integer',
        'lifetime_points' => 'integer',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->referral_code)) {
                $account->referral_code = self::generateReferralCode();
            }
            if (empty($account->tier)) {
                $account->tier = 'bronze';
            }
        });
    }

    /**
     * Müşteri ilişkisi
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Customer\Models\Customer::class);
    }

    /**
     * Puan işlemleri
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * Referral yapanlar
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by');
    }

    /**
     * Benzersiz referral kodu oluştur
     */
    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Puan ekle
     */
    public function addPoints(int $points, string $type, string $description, $orderId = null): LoyaltyTransaction
    {
        // Multiplier uygula
        $multiplier = $this->getTierMultiplier();
        $earnedPoints = (int) floor($points * $multiplier);

        // Transaction oluştur
        $transaction = $this->transactions()->create([
            'type' => $type,
            'points' => $earnedPoints,
            'balance_after' => $this->available_points + $earnedPoints,
            'description' => $description,
            'order_id' => $orderId,
        ]);

        // Hesabı güncelle
        $this->increment('available_points', $earnedPoints);
        $this->increment('total_points', $earnedPoints);
        $this->increment('lifetime_points', $earnedPoints);

        // Tier güncelle
        $this->updateTier();

        return $transaction;
    }

    /**
     * Puan kullan
     */
    public function redeemPoints(int $points, string $description, $orderId = null): ?LoyaltyTransaction
    {
        if ($points > $this->available_points) {
            return null;
        }

        $transaction = $this->transactions()->create([
            'type' => 'redeem',
            'points' => -$points,
            'balance_after' => $this->available_points - $points,
            'description' => $description,
            'order_id' => $orderId,
        ]);

        $this->decrement('available_points', $points);

        return $transaction;
    }

    /**
     * Tier multiplier
     */
    public function getTierMultiplier(): float
    {
        $tiers = config('castmart-marketing.loyalty.tiers', []);
        return $tiers[$this->tier]['multiplier'] ?? 1.0;
    }

    /**
     * Tier güncelle
     */
    public function updateTier(): void
    {
        $tiers = config('castmart-marketing.loyalty.tiers', []);
        $currentTier = 'bronze';

        foreach ($tiers as $tierKey => $tierConfig) {
            if ($this->lifetime_points >= $tierConfig['min_points']) {
                $currentTier = $tierKey;
            }
        }

        if ($this->tier !== $currentTier) {
            $this->update(['tier' => $currentTier]);
        }
    }

    /**
     * Tier adı
     */
    public function getTierNameAttribute(): string
    {
        $tiers = config('castmart-marketing.loyalty.tiers', []);
        return $tiers[$this->tier]['name'] ?? ucfirst($this->tier);
    }

    /**
     * Sonraki tier
     */
    public function getNextTierAttribute(): ?array
    {
        $tiers = config('castmart-marketing.loyalty.tiers', []);
        $tierKeys = array_keys($tiers);
        $currentIndex = array_search($this->tier, $tierKeys);

        if ($currentIndex !== false && isset($tierKeys[$currentIndex + 1])) {
            $nextTierKey = $tierKeys[$currentIndex + 1];
            return array_merge($tiers[$nextTierKey], ['key' => $nextTierKey]);
        }

        return null;
    }

    /**
     * Sonraki tier'e kalan puan
     */
    public function getPointsToNextTierAttribute(): int
    {
        $nextTier = $this->next_tier;
        
        if (!$nextTier) {
            return 0;
        }

        return max(0, $nextTier['min_points'] - $this->lifetime_points);
    }

    /**
     * Puanın TL karşılığı
     */
    public function getPointsValueAttribute(): float
    {
        $rate = config('castmart-marketing.loyalty.points_to_currency_rate', 100);
        return $this->available_points / $rate;
    }
}
