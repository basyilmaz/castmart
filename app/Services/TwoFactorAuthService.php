<?php

namespace App\Services;

use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Yeni 2FA secret key oluştur
     */
    public function generateSecretKey(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * QR Code URL oluştur (Google Authenticator için)
     */
    public function getQRCodeUrl(string $email, string $secretKey, string $appName = null): string
    {
        $appName = $appName ?? config('app.name', 'CastMart');
        
        return $this->google2fa->getQRCodeUrl(
            $appName,
            $email,
            $secretKey
        );
    }

    /**
     * QR Code inline SVG oluştur
     */
    public function getQRCodeSvg(string $email, string $secretKey): string
    {
        $url = $this->getQRCodeUrl($email, $secretKey);
        
        // Simple QR Code generation using a basic approach
        // In production, use a proper QR code library
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($url);
        
        return '<img src="' . $qrCodeUrl . '" alt="QR Code" style="width:200px;height:200px;">';
    }

    /**
     * OTP kodunu doğrula
     */
    public function verifyCode(string $secretKey, string $code): bool
    {
        return $this->google2fa->verifyKey($secretKey, $code);
    }

    /**
     * 2FA'yı etkinleştir (Müşteri için)
     */
    public function enableForCustomer(Customer $customer, string $secretKey, string $code): bool
    {
        if (!$this->verifyCode($secretKey, $code)) {
            return false;
        }

        $customer->update([
            'two_factor_secret' => encrypt($secretKey),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        // Recovery kodları oluştur
        $this->generateRecoveryCodes($customer, 'customer');

        return true;
    }

    /**
     * 2FA'yı etkinleştir (Admin için)
     */
    public function enableForAdmin(Admin $admin, string $secretKey, string $code): bool
    {
        if (!$this->verifyCode($secretKey, $code)) {
            return false;
        }

        $admin->update([
            'two_factor_secret' => encrypt($secretKey),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ]);

        $this->generateRecoveryCodes($admin, 'admin');

        return true;
    }

    /**
     * 2FA'yı devre dışı bırak
     */
    public function disable($user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    /**
     * Recovery kodları oluştur
     */
    public function generateRecoveryCodes($user, string $type): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ]);

        return $codes;
    }

    /**
     * Recovery kodu ile doğrula
     */
    public function verifyRecoveryCode($user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        
        if (!is_array($codes)) {
            return false;
        }

        $index = array_search(strtoupper($code), array_map('strtoupper', $codes));
        
        if ($index === false) {
            return false;
        }

        // Kullanılan kodu sil
        unset($codes[$index]);
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
        ]);

        return true;
    }

    /**
     * E-posta ile OTP gönder (alternatif 2FA)
     */
    public function sendEmailOTP(string $email, string $type = 'customer'): string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Cache'e kaydet (5 dakika geçerli)
        $cacheKey = "2fa_email_otp:{$type}:{$email}";
        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        // E-posta gönder
        Mail::send('emails.two-factor-otp', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Doğrulama Kodu - ' . config('app.name'));
        });

        return $otp;
    }

    /**
     * E-posta OTP doğrula
     */
    public function verifyEmailOTP(string $email, string $otp, string $type = 'customer'): bool
    {
        $cacheKey = "2fa_email_otp:{$type}:{$email}";
        $storedOtp = Cache::get($cacheKey);

        if ($storedOtp && $storedOtp === $otp) {
            Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * SMS ile OTP gönder (CastMart SMS modülü ile)
     */
    public function sendSmsOTP(string $phone, string $type = 'customer'): ?string
    {
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $cacheKey = "2fa_sms_otp:{$type}:{$phone}";
        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        try {
            // CastMart SMS Service kullan
            $smsService = app(\CastMart\SMS\Services\SmsService::class);
            $smsService->send($phone, "Doğrulama kodunuz: {$otp}");
            return $otp;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * SMS OTP doğrula
     */
    public function verifySmsOTP(string $phone, string $otp, string $type = 'customer'): bool
    {
        $cacheKey = "2fa_sms_otp:{$type}:{$phone}";
        $storedOtp = Cache::get($cacheKey);

        if ($storedOtp && $storedOtp === $otp) {
            Cache::forget($cacheKey);
            return true;
        }

        return false;
    }

    /**
     * 2FA session'ını geçerli yap
     */
    public function confirmTwoFactor(string $type, int $userId): void
    {
        session()->put("2fa_confirmed:{$type}:{$userId}", true);
        session()->put("2fa_confirmed_at:{$type}:{$userId}", now());
    }

    /**
     * 2FA session kontrolü
     */
    public function isTwoFactorConfirmed(string $type, int $userId): bool
    {
        $confirmed = session()->get("2fa_confirmed:{$type}:{$userId}", false);
        $confirmedAt = session()->get("2fa_confirmed_at:{$type}:{$userId}");

        if (!$confirmed || !$confirmedAt) {
            return false;
        }

        // 30 dakika geçerlilik
        $timeout = config('auth.two_factor_timeout', 30);
        return $confirmedAt->addMinutes($timeout)->isFuture();
    }

    /**
     * 2FA session'ını temizle
     */
    public function clearTwoFactorSession(string $type, int $userId): void
    {
        session()->forget("2fa_confirmed:{$type}:{$userId}");
        session()->forget("2fa_confirmed_at:{$type}:{$userId}");
    }
}
