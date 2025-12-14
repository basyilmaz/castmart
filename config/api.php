<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */

    // API versiyonu
    'version' => 'v1',

    // Rate limiting (dakikada istek sayısı)
    'rate_limit' => env('API_RATE_LIMIT', 60),

    // API anahtarları (production'da veritabanı kullanın)
    'keys' => array_filter([
        env('API_KEY_PRIMARY'),
        env('API_KEY_SECONDARY'),
    ]),

    // Webhook imza secret'ı
    'webhook_secret' => env('API_WEBHOOK_SECRET'),

    // Public endpoint'ler (API key gerektirmez)
    'public_endpoints' => [
        'api/health',
        'api/status',
        'api/v1/products',
        'api/v1/categories',
    ],

    // IP whitelist (boş = tüm IP'lere izin)
    'ip_whitelist' => array_filter(explode(',', env('API_IP_WHITELIST', ''))),

    /*
    |--------------------------------------------------------------------------
    | JWT Configuration
    |--------------------------------------------------------------------------
    */
    'jwt' => [
        'secret' => env('JWT_SECRET'),
        'ttl' => env('JWT_TTL', 60), // Token süresi (dakika)
        'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // Refresh süresi (dakika)
        'algo' => 'HS256',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Response Settings
    |--------------------------------------------------------------------------
    */
    'response' => [
        'default_per_page' => 20,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Settings
    |--------------------------------------------------------------------------
    */
    'cors' => [
        'allowed_origins' => array_filter(explode(',', env('API_CORS_ORIGINS', '*'))),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-API-Key', 'X-Requested-With'],
        'max_age' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Throttle Settings by Endpoint Type
    |--------------------------------------------------------------------------
    */
    'throttle' => [
        'default' => [
            'limit' => 60,
            'decay' => 1, // dakika
        ],
        'auth' => [
            'limit' => 10,
            'decay' => 1,
        ],
        'search' => [
            'limit' => 30,
            'decay' => 1,
        ],
        'upload' => [
            'limit' => 10,
            'decay' => 1,
        ],
    ],
];
