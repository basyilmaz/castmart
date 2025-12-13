<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Configuration
    |--------------------------------------------------------------------------
    */

    'sync_intervals' => [
        'orders' => 5,      // dakika
        'questions' => 15,  // dakika
        'reviews' => 1440,  // dakika (günde 1 kez)
    ],

    'scraping' => [
        'driver' => 'selenium', // selenium veya puppeteer
        'timeout' => 30,
        'rate_limit_delay' => 3, // saniye
    ],

    'enabled_marketplaces' => [
        'trendyol',
        // 'hepsiburada',
        // 'n11',
    ],
];
