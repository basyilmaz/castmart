<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Optimization Configuration
    |--------------------------------------------------------------------------
    */

    // JPEG/PNG kalitesi (0-100)
    'quality' => env('IMAGE_QUALITY', 85),

    // WebP kalitesi (0-100)
    'webp_quality' => env('IMAGE_WEBP_QUALITY', 80),

    // Maksimum boyutlar
    'max_width' => env('IMAGE_MAX_WIDTH', 1920),
    'max_height' => env('IMAGE_MAX_HEIGHT', 1920),

    // WebP versiyonu oluştur
    'create_webp' => env('IMAGE_CREATE_WEBP', true),

    // Thumbnail oluştur
    'create_thumbnails' => env('IMAGE_CREATE_THUMBNAILS', true),

    // Thumbnail boyutları
    'thumbnail_sizes' => [
        'small' => [
            'width' => 150,
            'height' => 150,
        ],
        'medium' => [
            'width' => 300,
            'height' => 300,
        ],
        'large' => [
            'width' => 600,
            'height' => 600,
        ],
        'product' => [
            'width' => 800,
            'height' => 800,
        ],
    ],

    // Lazy loading placeholder genişliği
    'placeholder_width' => 20,

    // Otomatik optimizasyon yapılacak disk'ler
    'auto_optimize_disks' => ['public'],

    // Optimizasyon yapılmayacak klasörler
    'exclude_directories' => [
        'theme',
        'admin',
        'vendor',
    ],
];
