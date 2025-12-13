<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Varsayılan Kargo Firması
    |--------------------------------------------------------------------------
    */
    'default_carrier' => env('DEFAULT_CARRIER', 'aras'),

    /*
    |--------------------------------------------------------------------------
    | Aras Kargo API Ayarları
    |--------------------------------------------------------------------------
    */
    'aras' => [
        'enabled' => env('ARAS_KARGO_ENABLED', true),
        'customer_code' => env('ARAS_CUSTOMER_CODE', ''),
        'password' => env('ARAS_PASSWORD', ''),
        'username' => env('ARAS_USERNAME', ''),
        
        // API URL'leri
        'api_url' => [
            'production' => 'https://customerservices.araskargo.com.tr/aaborWSProd/Service1.svc',
            'test' => 'https://customerws.araskargo.com.tr/aaborWS/Service1.svc',
        ],
        
        // Kullanılacak ortam
        'mode' => env('ARAS_MODE', 'test'),
        
        // Varsayılan gönderici bilgileri
        'sender' => [
            'name' => env('ARAS_SENDER_NAME', ''),
            'phone' => env('ARAS_SENDER_PHONE', ''),
            'address' => env('ARAS_SENDER_ADDRESS', ''),
            'city' => env('ARAS_SENDER_CITY', ''),
            'district' => env('ARAS_SENDER_DISTRICT', ''),
        ],
        
        // Ödeme tipi: 1=Gönderici Ödemeli, 2=Alıcı Ödemeli
        'payment_type' => env('ARAS_PAYMENT_TYPE', 1),
        
        // Teslimat tipi: 1=Adrese Teslim, 2=Şubeye Teslim
        'delivery_type' => env('ARAS_DELIVERY_TYPE', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | MNG Kargo API Ayarları
    |--------------------------------------------------------------------------
    */
    'mng' => [
        'enabled' => env('MNG_KARGO_ENABLED', false),
        'api_key' => env('MNG_API_KEY', ''),
        'api_secret' => env('MNG_API_SECRET', ''),
        'customer_number' => env('MNG_CUSTOMER_NUMBER', ''),
        
        'api_url' => [
            'production' => 'https://api.mngkargo.com.tr/mngapi/api',
            'test' => 'https://testapi.mngkargo.com.tr/mngapi/api',
        ],
        
        'mode' => env('MNG_MODE', 'test'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Yurtiçi Kargo API Ayarları
    |--------------------------------------------------------------------------
    */
    'yurtici' => [
        'enabled' => env('YURTICI_KARGO_ENABLED', false),
        'username' => env('YURTICI_USERNAME', ''),
        'password' => env('YURTICI_PASSWORD', ''),
        'customer_code' => env('YURTICI_CUSTOMER_CODE', ''),
        
        'api_url' => [
            'production' => 'https://ws.yurticikargo.com/ShippingOrdersDispatherServices/services/ShippingOrdersServiceV2',
            'test' => 'https://testws.yurticikargo.com/ShippingOrdersDispatherServices/services/ShippingOrdersServiceV2',
        ],
        
        'mode' => env('YURTICI_MODE', 'test'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Desi/Ağırlık Hesaplama
    |--------------------------------------------------------------------------
    */
    'desi_coefficient' => 3000, // Hacimsel ağırlık katsayısı

    /*
    |--------------------------------------------------------------------------
    | Etiket Ayarları
    |--------------------------------------------------------------------------
    */
    'label' => [
        'format' => 'PDF', // PDF, ZPL
        'size' => '10x15', // cm
    ],
];
