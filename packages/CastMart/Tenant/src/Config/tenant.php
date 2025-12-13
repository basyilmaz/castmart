<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Multi-Tenant Ayarları
    |--------------------------------------------------------------------------
    */
    
    // Multi-tenant modu aktif mi?
    'enabled' => env('MULTI_TENANT_ENABLED', false),

    // Tenant belirleme yöntemi
    // 'subdomain': tenant1.castmart.com
    // 'domain': tenant1.com
    // 'path': castmart.com/tenant1
    // 'header': X-Tenant-ID header
    'identification' => env('TENANT_IDENTIFICATION', 'subdomain'),

    // Ana domain (subdomain modu için)
    'central_domain' => env('TENANT_CENTRAL_DOMAIN', 'castmart.com'),

    // Subdomain'ler için varsayılan önek
    'subdomain_suffix' => env('TENANT_SUBDOMAIN_SUFFIX', '.castmart.com'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Veritabanı Ayarları
    |--------------------------------------------------------------------------
    */
    
    // Veritabanı izolasyonu
    // 'single': Tek veritabanı, tenant_id ile filtreleme
    // 'database': Her tenant için ayrı veritabanı
    // 'schema': Her tenant için ayrı schema (PostgreSQL)
    'database_mode' => env('TENANT_DATABASE_MODE', 'single'),

    // Tenant veritabanı prefix'i (database modu için)
    'database_prefix' => 'tenant_',

    /*
    |--------------------------------------------------------------------------
    | Tenant İzolasyonu
    |--------------------------------------------------------------------------
    */
    
    // Otomatik tenant filtreleme yapılacak modeller
    'tenant_models' => [
        \Webkul\Product\Models\Product::class,
        \Webkul\Category\Models\Category::class,
        \Webkul\Sales\Models\Order::class,
        \Webkul\Customer\Models\Customer::class,
        \Webkul\Inventory\Models\InventorySource::class,
        \CastMart\Marketplace\Models\MarketplaceAccount::class,
        \CastMart\Shipping\Models\Shipment::class,
    ],

    // Tenant'a ait olmayan (global) modeller
    'global_models' => [
        \Webkul\User\Models\Admin::class,
        \Webkul\Core\Models\Currency::class,
        \Webkul\Core\Models\Locale::class,
        \Webkul\Core\Models\Country::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Planları
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'price_monthly' => 299,
            'price_yearly' => 2990,
            'features' => [
                'max_products' => 100,
                'max_orders_per_month' => 500,
                'max_users' => 2,
                'marketplaces' => ['trendyol'],
                'sms_credits' => 100,
                'support' => 'email',
            ],
        ],
        'professional' => [
            'name' => 'Profesyonel',
            'price_monthly' => 599,
            'price_yearly' => 5990,
            'features' => [
                'max_products' => 1000,
                'max_orders_per_month' => 2000,
                'max_users' => 5,
                'marketplaces' => ['trendyol', 'hepsiburada', 'n11'],
                'sms_credits' => 500,
                'support' => 'priority',
            ],
        ],
        'enterprise' => [
            'name' => 'Kurumsal',
            'price_monthly' => 1499,
            'price_yearly' => 14990,
            'features' => [
                'max_products' => -1, // Limitsiz
                'max_orders_per_month' => -1,
                'max_users' => -1,
                'marketplaces' => ['trendyol', 'hepsiburada', 'n11', 'amazon'],
                'sms_credits' => 2000,
                'support' => 'dedicated',
                'custom_domain' => true,
                'api_access' => true,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Varsayılan Tenant Ayarları
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'plan' => 'starter',
        'status' => 'active',
        'trial_days' => 14,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Ayarları
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'prefix' => 'tenant_',
        'ttl' => 3600, // 1 saat
    ],
];
