<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mevcut slider (image_carousel) customization'larını bul ve güncelle
        $sliders = DB::table('theme_customizations')
            ->where('type', 'image_carousel')
            ->get();

        foreach ($sliders as $slider) {
            // Yeni slider verilerini hazırla
            $sliderOptions = [
                'images' => [
                    [
                        'image' => '/images/sliders/slider1.png',
                        'title' => 'CastMart\'a Hoş Geldiniz',
                        'link' => '/',
                    ],
                    [
                        'image' => '/images/sliders/slider2.png',
                        'title' => '%40\'a Varan İndirimler',
                        'link' => '/categories',
                    ],
                    [
                        'image' => '/images/sliders/slider3.png',
                        'title' => 'Yeni Ürünler',
                        'link' => '/new-products',
                    ],
                    [
                        'image' => '/images/sliders/slider4.png',
                        'title' => 'Ücretsiz Kargo',
                        'link' => '/free-shipping',
                    ],
                ],
            ];

            // Ana customization'ı güncelle
            DB::table('theme_customizations')
                ->where('id', $slider->id)
                ->update([
                    'options' => json_encode($sliderOptions),
                ]);

            // Translation'ları da güncelle
            DB::table('theme_customization_translations')
                ->where('theme_customization_id', $slider->id)
                ->update([
                    'options' => json_encode($sliderOptions),
                ]);
        }

        // Eğer hiç slider yoksa yenisini oluştur
        if ($sliders->isEmpty()) {
            $id = DB::table('theme_customizations')->insertGetId([
                'type' => 'image_carousel',
                'name' => 'Ana Sayfa Slider',
                'sort_order' => 1,
                'status' => 1,
                'channel_id' => 1,
                'theme_code' => 'default',
                'options' => json_encode([
                    'images' => [
                        [
                            'image' => '/images/sliders/slider1.png',
                            'title' => 'CastMart\'a Hoş Geldiniz',
                            'link' => '/',
                        ],
                        [
                            'image' => '/images/sliders/slider2.png',
                            'title' => '%40\'a Varan İndirimler',
                            'link' => '/categories',
                        ],
                        [
                            'image' => '/images/sliders/slider3.png',
                            'title' => 'Yeni Ürünler',
                            'link' => '/new-products',
                        ],
                        [
                            'image' => '/images/sliders/slider4.png',
                            'title' => 'Ücretsiz Kargo',
                            'link' => '/free-shipping',
                        ],
                    ],
                ]),
            ]);

            DB::table('theme_customization_translations')->insert([
                'theme_customization_id' => $id,
                'locale' => 'tr',
                'options' => json_encode([
                    'images' => [
                        [
                            'image' => '/images/sliders/slider1.png',
                            'title' => 'CastMart\'a Hoş Geldiniz',
                            'link' => '/',
                        ],
                    ],
                ]),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alınamaz
    }
};
