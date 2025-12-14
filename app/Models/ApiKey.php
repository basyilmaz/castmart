<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'key',
        'prefix',
        'user_id',
        'user_type',
        'permissions',
        'rate_limits',
        'ip_whitelist',
        'last_used_at',
        'last_used_ip',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'rate_limits' => 'array',
        'ip_whitelist' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'key',
    ];

    /**
     * Yeni API key oluştur
     */
    public static function generate(array $attributes = []): array
    {
        $plainTextKey = 'cast_' . Str::random(32);
        $prefix = substr($plainTextKey, 0, 8);
        
        $apiKey = static::create(array_merge($attributes, [
            'key' => hash('sha256', $plainTextKey),
            'prefix' => $prefix,
        ]));

        return [
            'api_key' => $apiKey,
            'plain_text_key' => $plainTextKey, // Sadece bir kez gösterilir!
        ];
    }

    /**
     * API key doğrula
     */
    public static function validate(string $plainTextKey): ?self
    {
        $hashedKey = hash('sha256', $plainTextKey);

        $apiKey = static::where('key', $hashedKey)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($apiKey) {
            $apiKey->recordUsage();
        }

        return $apiKey;
    }

    /**
     * Kullanım kaydı
     */
    public function recordUsage(string $endpoint = null, string $method = null, int $responseCode = 200, int $responseTime = 0): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => request()->ip(),
        ]);

        if ($endpoint) {
            \DB::table('api_key_logs')->insert([
                'api_key_id' => $this->id,
                'endpoint' => $endpoint,
                'method' => $method ?? 'GET',
                'ip' => request()->ip(),
                'response_code' => $responseCode,
                'response_time_ms' => $responseTime,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * İzin kontrolü
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return true; // Boş = tüm izinler
        }

        return in_array($permission, $this->permissions) || 
               in_array('*', $this->permissions);
    }

    /**
     * IP whitelist kontrolü
     */
    public function isIpAllowed(string $ip): bool
    {
        if (empty($this->ip_whitelist)) {
            return true; // Boş = tüm IP'ler
        }

        return in_array($ip, $this->ip_whitelist);
    }

    /**
     * Key süresinin dolup dolmadığını kontrol et
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Key'i deaktive et
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Key'i yeniden oluştur (rotate)
     */
    public function rotate(): string
    {
        $plainTextKey = 'cast_' . Str::random(32);
        
        $this->update([
            'key' => hash('sha256', $plainTextKey),
            'prefix' => substr($plainTextKey, 0, 8),
        ]);

        return $plainTextKey;
    }

    /**
     * Kullanım istatistikleri
     */
    public function getUsageStats(int $days = 30): array
    {
        return \DB::table('api_key_logs')
            ->where('api_key_id', $this->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(response_time_ms) as avg_time')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * Masked key (görüntüleme için)
     */
    public function getMaskedKeyAttribute(): string
    {
        return $this->prefix . '...' . str_repeat('*', 8);
    }
}
