<?php

namespace CastMart\Trendyol\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRate extends Model
{
    protected $table = 'trendyol_commission_rates';

    protected $fillable = [
        'category_name',
        'category_id',
        'commission_rate',
        'service_fee',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Kategori adına göre komisyon oranını bul
     */
    public static function findByCategory(string $categoryName): ?self
    {
        // Tam eşleşme
        $rate = self::active()
            ->where('category_name', $categoryName)
            ->first();

        if ($rate) {
            return $rate;
        }

        // Kısmi eşleşme
        $rate = self::active()
            ->whereRaw('LOWER(category_name) LIKE ?', ['%' . strtolower($categoryName) . '%'])
            ->first();

        return $rate;
    }

    /**
     * Komisyon oranını yüzde olarak al
     */
    public function getCommissionPercent(): float
    {
        return $this->commission_rate;
    }

    /**
     * Toplam kesinti oranını hesapla (komisyon + hizmet bedeli yüzdesi)
     */
    public function getTotalDeductionRate(float $salePrice): float
    {
        $commissionAmount = $salePrice * ($this->commission_rate / 100);
        $totalDeduction = $commissionAmount + $this->service_fee;
        
        return $salePrice > 0 ? ($totalDeduction / $salePrice) * 100 : 0;
    }
}
