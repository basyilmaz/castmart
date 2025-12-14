<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Configuration
    |--------------------------------------------------------------------------
    */

    // 2FA etkin mi?
    'enabled' => env('TWO_FACTOR_ENABLED', true),

    // Session timeout (dakika) - Bu süre sonunda tekrar 2FA gerekir
    'timeout' => env('TWO_FACTOR_TIMEOUT', 30),

    // Zorunlu 2FA rolleri (örn: admin'ler için zorunlu)
    'required_roles' => [
        'admin' => env('TWO_FACTOR_REQUIRED_ADMIN', false),
        'customer' => env('TWO_FACTOR_REQUIRED_CUSTOMER', false),
    ],

    // Recovery kod sayısı
    'recovery_codes_count' => 8,

    // OTP geçerlilik süresi (saniye)
    'otp_validity' => 30,

    // Email OTP geçerlilik süresi (dakika)
    'email_otp_validity' => 5,

    // SMS OTP geçerlilik süresi (dakika)
    'sms_otp_validity' => 5,

    // Desteklenen yöntemler
    'methods' => [
        'authenticator' => true,  // Google Authenticator, Authy vb.
        'email' => true,          // E-posta ile OTP
        'sms' => true,            // SMS ile OTP (SMS modülü gerekli)
    ],

    // QR Code ayarları
    'qr_code' => [
        'size' => 200,
        'format' => 'svg',
    ],

    // Rate limiting (brute force koruması)
    'rate_limiting' => [
        'max_attempts' => 5,
        'lockout_minutes' => 15,
    ],
];
