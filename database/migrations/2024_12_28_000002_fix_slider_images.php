<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Update slider images in theme_customization_translations
     */
    public function up(): void
    {
        // Slider verilerini hazirla - statik public gorseller
        $sliderOptions = [
            'images' => [
                [
                    'image' => '/images/sliders/slider1.png',
                    'title' => 'CastMarta Hos Geldiniz',
                    'link' => '/',
                ],
                [
                    'image' => '/images/sliders/slider2.png',
                    'title' => 'Yuzde 40a Varan Indirimler',
                    'link' => '/categories',
                ],
                [
                    'image' => '/images/sliders/slider3.png',
                    'title' => 'Yeni Urunler',
                    'link' => '/new-products',
                ],
                [
                    'image' => '/images/sliders/slider4.png',
                    'title' => 'Ucretsiz Kargo',
                    'link' => '/free-shipping',
                ],
            ],
        ];

        // image_carousel tipindeki tum slider customization IDlerini bul
        $sliderIds = DB::table('theme_customizations')
            ->where('type', 'image_carousel')
            ->pluck('id');

        // Her slider icin translation tablosunu guncelle
        foreach ($sliderIds as $sliderId) {
            DB::table('theme_customization_translations')
                ->where('theme_customization_id', $sliderId)
                ->update([
                    'options' => json_encode($sliderOptions),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bu migration geri alinamaz
    }
};
