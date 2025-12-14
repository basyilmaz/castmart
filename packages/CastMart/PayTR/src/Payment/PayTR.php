<?php

namespace CastMart\PayTR\Payment;

use Webkul\Payment\Payment\Payment;
use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayTR extends Payment
{
    /**
     * Payment method code
     */
    protected $code = 'paytr';

    /**
     * Get redirect URL
     */
    public function getRedirectUrl()
    {
        return route('paytr.redirect');
    }

    /**
     * Get PayTR credentials
     */
    protected function getMerchantId(): string
    {
        return $this->getConfigData('merchant_id') ?? '';
    }

    protected function getMerchantKey(): string
    {
        return $this->getConfigData('merchant_key') ?? '';
    }

    protected function getMerchantSalt(): string
    {
        return $this->getConfigData('merchant_salt') ?? '';
    }

    protected function isSandbox(): bool
    {
        return (bool) $this->getConfigData('sandbox');
    }

    /**
     * Get payment form (iframe token)
     */
    public function getPaymentForm()
    {
        $cart = $this->getCart();
        
        if (!$cart) {
            return null;
        }

        try {
            $params = $this->createPaymentParams($cart);
            $token = $this->getIframeToken($params);

            if ($token) {
                return [
                    'token' => $token,
                    'iframe_url' => "https://www.paytr.com/odeme/guvenli/" . $token,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayTR form error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create payment parameters
     */
    protected function createPaymentParams($cart): array
    {
        $address = $cart->billing_address ?? $cart->shipping_address;
        
        // Sepet ürünleri (JSON formatında)
        $basketItems = [];
        foreach ($cart->items as $item) {
            $basketItems[] = [
                $item->name,
                $this->formatPrice($item->price),
                $item->quantity
            ];
        }
        
        // Kargo varsa ekle
        if ($cart->selected_shipping_rate && $cart->selected_shipping_rate->price > 0) {
            $basketItems[] = [
                'Kargo Ücreti',
                $this->formatPrice($cart->selected_shipping_rate->price),
                1
            ];
        }

        $userBasket = base64_encode(json_encode($basketItems));
        
        // Sipariş numarası
        $merchantOid = 'SP' . $cart->id . '_' . time();
        
        // Kullanıcı bilgileri
        $email = $cart->customer_email ?? $address->email ?? 'guest@example.com';
        $paymentAmount = (int) ($cart->grand_total * 100); // Kuruş cinsinden
        $userName = ($address->first_name ?? 'Misafir') . ' ' . ($address->last_name ?? 'Kullanıcı');
        $userAddress = $address->address1 ?? 'Adres';
        $userPhone = $this->formatPhone($address->phone ?? '');

        // Test modunda config
        $testMode = $this->isSandbox() ? '1' : '0';
        
        // Taksit ayarları
        $noInstallment = $this->getConfigData('no_installment') ? '1' : '0';
        $maxInstallment = $this->getConfigData('max_installment') ?? '12';

        // Hash string oluştur
        $hashStr = $this->getMerchantId() . 
                   request()->ip() . 
                   $merchantOid . 
                   $email . 
                   $paymentAmount . 
                   $userBasket . 
                   $noInstallment . 
                   $maxInstallment . 
                   'TL' . 
                   $testMode;
        
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $this->getMerchantSalt(), $this->getMerchantKey(), true));

        return [
            'merchant_id' => $this->getMerchantId(),
            'user_ip' => request()->ip(),
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => $paymentAmount,
            'paytr_token' => $paytrToken,
            'user_basket' => $userBasket,
            'debug_on' => $this->isSandbox() ? '1' : '0',
            'no_installment' => $noInstallment,
            'max_installment' => $maxInstallment,
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'merchant_ok_url' => route('paytr.success'),
            'merchant_fail_url' => route('paytr.fail'),
            'timeout_limit' => '30',
            'currency' => 'TL',
            'test_mode' => $testMode,
            'lang' => 'tr',
        ];
    }

    /**
     * Get iframe token from PayTR API
     */
    protected function getIframeToken(array $params): ?string
    {
        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post('https://www.paytr.com/odeme/api/get-token', $params);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['status']) && $result['status'] === 'success') {
                    return $result['token'];
                }
                
                Log::error('PayTR token error', ['response' => $result]);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayTR API error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Verify callback notification
     */
    public function verifyCallback(array $postData): array
    {
        $merchantOid = $postData['merchant_oid'] ?? '';
        $status = $postData['status'] ?? '';
        $totalAmount = $postData['total_amount'] ?? '';
        $hash = $postData['hash'] ?? '';
        
        // Hash doğrulama
        $hashStr = $merchantOid . $this->getMerchantSalt() . $status . $totalAmount;
        $expectedHash = base64_encode(hash_hmac('sha256', $hashStr, $this->getMerchantKey(), true));

        if ($hash !== $expectedHash) {
            Log::error('PayTR hash mismatch', [
                'expected' => $expectedHash, 
                'received' => $hash
            ]);
            return [
                'success' => false,
                'error_message' => 'Hash doğrulama hatası',
            ];
        }

        // Cart ID'yi merchant_oid'den çıkar
        $cartId = null;
        if (preg_match('/^SP(\d+)_/', $merchantOid, $matches)) {
            $cartId = $matches[1];
        }

        return [
            'success' => $status === 'success',
            'merchant_oid' => $merchantOid,
            'cart_id' => $cartId,
            'total_amount' => $totalAmount / 100, // TL'ye çevir
            'status' => $status,
            'payment_type' => $postData['payment_type'] ?? null,
            'currency' => $postData['currency'] ?? 'TL',
            'installment_count' => $postData['installment_count'] ?? 1,
        ];
    }

    /**
     * Refund a payment
     */
    public function refund(string $merchantOid, float $amount): array
    {
        try {
            $refundAmount = (int) ($amount * 100); // Kuruş cinsinden
            
            $hashStr = $this->getMerchantId() . $merchantOid . $refundAmount . $this->getMerchantSalt();
            $paytrToken = base64_encode(hash_hmac('sha256', $hashStr, $this->getMerchantKey(), true));

            $response = Http::asForm()
                ->timeout(30)
                ->post('https://www.paytr.com/odeme/iade', [
                    'merchant_id' => $this->getMerchantId(),
                    'merchant_oid' => $merchantOid,
                    'return_amount' => $refundAmount,
                    'paytr_token' => $paytrToken,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                return [
                    'success' => ($result['status'] ?? '') === 'success',
                    'merchant_oid' => $merchantOid,
                    'refund_amount' => $amount,
                    'is_test' => $result['is_test'] ?? 0,
                    'error_message' => $result['err_msg'] ?? null,
                ];
            }

            return ['success' => false, 'message' => 'API hatası'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get installment options (BIN sorgulama)
     */
    public function getInstallmentOptions(string $binNumber, float $price): array
    {
        try {
            $amount = (int) ($price * 100);
            
            $hashStr = $binNumber . $this->getMerchantId() . $amount . $this->getMerchantSalt();
            $paytrToken = base64_encode(hash_hmac('sha256', $hashStr, $this->getMerchantKey(), true));

            $response = Http::asForm()
                ->timeout(30)
                ->post('https://www.paytr.com/odeme/taksit-oranlari', [
                    'merchant_id' => $this->getMerchantId(),
                    'cc_bin' => $binNumber,
                    'amount' => $amount,
                    'paytr_token' => $paytrToken,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (($result['status'] ?? '') === 'success') {
                    $installments = [];
                    
                    foreach ($result['taksitler'] ?? [] as $taksit) {
                        $installments[] = [
                            'installment_number' => $taksit['taksit_sayisi'],
                            'installment_amount' => $taksit['taksit_tutari'] / 100,
                            'total_amount' => $taksit['toplam_tutar'] / 100,
                            'commission_rate' => $taksit['komisyon_orani'] ?? 0,
                        ];
                    }

                    return [
                        'success' => true,
                        'card_type' => $result['kart_tipi'] ?? null,
                        'card_brand' => $result['kart_marka'] ?? null,
                        'installments' => $installments,
                    ];
                }
            }

            return ['success' => false, 'message' => 'BIN sorgulama başarısız'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format price for PayTR (integer in kuruş)
     */
    protected function formatPrice($price): string
    {
        return number_format((float) $price, 2, '.', '');
    }

    /**
     * Format phone number
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10 && !str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }

        return $phone ?: '05000000000';
    }
}
