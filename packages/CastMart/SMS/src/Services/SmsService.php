<?php

namespace CastMart\SMS\Services;

use CastMart\SMS\Contracts\SmsDriverInterface;
use CastMart\SMS\Channels\NetgsmChannel;
use CastMart\SMS\Channels\IletimerkeziChannel;
use CastMart\SMS\Models\SmsLog;
use CastMart\SMS\Models\OtpVerification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsService
{
    protected array $drivers = [];
    protected ?SmsDriverInterface $driver = null;

    public function __construct()
    {
        $this->registerDrivers();
        $this->setDefaultDriver();
    }

    /**
     * Driver'ları kaydet
     */
    protected function registerDrivers(): void
    {
        $this->drivers = [
            'netgsm' => new NetgsmChannel(),
            'iletimerkezi' => new IletimerkeziChannel(),
        ];
    }

    /**
     * Varsayılan driver'ı ayarla
     */
    protected function setDefaultDriver(): void
    {
        $defaultDriver = config('castmart-sms.default', 'netgsm');
        $this->driver = $this->drivers[$defaultDriver] ?? null;
    }

    /**
     * Belirli bir driver kullan
     */
    public function via(string $driver): self
    {
        if (isset($this->drivers[$driver])) {
            $this->driver = $this->drivers[$driver];
        }
        return $this;
    }

    /**
     * SMS gönder
     */
    public function send(string $to, string $message, array $options = []): array
    {
        if (!$this->driver) {
            return ['success' => false, 'message' => 'SMS driver bulunamadı'];
        }

        if (!$this->driver->isEnabled()) {
            return ['success' => false, 'message' => 'SMS servisi devre dışı'];
        }

        // Günlük limit kontrolü
        if ($this->isDailyLimitReached()) {
            return ['success' => false, 'message' => 'Günlük SMS limiti aşıldı'];
        }

        return $this->driver->send($to, $message, $options);
    }

    /**
     * Toplu SMS gönder
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        if (!$this->driver) {
            return ['success' => false, 'message' => 'SMS driver bulunamadı'];
        }

        return $this->driver->sendBulk($recipients, $message, $options);
    }

    /**
     * OTP oluştur ve gönder
     */
    public function sendOtp(string $phone, int $length = 6): array
    {
        // OTP kodu oluştur
        $code = $this->generateOtpCode($length);
        
        // Önceki OTP'leri iptal et
        OtpVerification::where('phone', $phone)
            ->where('verified', false)
            ->update(['expired' => true]);

        // Yeni OTP kaydet
        $otp = OtpVerification::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(config('castmart-sms.notifications.otp_expiry_minutes', 5)),
        ]);

        // SMS gönder
        $result = $this->driver->sendOtp($phone, $code);

        if ($result['success']) {
            $otp->update(['message_id' => $result['message_id'] ?? null]);
        }

        return [
            'success' => $result['success'],
            'otp_id' => $otp->id,
            'expires_at' => $otp->expires_at->toIso8601String(),
            'message' => $result['success'] ? 'OTP gönderildi' : ($result['message'] ?? 'OTP gönderilemedi'),
        ];
    }

    /**
     * OTP doğrula
     */
    public function verifyOtp(string $phone, string $code): array
    {
        $otp = OtpVerification::where('phone', $phone)
            ->where('code', $code)
            ->where('verified', false)
            ->where('expired', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return [
                'success' => false,
                'message' => 'Geçersiz veya süresi dolmuş doğrulama kodu',
            ];
        }

        $otp->update([
            'verified' => true,
            'verified_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Doğrulama başarılı',
        ];
    }

    /**
     * Sipariş bildirimi gönder
     */
    public function sendOrderNotification(string $type, $order): array
    {
        $notificationEnabled = config("castmart-sms.notifications.order_{$type}", false);
        
        if (!$notificationEnabled) {
            return ['success' => false, 'message' => 'Bu bildirim türü devre dışı'];
        }

        $template = config("castmart-sms.templates.order_{$type}");
        
        if (!$template) {
            return ['success' => false, 'message' => 'Şablon bulunamadı'];
        }

        // Şablonu doldur
        $message = $this->parseTemplate($template, [
            'customer_name' => $order->customer_first_name ?? 'Değerli Müşteri',
            'order_id' => $order->increment_id ?? $order->id,
            'tracking_number' => $order->shipments->first()?->tracking_number ?? '',
            'total' => number_format($order->grand_total, 2) . ' ₺',
        ]);

        // Telefon numarası
        $phone = $order->shipping_address->phone ?? $order->billing_address->phone ?? null;

        if (!$phone) {
            return ['success' => false, 'message' => 'Telefon numarası bulunamadı'];
        }

        return $this->send($phone, $message);
    }

    /**
     * Hoş geldin SMS'i gönder
     */
    public function sendWelcome(string $phone, string $customerName): array
    {
        $template = config('castmart-sms.templates.welcome');
        $message = $this->parseTemplate($template, [
            'customer_name' => $customerName,
        ]);

        return $this->send($phone, $message);
    }

    /**
     * Bakiye sorgula
     */
    public function getBalance(): array
    {
        if (!$this->driver) {
            return ['success' => false, 'message' => 'SMS driver bulunamadı'];
        }

        return $this->driver->getBalance();
    }

    /**
     * SMS durumu sorgula
     */
    public function getStatus(string $messageId): array
    {
        if (!$this->driver) {
            return ['success' => false, 'message' => 'SMS driver bulunamadı'];
        }

        return $this->driver->getStatus($messageId);
    }

    /**
     * Aktif driver'ları getir
     */
    public function getEnabledDrivers(): array
    {
        return array_filter($this->drivers, fn($driver) => $driver->isEnabled());
    }

    /**
     * Bugünkü SMS sayısını getir
     */
    public function getTodayCount(): int
    {
        return SmsLog::where('created_at', '>=', now()->startOfDay())->count();
    }

    /**
     * Günlük limit kontrolü
     */
    protected function isDailyLimitReached(): bool
    {
        $limit = config('castmart-sms.daily_limit', 1000);
        return $this->getTodayCount() >= $limit;
    }

    /**
     * OTP kodu oluştur
     */
    protected function generateOtpCode(int $length): string
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return (string) random_int($min, $max);
    }

    /**
     * Şablonu parse et
     */
    protected function parseTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }
}
