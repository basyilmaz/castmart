<?php

namespace CastMart\Shipping\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $table = 'castmart_shipments';

    protected $fillable = [
        'order_id',
        'carrier_code',
        'tracking_number',
        'cargo_key',
        'status',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'receiver_city',
        'receiver_district',
        'piece_count',
        'weight',
        'desi',
        'is_cod',
        'cod_amount',
        'last_location',
        'shipped_at',
        'delivered_at',
        'metadata',
    ];

    protected $casts = [
        'is_cod' => 'boolean',
        'cod_amount' => 'decimal:2',
        'weight' => 'decimal:2',
        'desi' => 'decimal:2',
        'metadata' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * İlişkili sipariş
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Sales\Models\Order::class, 'order_id');
    }

    /**
     * Kargo firması adı
     */
    public function getCarrierNameAttribute(): string
    {
        $carriers = [
            'aras' => 'Aras Kargo',
            'mng' => 'MNG Kargo',
            'yurtici' => 'Yurtiçi Kargo',
        ];

        return $carriers[$this->carrier_code] ?? $this->carrier_code;
    }

    /**
     * Durum metni
     */
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'created' => 'Oluşturuldu',
            'pending' => 'Beklemede',
            'picked_up' => 'Teslim Alındı',
            'in_transit' => 'Yolda',
            'out_for_delivery' => 'Dağıtımda',
            'delivered' => 'Teslim Edildi',
            'returned' => 'İade Edildi',
            'cancelled' => 'İptal Edildi',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Takip linki
     */
    public function getTrackingUrlAttribute(): string
    {
        $urls = [
            'aras' => 'https://kargotakip.araskargo.com.tr/?code=' . $this->tracking_number,
            'mng' => 'https://www.mngkargo.com.tr/gonderi-takip/?gonderino=' . $this->tracking_number,
            'yurtici' => 'https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code=' . $this->tracking_number,
        ];

        return $urls[$this->carrier_code] ?? '#';
    }

    /**
     * Durum rozeti rengi
     */
    public function getStatusColorAttribute(): string
    {
        $colors = [
            'created' => 'gray',
            'pending' => 'yellow',
            'picked_up' => 'blue',
            'in_transit' => 'blue',
            'out_for_delivery' => 'purple',
            'delivered' => 'green',
            'returned' => 'orange',
            'cancelled' => 'red',
        ];

        return $colors[$this->status] ?? 'gray';
    }

    /**
     * Scope: Bekleyen gönderiler
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['created', 'pending']);
    }

    /**
     * Scope: Yoldaki gönderiler
     */
    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['picked_up', 'in_transit', 'out_for_delivery']);
    }

    /**
     * Scope: Teslim edilenler
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }
}
