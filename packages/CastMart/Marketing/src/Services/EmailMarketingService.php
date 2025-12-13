<?php

namespace CastMart\Marketing\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;

class EmailMarketingService
{
    /*
    |--------------------------------------------------------------------------
    | OTOMATİK EMAIL'LER
    |--------------------------------------------------------------------------
    */

    /**
     * Hoş geldin emaili
     */
    public function sendWelcomeEmail(Customer $customer): bool
    {
        if (!config('castmart-marketing.email.automated.welcome', true)) {
            return false;
        }

        try {
            Mail::send('castmart-marketing::emails.welcome', [
                'customer' => $customer,
            ], function ($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                    ->subject('CastMart\'a Hoş Geldiniz! 🎉');
            });

            Log::info('Welcome email sent', ['customer_id' => $customer->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Welcome email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Terk edilen sepet hatırlatması
     */
    public function sendAbandonedCartEmail($cart): bool
    {
        if (!config('castmart-marketing.email.automated.abandoned_cart', true)) {
            return false;
        }

        $customer = $cart->customer;
        if (!$customer || !$customer->email) {
            return false;
        }

        try {
            Mail::send('castmart-marketing::emails.abandoned-cart', [
                'customer' => $customer,
                'cart' => $cart,
                'items' => $cart->items,
                'total' => $cart->grand_total,
            ], function ($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                    ->subject('Sepetinizde ürünler bekliyor! 🛒');
            });

            Log::info('Abandoned cart email sent', ['cart_id' => $cart->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Abandoned cart email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Sipariş sonrası takip emaili
     */
    public function sendOrderFollowupEmail(Order $order): bool
    {
        if (!config('castmart-marketing.email.automated.order_followup', true)) {
            return false;
        }

        $customer = $order->customer;
        if (!$customer || !$customer->email) {
            return false;
        }

        try {
            Mail::send('castmart-marketing::emails.order-followup', [
                'customer' => $customer,
                'order' => $order,
            ], function ($message) use ($customer, $order) {
                $message->to($customer->email, $customer->name)
                    ->subject("Siparişiniz nasıldı? #{$order->increment_id}");
            });

            Log::info('Order followup email sent', ['order_id' => $order->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Order followup email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Doğum günü emaili
     */
    public function sendBirthdayEmail(Customer $customer): bool
    {
        if (!config('castmart-marketing.email.automated.birthday', true)) {
            return false;
        }

        try {
            Mail::send('castmart-marketing::emails.birthday', [
                'customer' => $customer,
            ], function ($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                    ->subject("Doğum Gününüz Kutlu Olsun! 🎂 Özel hediyeniz var!");
            });

            Log::info('Birthday email sent', ['customer_id' => $customer->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Birthday email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * İnaktif müşteri win-back emaili
     */
    public function sendWinBackEmail(Customer $customer): bool
    {
        if (!config('castmart-marketing.email.automated.win_back', true)) {
            return false;
        }

        try {
            Mail::send('castmart-marketing::emails.win-back', [
                'customer' => $customer,
            ], function ($message) use ($customer) {
                $message->to($customer->email, $customer->name)
                    ->subject("Sizi özledik! 💝 Size özel indirim");
            });

            Log::info('Win-back email sent', ['customer_id' => $customer->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Win-back email failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TOPLU EMAIL
    |--------------------------------------------------------------------------
    */

    /**
     * Toplu kampanya emaili gönder
     */
    public function sendCampaignEmail(array $customerIds, string $subject, string $template, array $data = []): array
    {
        $sent = 0;
        $failed = 0;

        $customers = Customer::whereIn('id', $customerIds)
            ->where('subscribed_to_news_letter', true)
            ->get();

        foreach ($customers as $customer) {
            try {
                Mail::send($template, array_merge($data, ['customer' => $customer]), function ($message) use ($customer, $subject) {
                    $message->to($customer->email, $customer->name)->subject($subject);
                });
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                Log::warning('Campaign email failed', [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'total' => count($customers),
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    /**
     * Segment bazlı email gönder
     */
    public function sendToSegment(string $segment, string $subject, string $template, array $data = []): array
    {
        $query = Customer::query()->where('subscribed_to_news_letter', true);

        switch ($segment) {
            case 'new':
                // Son 30 günde kayıt olanlar
                $query->where('created_at', '>=', now()->subDays(30));
                break;
            
            case 'active':
                // Son 30 günde sipariş verenler
                $query->whereHas('orders', fn($q) => $q->where('created_at', '>=', now()->subDays(30)));
                break;
            
            case 'inactive':
                // 60+ gündür sipariş vermeyenler
                $query->whereDoesntHave('orders', fn($q) => $q->where('created_at', '>=', now()->subDays(60)));
                break;
            
            case 'vip':
                // Yüksek harcama yapanlar
                $query->whereHas('orders', function ($q) {
                    $q->selectRaw('SUM(grand_total) as total')
                      ->havingRaw('SUM(grand_total) > 5000');
                });
                break;
        }

        $customerIds = $query->pluck('id')->toArray();
        
        return $this->sendCampaignEmail($customerIds, $subject, $template, $data);
    }

    /*
    |--------------------------------------------------------------------------
    | HATIRLATMALAR
    |--------------------------------------------------------------------------
    */

    /**
     * Terk edilen sepetleri kontrol et ve email gönder
     */
    public function processAbandonedCarts(): int
    {
        $delay = config('castmart-marketing.email.abandoned_cart_delay', 2);
        
        // X saat önce güncellenen, hala aktif olan sepetler
        $carts = \Webkul\Checkout\Models\Cart::query()
            ->whereNotNull('customer_id')
            ->where('is_active', true)
            ->where('updated_at', '<=', now()->subHours($delay))
            ->where('updated_at', '>=', now()->subHours($delay + 24)) // Son 24 saat içinde
            ->whereDoesntHave('order') // Sipariş olmamış
            ->with(['customer', 'items'])
            ->get();

        $count = 0;
        foreach ($carts as $cart) {
            if ($this->sendAbandonedCartEmail($cart)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Doğum günü emaillerini gönder
     */
    public function processBirthdayEmails(): int
    {
        $customers = Customer::query()
            ->whereRaw('DAY(date_of_birth) = ?', [now()->day])
            ->whereRaw('MONTH(date_of_birth) = ?', [now()->month])
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            if ($this->sendBirthdayEmail($customer)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * İnaktif müşterilere win-back email gönder
     */
    public function processWinBackEmails(): int
    {
        $days = config('castmart-marketing.email.win_back_days', 60);

        $customers = Customer::query()
            ->whereHas('orders') // En az 1 siparişi olan
            ->whereDoesntHave('orders', fn($q) => $q->where('created_at', '>=', now()->subDays($days)))
            ->get();

        $count = 0;
        foreach ($customers as $customer) {
            if ($this->sendWinBackEmail($customer)) {
                $count++;
            }
        }

        return $count;
    }
}
