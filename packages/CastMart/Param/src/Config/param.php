<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Param Payment Configuration
    |--------------------------------------------------------------------------
    */

    // Test modu
    'test_mode' => env('PARAM_TEST_MODE', true),

    // API Kimlik Bilgileri
    'client_code' => env('PARAM_CLIENT_CODE'),
    'client_username' => env('PARAM_CLIENT_USERNAME'),
    'client_password' => env('PARAM_CLIENT_PASSWORD'),
    'guid' => env('PARAM_GUID'),

    // Taksit ayarları
    'installments' => [
        'enabled' => env('PARAM_INSTALLMENTS_ENABLED', true),
        'max_installment' => env('PARAM_MAX_INSTALLMENT', 12),
        'min_amount' => env('PARAM_MIN_INSTALLMENT_AMOUNT', 100), // TL
    ],

    // 3D Secure
    '3d_secure' => [
        'enabled' => env('PARAM_3D_ENABLED', true),
        'force' => env('PARAM_3D_FORCE', true), // Her zaman 3D kullan
    ],

    // Desteklenen kartlar
    'supported_cards' => [
        'visa',
        'mastercard',
        'amex',
        'troy',
    ],
];
