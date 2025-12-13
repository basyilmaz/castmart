<?php

namespace CastMart\SMS\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $table = 'castmart_otp_verifications';

    protected $fillable = [
        'phone',
        'code',
        'message_id',
        'expires_at',
        'verified',
        'verified_at',
        'expired',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'verified' => 'boolean',
        'expired' => 'boolean',
    ];

    /**
     * OTP geçerli mi?
     */
    public function isValid(): bool
    {
        return !$this->verified 
            && !$this->expired 
            && $this->expires_at->isFuture();
    }

    /**
     * OTP'yi doğrula
     */
    public function verify(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * OTP'yi expire et
     */
    public function expire(): void
    {
        $this->update(['expired' => true]);
    }

    /**
     * Scope: Aktif (doğrulanmamış ve süresi dolmamış)
     */
    public function scopeActive($query)
    {
        return $query->where('verified', false)
            ->where('expired', false)
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Belirli telefon
     */
    public function scopeForPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }
}
