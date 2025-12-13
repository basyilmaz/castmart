<?php

namespace CastMart\Tenant\Services;

use CastMart\Tenant\Models\Tenant;
use CastMart\Tenant\Models\TenantSubscription;
use CastMart\Tenant\Facades\Tenant as TenantFacade;
use Illuminate\Support\Facades\Log;

class BillingService
{
    protected array $plans;

    public function __construct()
    {
        $this->plans = config('castmart-tenant.plans', []);
    }

    /**
     * Checkout session oluştur (iyzico)
     */
    public function createCheckoutSession(Tenant $tenant, string $plan, string $billingCycle = 'monthly'): array
    {
        $planDetails = $this->plans[$plan] ?? null;

        if (!$planDetails) {
            return ['success' => false, 'message' => 'Geçersiz plan'];
        }

        $price = $billingCycle === 'yearly' 
            ? $planDetails['price_yearly'] 
            : $planDetails['price_monthly'];

        try {
            $options = $this->getIyzicoOptions();
            
            $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId($tenant->id . '_' . time());
            $request->setPrice($price);
            $request->setPaidPrice($price);
            $request->setCurrency(\Iyzipay\Model\Currency::TL);
            $request->setBasketId('SUB_' . $tenant->id . '_' . $plan);
            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::SUBSCRIPTION);
            $request->setCallbackUrl(route('tenant.billing.callback'));
            
            // Alıcı bilgileri
            $buyer = new \Iyzipay\Model\Buyer();
            $buyer->setId($tenant->owner_id ?? $tenant->id);
            $buyer->setName($tenant->owner?->name ?? $tenant->name);
            $buyer->setSurname('');
            $buyer->setEmail($tenant->owner?->email ?? 'billing@' . $tenant->subdomain . '.com');
            $buyer->setIdentityNumber('11111111111');
            $buyer->setRegistrationAddress($tenant->settings['address'] ?? 'Türkiye');
            $buyer->setCity($tenant->settings['city'] ?? 'İstanbul');
            $buyer->setCountry('Turkey');
            $request->setBuyer($buyer);

            // Adres
            $address = new \Iyzipay\Model\Address();
            $address->setContactName($tenant->name);
            $address->setCity($tenant->settings['city'] ?? 'İstanbul');
            $address->setCountry('Turkey');
            $address->setAddress($tenant->settings['address'] ?? 'Türkiye');
            $request->setShippingAddress($address);
            $request->setBillingAddress($address);

            // Sepet (abonelik)
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId($plan);
            $basketItem->setName($planDetails['name'] . ' Abonelik (' . ($billingCycle === 'yearly' ? 'Yıllık' : 'Aylık') . ')');
            $basketItem->setCategory1('Abonelik');
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
            $basketItem->setPrice($price);
            $request->setBasketItems([$basketItem]);

            // iyzico'ya istek gönder
            $checkoutForm = \Iyzipay\Model\CheckoutFormInitialize::create($request, $options);

            if ($checkoutForm->getStatus() === 'success') {
                // Pending subscription oluştur
                $subscription = TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'plan' => $plan,
                    'billing_cycle' => $billingCycle,
                    'price' => $price,
                    'currency' => 'TRY',
                    'status' => 'pending',
                    'payment_method' => 'iyzico',
                    'starts_at' => now(),
                    'ends_at' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                    'metadata' => [
                        'token' => $checkoutForm->getToken(),
                        'conversation_id' => $request->getConversationId(),
                    ],
                ]);

