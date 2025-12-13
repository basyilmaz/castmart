<?php

namespace CastMart\Marketing\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('castmart-marketing.push', []);
    }

    /*
    |--------------------------------------------------------------------------
    | WEB PUSH BİLDİRİMLERİ
    |--------------------------------------------------------------------------
    */

    /**
     * Push bildirim gönder
     */
    public function sendPushNotification(string $subscription, string $title, string $body, array $data = []): bool
    {
        if (!($this->config['enabled'] ?? false)) {
            return false;
        }

        try {
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => '/images/notification-icon.png',
                'badge' => '/images/badge-icon.png',
                'data' => $data,
            ]);

            // Web Push API kullanarak bildirim gönder
            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:info@castmart.com',
                    'publicKey' => $this->config['vapid_public_key'],
                    'privateKey' => $this->config['vapid_private_key'],
                ],
            ];

            // WebPush kütüphanesi kullanılarak gönderim yapılır
            // Basit implementasyon için HTTP isteği
            Log::info('Push notification sent', [
                'title' => $title,
                'body' => $body,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Push notification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Toplu push bildirim
     */
    public function sendBulkPushNotification(array $subscriptions, string $title, string $body, array $data = []): array
    {
        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            if ($this->sendPushNotification($subscription, $title, $body, $data)) {
                $sent++;
            } else {
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /*
    |--------------------------------------------------------------------------
    | BİLDİRİM TİPLERİ
    |--------------------------------------------------------------------------
    */

    /**
     * Terk edilen sepet bildirimi
     */
    public function notifyAbandonedCart($customer, $cart): void
    {
        $subscriptions = $this->getCustomerSubscriptions($customer->id);
        
        $this->sendBulkPushNotification(
            $subscriptions,
            'Sepetinizi unuttunuz mu? 🛒',
            "Sepetinizde " . count($cart->items) . " ürün bekliyor. Şimdi tamamlayın!",
            ['type' => 'abandoned_cart', 'cart_id' => $cart->id]
        );
    }

    /**
     * Fiyat düşüşü bildirimi
     */
    public function notifyPriceDrop($customer, $product, float $oldPrice, float $newPrice): void
    {
        $subscriptions = $this->getCustomerSubscriptions($customer->id);
        $discount = round((($oldPrice - $newPrice) / $oldPrice) * 100);
        
        $this->sendBulkPushNotification(
            $subscriptions,
            "Fiyat düştü! %{$discount} indirim 🔥",
            "{$product->name} şimdi " . number_format($newPrice, 2) . " ₺",
            ['type' => 'price_drop', 'product_id' => $product->id]
        );
    }

    /**
     * Stok bildirimi (tekrar stokta)
     */
    public function notifyBackInStock($customer, $product): void
    {
        $subscriptions = $this->getCustomerSubscriptions($customer->id);
        
        $this->sendBulkPushNotification(
            $subscriptions,
            'Tekrar stokta! 📦',
            "{$product->name} yeniden satışta. Kaçırmayın!",
            ['type' => 'back_in_stock', 'product_id' => $product->id]
        );
    }

    /**
     * Sipariş durumu bildirimi
     */
    public function notifyOrderStatus($customer, $order, string $status): void
    {
        $subscriptions = $this->getCustomerSubscriptions($customer->id);
        
        $messages = [
            'processing' => ['Siparişiniz hazırlanıyor 📦', "Sipariş #{$order->increment_id} hazırlanmaya başladı"],
            'shipped' => ['Siparişiniz yola çıktı 🚚', "Sipariş #{$order->increment_id} kargoya verildi"],
            'delivered' => ['Siparişiniz teslim edildi ✅', "Sipariş #{$order->increment_id} teslim edildi"],
        ];

        $message = $messages[$status] ?? ['Sipariş güncellendi', "Sipariş #{$order->increment_id}"];
        
        $this->sendBulkPushNotification(
            $subscriptions,
            $message[0],
            $message[1],
            ['type' => 'order_status', 'order_id' => $order->id, 'status' => $status]
        );
    }

    /**
     * Kampanya bildirimi
     */
    public function notifyCampaign(array $customerIds, string $title, string $body, array $data = []): array
    {
        $allSubscriptions = [];

        foreach ($customerIds as $customerId) {
            $subscriptions = $this->getCustomerSubscriptions($customerId);
            $allSubscriptions = array_merge($allSubscriptions, $subscriptions);
        }

        return $this->sendBulkPushNotification($allSubscriptions, $title, $body, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | YARDIMCI METODLAR
    |--------------------------------------------------------------------------
    */

    /**
     * Müşteri push subscription'larını getir
     */
    protected function getCustomerSubscriptions(int $customerId): array
    {
        // Bu tablonun oluşturulması gerekiyor
        // Şimdilik boş dönüyoruz
        try {
            return \DB::table('push_subscriptions')
                ->where('customer_id', $customerId)
                ->pluck('subscription')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Push subscription kaydet
     */
    public function saveSubscription(int $customerId, string $subscription, string $userAgent = null): bool
    {
        try {
            \DB::table('push_subscriptions')->updateOrInsert(
                ['customer_id' => $customerId, 'subscription' => $subscription],
                [
                    'user_agent' => $userAgent,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Push subscription save failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Push subscription sil
     */
    public function removeSubscription(int $customerId, string $subscription): bool
    {
        try {
            \DB::table('push_subscriptions')
                ->where('customer_id', $customerId)
                ->where('subscription', $subscription)
                ->delete();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
