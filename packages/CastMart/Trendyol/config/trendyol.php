<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trendyol API Configuration
    |--------------------------------------------------------------------------
    */

    'api' => [
        'base_url' => env('TRENDYOL_API_URL', 'https://api.trendyol.com/sapigw'),
        'timeout' => env('TRENDYOL_API_TIMEOUT', 30),
    ],

    'scraping' => [
        'enabled' => env('TRENDYOL_SCRAPING_ENABLED', true),
        'rate_limit_delay' => env('TRENDYOL_RATE_LIMIT', 3), // saniye
        'reviews_per_page' => 20,
        'max_review_pages' => 50,
    ],

    'sync' => [
        'orders_interval' => 5,      // dakika
        'questions_interval' => 15,  // dakika
        'reviews_interval' => 1440,  // dakika (günlük)
    ],

    'cargo_providers' => [
        1 => 'Aras Kargo',
        2 => 'MNG Kargo', 
        3 => 'Yurtiçi Kargo',
        4 => 'PTT Kargo',
        5 => 'Sürat Kargo',
        6 => 'Hepsijet',
        7 => 'Sendeo',
        8 => 'DHL',
        9 => 'UPS',
        10 => 'Trendyol Express',
    ],
];
