<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayTR Configuration
    |--------------------------------------------------------------------------
    */

    'merchant_id' => env('PAYTR_MERCHANT_ID', ''),
    'merchant_key' => env('PAYTR_MERCHANT_KEY', ''),
    'merchant_salt' => env('PAYTR_MERCHANT_SALT', ''),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    | true: Test modu (gerçek ödeme alınmaz)
    | false: Canlı mod (gerçek ödeme alınır)
    */
    'sandbox' => env('PAYTR_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Installment Options
    |--------------------------------------------------------------------------
    */
    'installments' => [
        'enabled' => env('PAYTR_INSTALLMENTS_ENABLED', true),
        'no_installment' => env('PAYTR_NO_INSTALLMENT', false),
        'max_installment' => env('PAYTR_MAX_INSTALLMENT', 12),
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    */
    'title' => 'PayTR ile Öde',
    'description' => 'Kredi kartı ile güvenli ödeme',
    'sort' => 2,
    'active' => env('PAYTR_ACTIVE', true),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    | Ödeme sayfası timeout süresi (dakika)
    */
    'timeout_limit' => 30,
];
