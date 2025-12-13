<?php

namespace CastMart\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type', // percentage, fixed, free_shipping, buy_x_get_y
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_per_customer',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'conditions',
        'applicable_products',
        'applicable_categories',
        'excluded_products',
        'applicable_customer_groups',
        'first_order_only',
        'free_shipping',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_customer' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'conditions' => 'array',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'excluded_products' => 'array',
        'applicable_customer_groups' => 'array',
        'first_order_only' => 'boolean',
        'free_shipping' => 'boolean',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($coupon) {
            if (empty($coupon->code)) {
                $coupon->code = self::generateCode();
            }
        });
    }

    /**
     * Kupon kullanımları
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Benzersiz kupon kodu oluştur
     */
    public static function generateCode(int $length = null): string
    {
        $prefix = config('castmart-marketing.coupons.code_prefix', 'CM');
        $length = $length ?? config('castmart-marketing.coupons.code_length', 8);

        do {
            $code = $prefix . strtoupper(Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Kupon aktif ve geçerli mi?
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Müşteri için kupon geçerli mi?
     */
    public function isValidForCustomer($customer): array
    {
        if (!$this->isValid()) {
            return ['valid' => false, 'message' => 'Kupon geçerli değil veya süresi dolmuş'];
        }

        // Müşteri kullanım limiti
        if ($this->usage_per_customer) {
            $customerUsages = $this->usages()
                ->where('customer_id', $customer->id)
                ->count();

            if ($customerUsages >= $this->usage_per_customer) {
                return ['valid' => false, 'message' => 'Bu kuponu maksimum kullanım sayısına ulaştınız'];
            }
        }

        // İlk sipariş kontrolü
        if ($this->first_order_only) {
            $orderCount = $customer->orders()->count();
            if ($orderCount > 0) {
                return ['valid' => false, 'message' => 'Bu kupon sadece ilk siparişte geçerlidir'];
            }
        }

        // Müşteri grubu kontrolü
        if (!empty($this->applicable_customer_groups)) {
            if (!in_array($customer->customer_group_id, $this->applicable_customer_groups)) {
                return ['valid' => false, 'message' => 'Bu kupon sizin müşteri grubunuz için geçerli değil'];
            }
        }

        return ['valid' => true, 'message' => 'Kupon geçerli'];
    }

    /**
     * İndirim hesapla
     */
    public function calculateDiscount(float $subtotal, array $cartItems = []): float
    {
        if ($this->min_order_amount && $subtotal < $this->min_order_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->type) {
            case 'percentage':
                $discount = $subtotal * ($this->value / 100);
                break;
            
            case 'fixed':
                $discount = $this->value;
                break;
            
            case 'free_shipping':
                // Kargo indirimi ayrıca işlenir
                $discount = 0;
                break;
            
            case 'buy_x_get_y':
                // X al Y öde - conditions'ta x ve y değerleri
                $discount = $this->calculateBuyXGetY($cartItems);
                break;
        }

        // Maksimum indirim sınırı
        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        // İndirim toplam tutarı geçemez
        return min($discount, $subtotal);
    }

    /**
     * X al Y öde hesapla
     */
    protected function calculateBuyXGetY(array $cartItems): float
    {
        $conditions = $this->conditions ?? [];
        $buyQuantity = $conditions['buy_quantity'] ?? 2;
        $getQuantity = $conditions['get_quantity'] ?? 1;

        // Uygulanabilir ürünleri bul
        $applicableItems = collect($cartItems)->filter(function ($item) {
            if (empty($this->applicable_products) && empty($this->applicable_categories)) {
                return true;
            }
            
            if (!empty($this->applicable_products) && in_array($item['product_id'], $this->applicable_products)) {
                return true;
            }
            
            return false;
        });

        $totalDiscount = 0;
        foreach ($applicableItems as $item) {
            $sets = floor($item['quantity'] / ($buyQuantity + $getQuantity));
            $totalDiscount += $sets * $getQuantity * $item['price'];
        }

        return $totalDiscount;
    }

    /**
     * Kupon tipinin metni
     */
    public function getTypeTextAttribute(): string
    {
        $types = config('castmart-marketing.coupons.types', []);
        return $types[$this->type] ?? $this->type;
    }

    /**
     * İndirim değeri formatı
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->type === 'percentage') {
            return '%' . number_format($this->value, 0);
        }
        
        if ($this->type === 'free_shipping') {
            return 'Ücretsiz Kargo';
        }
        
        return number_format($this->value, 2) . ' ₺';
    }

    /**
     * Scope: Aktif kuponlar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Kullanılabilir kuponlar
     */
    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            });
    }
}
