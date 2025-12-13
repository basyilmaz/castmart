<?php

namespace CastMart\Iyzico\Payment;

use Webkul\Payment\Payment\Payment;
use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Facades\Log;

class Iyzico extends Payment
{
    /**
     * Payment method code
     */
    protected $code = 'iyzico';

    /**
     * iyzico API options
     */
    protected $options;

    /**
     * Get redirect URL
     */
    public function getRedirectUrl()
    {
        return route('iyzico.redirect');
    }

    /**
     * Create iyzico options
     */
    protected function getOptions()
    {
        if ($this->options) {
            return $this->options;
        }

        $this->options = new \Iyzipay\Options();
        $this->options->setApiKey($this->getConfigData('api_key'));
        $this->options->setSecretKey($this->getConfigData('secret_key'));
        
        $mode = $this->getConfigData('sandbox') ? 'sandbox' : 'live';
        $baseUrl = config("iyzico.base_url.{$mode}");
        $this->options->setBaseUrl($baseUrl);

        return $this->options;
    }

    /**
     * Get payment form (for checkout page)
     */
    public function getPaymentForm()
    {
        $cart = $this->getCart();
        
        if (!$cart) {
            return null;
        }

        try {
            $request = $this->createCheckoutFormRequest($cart);
            $checkoutFormInitialize = \Iyzipay\Model\CheckoutFormInitialize::create($request, $this->getOptions());

            if ($checkoutFormInitialize->getStatus() === 'success') {
                return [
                    'token' => $checkoutFormInitialize->getToken(),
                    'checkoutFormContent' => $checkoutFormInitialize->getCheckoutFormContent(),
                    'paymentPageUrl' => $checkoutFormInitialize->getPaymentPageUrl(),
                ];
            }

            Log::error('iyzico form error', [
                'error_code' => $checkoutFormInitialize->getErrorCode(),
                'error_message' => $checkoutFormInitialize->getErrorMessage(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('iyzico exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Create checkout form request
     */
    protected function createCheckoutFormRequest($cart): \Iyzipay\Request\CreateCheckoutFormInitializeRequest
    {
        $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId((string) $cart->id);
        $request->setPrice($this->formatPrice($cart->sub_total));
        $request->setPaidPrice($this->formatPrice($cart->grand_total));
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        
        // Taksit bilgisi
        $enabledInstallments = [];
        $maxInstallments = config('iyzico.installments.max_installments', 12);
        for ($i = 1; $i <= $maxInstallments; $i++) {
            $enabledInstallments[] = $i;
        }
        $request->setEnabledInstallments($enabledInstallments);
        
        // Callback URL
        $request->setCallbackUrl(route('iyzico.callback'));
        
        // Basket ID
        $request->setBasketId((string) $cart->id);
        
        // Payment group
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);

        // Buyer bilgileri
        $buyer = $this->createBuyer($cart);
        $request->setBuyer($buyer);

        // Adres bilgileri
        $shippingAddress = $this->createAddress($cart->shipping_address, 'shipping');
        $request->setShippingAddress($shippingAddress);
        
        $billingAddress = $this->createAddress($cart->billing_address ?? $cart->shipping_address, 'billing');
        $request->setBillingAddress($billingAddress);

        // Sepet ürünleri
        $basketItems = $this->createBasketItems($cart);
        $request->setBasketItems($basketItems);

        return $request;
    }

    /**
     * Create buyer object
     */
    protected function createBuyer($cart): \Iyzipay\Model\Buyer
    {
        $address = $cart->billing_address ?? $cart->shipping_address;
        
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId((string) ($cart->customer_id ?? 'guest_' . $cart->id));
        $buyer->setName($address->first_name ?? 'Misafir');
        $buyer->setSurname($address->last_name ?? 'Kullanıcı');
        $buyer->setGsmNumber($this->formatPhone($address->phone ?? ''));
        $buyer->setEmail($cart->customer_email ?? $address->email ?? 'guest@example.com');
        $buyer->setIdentityNumber('11111111111'); // TC Kimlik No zorunlu
        $buyer->setRegistrationAddress($address->address1 ?? 'Adres');
        $buyer->setIp(request()->ip());
        $buyer->setCity($address->city ?? 'İstanbul');
        $buyer->setCountry('Turkey');
        $buyer->setZipCode($address->postcode ?? '34000');

        return $buyer;
    }

    /**
     * Create address object
     */
    protected function createAddress($address, string $type): \Iyzipay\Model\Address
    {
        $iyzicoAddress = new \Iyzipay\Model\Address();
        $iyzicoAddress->setContactName(($address->first_name ?? '') . ' ' . ($address->last_name ?? ''));
        $iyzicoAddress->setCity($address->city ?? 'İstanbul');
        $iyzicoAddress->setCountry('Turkey');
        $iyzicoAddress->setAddress($address->address1 ?? 'Adres');
        $iyzicoAddress->setZipCode($address->postcode ?? '34000');

        return $iyzicoAddress;
    }

    /**
     * Create basket items
     */
    protected function createBasketItems($cart): array
    {
        $basketItems = [];

        foreach ($cart->items as $item) {
            $basketItem = new \Iyzipay\Model\BasketItem();
            $basketItem->setId((string) $item->id);
            $basketItem->setName($item->name);
            $basketItem->setCategory1($item->product->categories->first()->name ?? 'Genel');
            $basketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
            $basketItem->setPrice($this->formatPrice($item->total));
            
            $basketItems[] = $basketItem;
        }

        // Kargo ücreti varsa sepete ekle
        if ($cart->selected_shipping_rate && $cart->selected_shipping_rate->price > 0) {
            $shippingItem = new \Iyzipay\Model\BasketItem();
            $shippingItem->setId('shipping_' . $cart->id);
            $shippingItem->setName('Kargo Ücreti');
            $shippingItem->setCategory1('Kargo');
            $shippingItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
            $shippingItem->setPrice($this->formatPrice($cart->selected_shipping_rate->price));
            
            $basketItems[] = $shippingItem;
        }

        return $basketItems;
    }

    /**
     * Verify callback and complete payment
     */
    public function verifyCallback(string $token): array
    {
        try {
            $request = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId('');
            $request->setToken($token);

            $result = \Iyzipay\Model\CheckoutForm::retrieve($request, $this->getOptions());

            return [
                'success' => $result->getStatus() === 'success' && $result->getPaymentStatus() === 'SUCCESS',
                'payment_id' => $result->getPaymentId(),
                'basket_id' => $result->getBasketId(),
                'conversation_id' => $result->getConversationId(),
                'price' => $result->getPrice(),
                'paid_price' => $result->getPaidPrice(),
                'installment' => $result->getInstallment(),
                'card_type' => $result->getCardType(),
                'card_association' => $result->getCardAssociation(),
                'card_family' => $result->getCardFamily(),
                'bin_number' => $result->getBinNumber(),
                'last_four_digits' => $result->getLastFourDigits(),
                'fraud_status' => $result->getFraudStatus(),
                'error_code' => $result->getErrorCode(),
                'error_message' => $result->getErrorMessage(),
                'raw_result' => $result->getRawResult(),
            ];
        } catch (\Exception $e) {
            Log::error('iyzico verification error', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get installment options for a BIN number
     */
    public function getInstallmentOptions(string $binNumber, float $price): array
    {
        try {
            $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId(uniqid());
            $request->setBinNumber($binNumber);
            $request->setPrice($this->formatPrice($price));

            $result = \Iyzipay\Model\InstallmentInfo::retrieve($request, $this->getOptions());

            if ($result->getStatus() === 'success') {
                $installmentDetails = $result->getInstallmentDetails();
                $options = [];

                if ($installmentDetails) {
                    foreach ($installmentDetails as $detail) {
                        foreach ($detail->getInstallmentPrices() as $installment) {
                            $options[] = [
                                'installment_number' => $installment->getInstallmentNumber(),
                                'total_price' => $installment->getTotalPrice(),
                                'installment_price' => $installment->getInstallmentPrice(),
                            ];
                        }
                    }
                }

                return [
                    'success' => true,
                    'card_type' => $installmentDetails[0]->getCardType() ?? null,
                    'card_association' => $installmentDetails[0]->getCardAssociation() ?? null,
                    'card_family_name' => $installmentDetails[0]->getCardFamilyName() ?? null,
                    'installments' => $options,
                ];
            }

            return ['success' => false, 'message' => $result->getErrorMessage()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Refund a payment
     */
    public function refund(string $paymentTransactionId, float $price): array
    {
        try {
            $request = new \Iyzipay\Request\CreateRefundRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId(uniqid());
            $request->setPaymentTransactionId($paymentTransactionId);
            $request->setPrice($this->formatPrice($price));
            $request->setCurrency(\Iyzipay\Model\Currency::TL);
            $request->setIp(request()->ip());

            $result = \Iyzipay\Model\Refund::create($request, $this->getOptions());

            return [
                'success' => $result->getStatus() === 'success',
                'payment_id' => $result->getPaymentId(),
                'price' => $result->getPrice(),
                'error_code' => $result->getErrorCode(),
                'error_message' => $result->getErrorMessage(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cancel a payment
     */
    public function cancel(string $paymentId): array
    {
        try {
            $request = new \Iyzipay\Request\CreateCancelRequest();
            $request->setLocale(\Iyzipay\Model\Locale::TR);
            $request->setConversationId(uniqid());
            $request->setPaymentId($paymentId);
            $request->setIp(request()->ip());

            $result = \Iyzipay\Model\Cancel::create($request, $this->getOptions());

            return [
                'success' => $result->getStatus() === 'success',
                'payment_id' => $result->getPaymentId(),
                'error_code' => $result->getErrorCode(),
                'error_message' => $result->getErrorMessage(),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Format price for iyzico (string with 2 decimals)
     */
    protected function formatPrice($price): string
    {
        return number_format((float) $price, 2, '.', '');
    }

    /**
     * Format phone number for iyzico
     */
    protected function formatPhone(string $phone): string
    {
        // Telefon numarasını temizle
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Türkiye formatına çevir
        if (strlen($phone) === 10 && !str_starts_with($phone, '0')) {
            $phone = '+90' . $phone;
        } elseif (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = '+9' . $phone;
        }

        return $phone ?: '+905000000000';
    }
}
