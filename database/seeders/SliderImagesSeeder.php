<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SliderImagesSeeder extends Seeder
{
    /**
     * Slider görsellerini güncelle - Railway için statik görseller
     */
    public function run(): void
    {
        // Image Carousel (Slider) customization'ını bul
        $sliderCustomization = DB::table('theme_customizations')
            ->where('type', 'image_carousel')
            ->first();

        if (!$sliderCustomization) {
            $this->command->info('Slider customization bulunamadı, yeni oluşturuluyor...');
            
            // Yeni slider oluştur
            $id = DB::table('theme_customizations')->insertGetId([
                'type' => 'image_carousel',
                'name' => 'Ana Sayfa Slider',
                'sort_order' => 1,
                'status' => 1,
                'channel_id' => 1,
                'theme_code' => 'default',
            ]);
        } else {
            $id = $sliderCustomization->id;
        }

        // Slider verilerini hazırla - statik public görseller
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

        // Customization'ı güncelle
        DB::table('theme_customizations')
            ->where('id', $id)
            ->update([
                'options' => json_encode($sliderOptions),
            ]);

        // Translation'ı güncelle veya oluştur
        $existingTranslation = DB::table('theme_customization_translations')
            ->where('theme_customization_id', $id)
            ->where('locale', 'tr')
            ->first();

        if ($existingTranslation) {
            DB::table('theme_customization_translations')
                ->where('id', $existingTranslation->id)
                ->update([
                    'options' => json_encode($sliderOptions),
                ]);
        } else {
            DB::table('theme_customization_translations')->insert([
                'theme_customization_id' => $id,
                'locale' => 'tr',
                'options' => json_encode($sliderOptions),
            ]);
        }

        $this->command->info('Slider görselleri başarıyla güncellendi!');
    }
}
