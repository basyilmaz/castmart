<?php

namespace CastMart\Tenant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUser extends Model
{
    protected $table = 'tenant_users';

    protected $fillable = [
        'tenant_id',
        'admin_id',
        'role',
        'permissions',
        'is_owner',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_owner' => 'boolean',
    ];

    /**
     * İlişkili tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * İlişkili admin kullanıcısı
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(\Webkul\User\Models\Admin::class, 'admin_id');
    }

    /**
     * Rol metni
     */
    public function getRoleTextAttribute(): string
    {
        $roles = [
            'owner' => 'Sahip',
            'admin' => 'Yönetici',
            'manager' => 'Mağaza Müdürü',
            'staff' => 'Personel',
            'viewer' => 'Görüntüleyici',
        ];

        return $roles[$this->role] ?? $this->role;
    }

    /**
     * Yetki kontrolü
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_owner || $this->role === 'admin') {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Scope: Belirli tenant için
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
