<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    */

    // CDN aktif mi?
    'enabled' => env('CDN_ENABLED', false),

    // Varsayılan CDN driver
    'driver' => env('CDN_DRIVER', 'local'),

    // Asset'ler için CDN URL (CSS, JS vb.)
    'asset_url' => env('CDN_ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | CDN Drivers
    |--------------------------------------------------------------------------
    */
    'drivers' => [
        
        // Yerel Storage (CDN yok)
        'local' => [
            'disk' => 'public',
        ],

        // Cloudflare R2 + CDN
        'cloudflare' => [
            'disk' => 'r2',
            'url' => env('CLOUDFLARE_CDN_URL'),
            'zone_id' => env('CLOUDFLARE_ZONE_ID'),
            'api_token' => env('CLOUDFLARE_API_TOKEN'),
            // Image Resizing aktif mi?
            'image_resizing' => env('CLOUDFLARE_IMAGE_RESIZING', false),
        ],

        // Bunny CDN
        'bunny' => [
            'disk' => 'bunny',
            'storage_zone' => env('BUNNY_STORAGE_ZONE'),
            'api_key' => env('BUNNY_API_KEY'),
            'pull_zone_url' => env('BUNNY_PULL_ZONE_URL'),
            'pull_zone_id' => env('BUNNY_PULL_ZONE_ID'),
            // Bunny Optimizer aktif mi?
            'optimizer' => env('BUNNY_OPTIMIZER', false),
        ],

        // AWS S3 + CloudFront
        's3' => [
            'disk' => 's3',
            'cloudfront_url' => env('AWS_CLOUDFRONT_URL'),
            // CloudFront distribution ID (cache invalidation için)
            'distribution_id' => env('AWS_CLOUDFRONT_DISTRIBUTION_ID'),
        ],

        // DigitalOcean Spaces + CDN
        'spaces' => [
            'disk' => 'spaces',
            'cdn_endpoint' => env('DO_SPACES_CDN_ENDPOINT'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    */
    'images' => [
        // Varsayılan kalite
        'quality' => 85,
        
        // Varsayılan format (auto = webp destekliyorsa webp)
        'format' => 'auto',
        
        // Preset boyutlar
        'presets' => [
            'thumbnail' => ['width' => 150, 'height' => 150, 'fit' => 'cover'],
            'small' => ['width' => 300, 'height' => 300, 'fit' => 'contain'],
            'medium' => ['width' => 600, 'height' => 600, 'fit' => 'contain'],
            'large' => ['width' => 1200, 'height' => 1200, 'fit' => 'contain'],
            'product' => ['width' => 800, 'height' => 800, 'fit' => 'contain'],
            'banner' => ['width' => 1920, 'height' => 600, 'fit' => 'cover'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        // Browser cache süresi (saniye)
        'max_age' => 31536000, // 1 yıl
        
        // CDN cache süresi (saniye)
        's_maxage' => 31536000,
        
        // Stale-while-revalidate
        'stale_while_revalidate' => 86400, // 1 gün
    ],

    /*
    |--------------------------------------------------------------------------
    | File Types
    |--------------------------------------------------------------------------
    */
    'allowed_types' => [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'media' => ['mp4', 'webm', 'mp3'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB
        'max_image_size' => 5 * 1024 * 1024,  // 5MB
    ],
];
