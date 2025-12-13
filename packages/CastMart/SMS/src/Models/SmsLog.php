<?php

namespace CastMart\SMS\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $table = 'castmart_sms_logs';

    protected $fillable = [
        'phone',
        'message',
        'message_id',
        'provider',
        'status',
        'type',
        'cost',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'cost' => 'decimal:4',
    ];

    /**
     * Scope: Bugünkü SMS'ler
     */
    public function scopeToday($query)
    {
        return $query->where('created_at', '>=', now()->startOfDay());
    }

    /**
     * Scope: Başarılı SMS'ler
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: Başarısız SMS'ler
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Belirli bir sağlayıcı
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Durum metni
     */
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'sent' => 'Gönderildi',
            'delivered' => 'Teslim Edildi',
            'failed' => 'Başarısız',
            'pending' => 'Bekliyor',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
