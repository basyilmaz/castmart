<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pazarlama Modülü Ayarları
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Sadakat Sistemi
    |--------------------------------------------------------------------------
    */
    'loyalty' => [
        'enabled' => env('LOYALTY_ENABLED', true),
        
        // Puan kazanma oranları
        'points_per_currency' => 1, // Her 1 TL için 1 puan
        'min_order_for_points' => 50, // Minimum sipariş tutarı
        
        // Puan harcama
        'points_to_currency_rate' => 100, // 100 puan = 1 TL
        'min_points_to_redeem' => 100, // Minimum harcama puanı
        'max_discount_percentage' => 30, // Maksimum indirim yüzdesi
        
        // Seviyeler
        'tiers' => [
            'bronze' => [
                'name' => 'Bronz',
                'min_points' => 0,
                'multiplier' => 1.0,
                'benefits' => ['Ücretsiz kargo (500₺+)', 'Doğum günü indirimi'],
            ],
            'silver' => [
                'name' => 'Gümüş',
                'min_points' => 1000,
                'multiplier' => 1.25,
                'benefits' => ['Ücretsiz kargo (250₺+)', 'Özel indirimler', 'Erken erişim'],
            ],
            'gold' => [
                'name' => 'Altın',
                'min_points' => 5000,
                'multiplier' => 1.5,
                'benefits' => ['Ücretsiz kargo', 'VIP destek', 'Özel ürünler', 'Kişisel temsilci'],
            ],
            'platinum' => [
                'name' => 'Platin',
                'min_points' => 15000,
                'multiplier' => 2.0,
                'benefits' => ['Tüm altın avantajları', 'Özel etkinlikler', 'Hediye paketleme'],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kupon Sistemi
    |--------------------------------------------------------------------------
    */
    'coupons' => [
        'enabled' => env('COUPONS_ENABLED', true),
        
        // Kupon tipleri
        'types' => [
            'percentage' => 'Yüzdelik İndirim',
            'fixed' => 'Sabit Tutar İndirim',
            'free_shipping' => 'Ücretsiz Kargo',
            'buy_x_get_y' => 'X Al Y Öde',
        ],
        
        // Varsayılan ayarlar
        'default_usage_limit' => 100,
        'default_per_customer_limit' => 1,
        'code_length' => 8,
        'code_prefix' => 'CM',
    ],

    /*
    |--------------------------------------------------------------------------
    | Referral (Arkadaş Davet) Sistemi
    |--------------------------------------------------------------------------
    */
    'referral' => [
        'enabled' => env('REFERRAL_ENABLED', true),
        
        // Ödüller
        'referrer_reward' => 50, // TL
        'referee_reward' => 25, // TL (yeni üye)
        
        // Minimum sipariş tutarı (ödül için)
        'min_order_amount' => 100,
        
        // Maksimum referral
        'max_referrals_per_user' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Pazarlama
    |--------------------------------------------------------------------------
    */
    'email' => [
        'enabled' => env('EMAIL_MARKETING_ENABLED', true),
        
        // Otomatik emailler
        'automated' => [
            'welcome' => true,
            'abandoned_cart' => true,
            'order_followup' => true,
            'birthday' => true,
            'win_back' => true, // İnaktif müşteri
        ],
        
        // Abandoned cart süresi (saat)
        'abandoned_cart_delay' => 2,
        
        // Win-back süresi (gün)
        'win_back_days' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Bildirimleri
    |--------------------------------------------------------------------------
    */
    'push' => [
        'enabled' => env('PUSH_NOTIFICATIONS_ENABLED', false),
        'vapid_public_key' => env('VAPID_PUBLIC_KEY'),
        'vapid_private_key' => env('VAPID_PRIVATE_KEY'),
    ],
];
