<?php

namespace CastMart\Marketing\Services;

use CastMart\Marketing\Models\Coupon;
use CastMart\Marketing\Models\CouponUsage;
use CastMart\Marketing\Models\LoyaltyAccount;
use CastMart\Marketing\Models\LoyaltyTransaction;
use Illuminate\Support\Facades\Log;

class MarketingService
{
    /*
    |--------------------------------------------------------------------------
    | KUPON İŞLEMLERİ
    |--------------------------------------------------------------------------
    */

    /**
     * Kupon kodu doğrula
     */
    public function validateCoupon(string $code, $customer = null, float $subtotal = 0): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Kupon kodu bulunamadı',
            ];
        }

        if (!$coupon->isValid()) {
            return [
                'valid' => false,
                'message' => 'Kupon geçerli değil veya süresi dolmuş',
            ];
        }

        if ($coupon->min_order_amount && $subtotal < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Minimum sipariş tutarı: ' . number_format($coupon->min_order_amount, 2) . ' ₺',
            ];
        }

        if ($customer) {
            $customerCheck = $coupon->isValidForCustomer($customer);
            if (!$customerCheck['valid']) {
                return $customerCheck;
            }
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Kupon uygulandı: ' . $coupon->formatted_value . ' indirim',
        ];
    }

    /**
     * Kupon kullan
     */
    public function applyCoupon(Coupon $coupon, $customer, $order, float $discountAmount): CouponUsage
    {
        // Kullanım kaydı oluştur
        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
        ]);

        // Kullanım sayısını artır
        $coupon->increment('used_count');

        Log::info('Kupon kullanıldı', [
            'coupon_id' => $coupon->id,
            'customer_id' => $customer->id,
            'discount' => $discountAmount,
        ]);

        return $usage;
    }

    /**
     * Yeni kupon oluştur
     */
    public function createCoupon(array $data): Coupon
    {
        return Coupon::create([
            'code' => $data['code'] ?? Coupon::generateCode(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'percentage',
            'value' => $data['value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_discount_amount' => $data['max_discount_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'usage_per_customer' => $data['usage_per_customer'] ?? 1,
            'starts_at' => $data['starts_at'] ?? now(),
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'first_order_only' => $data['first_order_only'] ?? false,
            'free_shipping' => $data['free_shipping'] ?? false,
            'applicable_products' => $data['applicable_products'] ?? null,
            'applicable_categories' => $data['applicable_categories'] ?? null,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SADAKAT PUANI İŞLEMLERİ
    |--------------------------------------------------------------------------
    */

    /**
     * Müşteri için sadakat hesabı getir veya oluştur
     */
    public function getLoyaltyAccount($customer): LoyaltyAccount
    {
        return LoyaltyAccount::firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'total_points' => 0,
                'available_points' => 0,
                'lifetime_points' => 0,
                'tier' => 'bronze',
            ]
        );
    }

    /**
     * Siparişten puan kazan
     */
    public function earnPointsFromOrder($order): ?LoyaltyTransaction
    {
        if (!config('castmart-marketing.loyalty.enabled', true)) {
            return null;
        }

        $customer = $order->customer;
        if (!$customer) {
            return null;
        }

        $minOrder = config('castmart-marketing.loyalty.min_order_for_points', 50);
        if ($order->grand_total < $minOrder) {
            return null;
        }

        $pointsPerCurrency = config('castmart-marketing.loyalty.points_per_currency', 1);
        $points = (int) floor($order->grand_total * $pointsPerCurrency);

        if ($points <= 0) {
            return null;
        }

        $account = $this->getLoyaltyAccount($customer);

        return $account->addPoints(
            $points,
            'earn',
            "Sipariş #{$order->increment_id} için puan kazanımı",
            $order->id
        );
    }

    /**
     * Puan kullan
     */
    public function redeemPoints($customer, int $points, $order = null): array
    {
        $account = $this->getLoyaltyAccount($customer);

        $minPoints = config('castmart-marketing.loyalty.min_points_to_redeem', 100);
        
        if ($points < $minPoints) {
            return [
                'success' => false,
                'message' => "Minimum {$minPoints} puan kullanabilirsiniz",
            ];
        }

        if ($points > $account->available_points) {
            return [
                'success' => false,
                'message' => 'Yeterli puanınız bulunmuyor',
            ];
        }

        $rate = config('castmart-marketing.loyalty.points_to_currency_rate', 100);
        $discount = $points / $rate;

        $transaction = $account->redeemPoints(
            $points,
            $order ? "Sipariş #{$order->increment_id} için puan kullanımı" : 'Puan kullanımı',
            $order?->id
        );

        return [
            'success' => true,
            'transaction' => $transaction,
            'discount' => $discount,
            'message' => "{$points} puan kullanıldı ({$discount} ₺ indirim)",
        ];
    }

    /**
     * Referral puan ver
     */
    public function processReferralReward($referrerAccount, $refereeAccount, $order): void
    {
        if (!config('castmart-marketing.referral.enabled', true)) {
            return;
        }

        $minOrder = config('castmart-marketing.referral.min_order_amount', 100);
        
        if ($order->grand_total < $minOrder) {
            return;
        }

        // Referrer'a ödül
        $referrerReward = config('castmart-marketing.referral.referrer_reward', 50);
        $referrerPoints = $referrerReward * config('castmart-marketing.loyalty.points_per_currency', 1);
        
        $referrerAccount->addPoints(
            $referrerPoints,
            'referral',
            "Arkadaş davetinden puan kazanımı"
        );

        Log::info('Referral reward processed', [
            'referrer_id' => $referrerAccount->id,
            'referee_id' => $refereeAccount->id,
            'order_id' => $order->id,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | İSTATİSTİKLER
    |--------------------------------------------------------------------------
    */

    /**
     * Kupon istatistikleri
     */
    public function getCouponStats(): array
    {
        return [
            'total' => Coupon::count(),
            'active' => Coupon::active()->count(),
            'used_today' => CouponUsage::whereDate('created_at', today())->count(),
            'total_discount_today' => CouponUsage::whereDate('created_at', today())->sum('discount_amount'),
        ];
    }

    /**
     * Sadakat istatistikleri
     */
    public function getLoyaltyStats(): array
    {
        return [
            'total_members' => LoyaltyAccount::count(),
            'total_points_issued' => LoyaltyTransaction::where('points', '>', 0)->sum('points'),
            'total_points_redeemed' => abs(LoyaltyTransaction::where('points', '<', 0)->sum('points')),
            'tier_distribution' => LoyaltyAccount::selectRaw('tier, count(*) as count')
                ->groupBy('tier')
                ->pluck('count', 'tier')
                ->toArray(),
        ];
    }
}
