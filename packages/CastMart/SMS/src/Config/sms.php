<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Varsayılan SMS Sağlayıcısı
    |--------------------------------------------------------------------------
    */
    'default' => env('SMS_DRIVER', 'netgsm'),

    /*
    |--------------------------------------------------------------------------
    | SMS Gönderim Ayarları
    |--------------------------------------------------------------------------
    */
    'from' => env('SMS_FROM', 'CASTMART'),
    
    /*
    |--------------------------------------------------------------------------
    | Netgsm Ayarları
    |--------------------------------------------------------------------------
    */
    'netgsm' => [
        'enabled' => env('NETGSM_ENABLED', true),
        'usercode' => env('NETGSM_USERCODE', ''),
        'password' => env('NETGSM_PASSWORD', ''),
        'msgheader' => env('NETGSM_HEADER', 'CASTMART'),
        
        // API URL'leri
        'api_url' => [
            'send' => 'https://api.netgsm.com.tr/sms/send/get/',
            'send_xml' => 'https://api.netgsm.com.tr/sms/send/xml',
            'otp' => 'https://api.netgsm.com.tr/sms/send/otp',
            'report' => 'https://api.netgsm.com.tr/sms/report/',
            'balance' => 'https://api.netgsm.com.tr/balance/list/get/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | İletimerkezi Ayarları
    |--------------------------------------------------------------------------
    */
    'iletimerkezi' => [
        'enabled' => env('ILETIMERKEZI_ENABLED', false),
        'api_key' => env('ILETIMERKEZI_API_KEY', ''),
        'api_hash' => env('ILETIMERKEZI_API_HASH', ''),
        'sender' => env('ILETIMERKEZI_SENDER', 'CASTMART'),
        
        'api_url' => 'https://api.iletimerkezi.com/v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | JetSms Ayarları (Mutlucell)
    |--------------------------------------------------------------------------
    */
    'jetsms' => [
        'enabled' => env('JETSMS_ENABLED', false),
        'username' => env('JETSMS_USERNAME', ''),
        'password' => env('JETSMS_PASSWORD', ''),
        'originator' => env('JETSMS_ORIGINATOR', 'CASTMART'),
        
        'api_url' => 'https://www.oztekbayi.com/api',
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Bildirimleri
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        // Sipariş bildirimleri
        'order_created' => env('SMS_ORDER_CREATED', true),
        'order_shipped' => env('SMS_ORDER_SHIPPED', true),
        'order_delivered' => env('SMS_ORDER_DELIVERED', true),
        'order_cancelled' => env('SMS_ORDER_CANCELLED', true),
        
        // OTP / Doğrulama
        'otp_enabled' => env('SMS_OTP_ENABLED', true),
        'otp_expiry_minutes' => 5,
        
        // Pazarlama
        'marketing_enabled' => env('SMS_MARKETING_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Şablonları
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'order_created' => 'Sayın {customer_name}, #{order_id} numaralı siparişiniz alınmıştır. CastMart',
        'order_shipped' => 'Sayın {customer_name}, siparişiniz kargoya verilmiştir. Takip No: {tracking_number}. CastMart',
        'order_delivered' => 'Sayın {customer_name}, siparişiniz teslim edilmiştir. İyi günler dileriz. CastMart',
        'order_cancelled' => 'Sayın {customer_name}, #{order_id} numaralı siparişiniz iptal edilmiştir. CastMart',
        'otp' => 'CastMart doğrulama kodunuz: {code}. Bu kodu kimseyle paylaşmayın.',
        'welcome' => 'CastMart ailesine hoş geldiniz {customer_name}! İyi alışverişler.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Günlük Limit
    |--------------------------------------------------------------------------
    */
    'daily_limit' => env('SMS_DAILY_LIMIT', 1000),
    
    /*
    |--------------------------------------------------------------------------
    | Debug Modu (SMS gönderilmez, sadece loglanır)
    |--------------------------------------------------------------------------
    */
    'debug' => env('SMS_DEBUG', false),
];