                return [
                    'success' => true,
                    'checkout_url' => $checkoutForm->getPaymentPageUrl(),
                    'token' => $checkoutForm->getToken(),
                    'subscription_id' => $subscription->id,
                ];
            }

            Log::error('iyzico checkout error', [
                'error_code' => $checkoutForm->getErrorCode(),
                'error_message' => $checkoutForm->getErrorMessage(),
            ]);

            return [
                'success' => false,
                'message' => $checkoutForm->getErrorMessage() ?? 'Ödeme başlatılamadı',
            ];
        } catch (\Exception $e) {
            Log::error('Billing checkout error', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Sistem hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Ödeme callback işle
     */
    public function handleCallback(string $token): array
    {
        try {
            $options = $this->getIyzicoOptions();

            $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setToken($token);

            $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve($request, $options);

            // Subscription bul
            $subscription = TenantSubscription::where('metadata->token', $token)
                ->where('status', 'pending')
                ->first();

            if (!$subscription) {
                return ['success' => false, 'message' => 'Abonelik bulunamadı'];
            }

            if ($checkoutForm->getStatus() === 'success' && $checkoutForm->getPaymentStatus() === 'SUCCESS') {
                // Aboneliği aktif et
                $subscription->update([
                    'status' => 'active',
                    'payment_id' => $checkoutForm->getPaymentId(),
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'payment_id' => $checkoutForm->getPaymentId(),
                        'paid_at' => now()->toIso8601String(),
                    ]),
                ]);

                // Tenant'ı güncelle
                $subscription->tenant->update([
                    'plan' => $subscription->plan,
                    'status' => 'active',
                    'subscription_ends_at' => $subscription->ends_at,
                ]);

                // Cache temizle
                TenantFacade::clearTenantCache($subscription->tenant);

                return [
                    'success' => true,
                    'message' => 'Abonelik başarıyla aktif edildi',
                    'subscription' => $subscription,
                ];
            }

            // Ödeme başarısız
            $subscription->update([
                'status' => 'failed',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'error_code' => $checkoutForm->getErrorCode(),
                    'error_message' => $checkoutForm->getErrorMessage(),
                ]),
            ]);

            return [
                'success' => false,
                'message' => $checkoutForm->getErrorMessage() ?? 'Ödeme başarısız',
            ];
        } catch (\Exception $e) {
            Log::error('Billing callback error', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Callback işleme hatası',
            ];
        }
    }

    /**
     * Abonelik yenile
     */
    public function renewSubscription(TenantSubscription $subscription): array
    {
        $tenant = $subscription->tenant;

        return $this->createCheckoutSession(
            $tenant,
            $subscription->plan,
            $subscription->billing_cycle
        );
    }

    /**
     * Abonelik iptal et
     */
    public function cancelSubscription(TenantSubscription $subscription, string $reason = null): bool
    {
        try {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'cancel_reason' => $reason,
                ]),
            ]);

            // Tenant durumunu trial bitti olarak işaretle (mevcut süre sonuna kadar aktif)
            // Abonelik süresi dolduğunda askıya alınacak

            Log::info('Subscription cancelled', [
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Subscription cancel error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Plan değiştir (upgrade/downgrade)
     */
    public function changePlan(Tenant $tenant, string $newPlan): array
    {
        $currentSubscription = $tenant->activeSubscription();

        if (!$currentSubscription) {
            // Yeni abonelik oluştur
            return $this->createCheckoutSession($tenant, $newPlan, 'monthly');
        }

        $oldPlan = $currentSubscription->plan;
        $oldPrice = $currentSubscription->price;
        $newPlanDetails = $this->plans[$newPlan] ?? null;

        if (!$newPlanDetails) {
            return ['success' => false, 'message' => 'Geçersiz plan'];
        }

        $newPrice = $currentSubscription->billing_cycle === 'yearly'
            ? $newPlanDetails['price_yearly']
            : $newPlanDetails['price_monthly'];

        // Upgrade mı downgrade mı?
        $isUpgrade = $newPrice > $oldPrice;

        if ($isUpgrade) {
            // Upgrade için fark ödemesi al
            $remainingDays = max(1, now()->diffInDays($currentSubscription->ends_at));
            $totalDays = $currentSubscription->billing_cycle === 'yearly' ? 365 : 30;
            $proratedDiff = (($newPrice - $oldPrice) / $totalDays) * $remainingDays;

            // Ödeme al
            return $this->createUpgradeCheckout($tenant, $newPlan, $proratedDiff, $currentSubscription);
        } else {
            // Downgrade - mevcut dönem sonunda değişecek
            $currentSubscription->update([
                'metadata' => array_merge($currentSubscription->metadata ?? [], [
                    'pending_downgrade' => $newPlan,
                    'downgrade_at' => $currentSubscription->ends_at->toIso8601String(),
                ]),
            ]);

            return [
                'success' => true,
                'message' => "Plan, mevcut dönem sonunda ({$currentSubscription->ends_at->format('d.m.Y')}) değiştirilecek.",
            ];
        }
    }

    /**
     * Upgrade checkout oluştur
     */
    protected function createUpgradeCheckout(Tenant $tenant, string $newPlan, float $amount, TenantSubscription $currentSub): array
    {
        // Basit bir ödeme al ve planı güncelle
        // Gerçek uygulamada iyzico ile ödeme alınır

        try {
            // Mevcut aboneliği güncelle
            $currentSub->update([
                'plan' => $newPlan,
                'price' => $this->plans[$newPlan][$currentSub->billing_cycle === 'yearly' ? 'price_yearly' : 'price_monthly'],
            ]);

            $tenant->update(['plan' => $newPlan]);
            TenantFacade::clearTenantCache($tenant);

            return [
                'success' => true,
                'message' => "Plan başarıyla {$this->plans[$newPlan]['name']}'e yükseltildi.",
                'amount_due' => $amount,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Fatura oluştur
     */
    public function generateInvoice(TenantSubscription $subscription): array
    {
        $tenant = $subscription->tenant;
        $planDetails = $this->plans[$subscription->plan] ?? [];

        return [
            'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($subscription->id, 6, '0', STR_PAD_LEFT),
            'date' => $subscription->starts_at->format('d.m.Y'),
            'due_date' => $subscription->starts_at->format('d.m.Y'),
            'tenant' => [
                'name' => $tenant->name,
                'email' => $tenant->owner?->email,
            ],
            'items' => [
                [
                    'description' => ($planDetails['name'] ?? $subscription->plan) . ' Abonelik',
                    'period' => $subscription->billing_cycle === 'yearly' ? 'Yıllık' : 'Aylık',
                    'quantity' => 1,
                    'unit_price' => $subscription->price,
                    'total' => $subscription->price,
                ],
            ],
            'subtotal' => $subscription->price,
            'tax' => $subscription->price * 0.20, // %20 KDV
            'total' => $subscription->price * 1.20,
            'currency' => $subscription->currency,
            'status' => $subscription->status === 'active' ? 'paid' : 'pending',
        ];
    }

    /**
     * iyzico options
     */
    protected function getIyzicoOptions(): \Iyzipay\Options
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey(config('castmart-iyzico.api_key'));
        $options->setSecretKey(config('castmart-iyzico.secret_key'));
        $options->setBaseUrl(config('castmart-iyzico.mode') === 'live' 
            ? 'https://api.iyzipay.com' 
            : 'https://sandbox-api.iyzipay.com');
        
        return $options;
    }
}
