<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop table first to fix previous failed migration
        Schema::dropIfExists('trendyol_commission_rates');
        
        // Komisyon Oranları tablosu
        Schema::create('trendyol_commission_rates', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->string('category_id')->nullable();
            $table->decimal('commission_rate', 5, 2); // %0.00 - %99.99
            $table->decimal('service_fee', 10, 2)->default(4.99);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique('category_name');
            $table->index('is_active');
        });

        // Varsayılan komisyon oranlarını ekle
        $this->seedDefaultRates();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_commission_rates');
    }

    /**
     * Varsayılan komisyon oranlarını ekle
     */
    private function seedDefaultRates(): void
    {
        $rates = [
            // Elektronik
            ['category_name' => 'Cep Telefonu & Aksesuar', 'commission_rate' => 8.00],
            ['category_name' => 'Bilgisayar & Tablet', 'commission_rate' => 10.00],
            ['category_name' => 'TV, Görüntü & Ses Sistemleri', 'commission_rate' => 9.00],
            ['category_name' => 'Beyaz Eşya', 'commission_rate' => 8.00],
            ['category_name' => 'Elektrikli Ev Aletleri', 'commission_rate' => 12.00],
            ['category_name' => 'Foto & Kamera', 'commission_rate' => 10.00],
            
            // Moda - Giyim
            ['category_name' => 'Kadın Giyim', 'commission_rate' => 18.00],
            ['category_name' => 'Erkek Giyim', 'commission_rate' => 18.00],
            ['category_name' => 'Çocuk Giyim', 'commission_rate' => 17.00],
            ['category_name' => 'Bebek Giyim', 'commission_rate' => 16.00],
            
            // Moda - Ayakkabı & Çanta
            ['category_name' => 'Kadın Ayakkabı', 'commission_rate' => 16.00],
            ['category_name' => 'Erkek Ayakkabı', 'commission_rate' => 16.00],
            ['category_name' => 'Çocuk Ayakkabı', 'commission_rate' => 15.00],
            ['category_name' => 'Kadın Çanta', 'commission_rate' => 16.00],
            ['category_name' => 'Erkek Çanta', 'commission_rate' => 15.00],
            
            // Moda - Aksesuar
            ['category_name' => 'Saat', 'commission_rate' => 14.00],
            ['category_name' => 'Takı & Mücevher', 'commission_rate' => 15.00],
            ['category_name' => 'Gözlük', 'commission_rate' => 14.00],
            
            // Ev & Yaşam
            ['category_name' => 'Mobilya', 'commission_rate' => 14.00],
            ['category_name' => 'Ev Tekstili', 'commission_rate' => 15.00],
            ['category_name' => 'Ev Dekorasyon', 'commission_rate' => 15.00],
            ['category_name' => 'Mutfak Gereçleri', 'commission_rate' => 14.00],
            ['category_name' => 'Banyo', 'commission_rate' => 14.00],
            ['category_name' => 'Aydınlatma', 'commission_rate' => 13.00],
            
            // Kozmetik & Kişisel Bakım
            ['category_name' => 'Kozmetik', 'commission_rate' => 17.00],
            ['category_name' => 'Parfüm & Deodorant', 'commission_rate' => 16.00],
            ['category_name' => 'Cilt Bakımı', 'commission_rate' => 17.00],
            ['category_name' => 'Saç Bakımı', 'commission_rate' => 16.00],
            ['category_name' => 'Kişisel Bakım', 'commission_rate' => 15.00],
            
            // Spor & Outdoor
            ['category_name' => 'Spor Giyim', 'commission_rate' => 15.00],
            ['category_name' => 'Spor Ayakkabı', 'commission_rate' => 14.00],
            ['category_name' => 'Outdoor & Kamp', 'commission_rate' => 13.00],
            ['category_name' => 'Fitness & Kondisyon', 'commission_rate' => 13.00],
            
            // Anne & Bebek
            ['category_name' => 'Bebek Bezi & Bakım', 'commission_rate' => 12.00],
            ['category_name' => 'Bebek Beslenmesi', 'commission_rate' => 11.00],
            ['category_name' => 'Bebek Araç & Gereç', 'commission_rate' => 12.00],
            
            // Süpermarket
            ['category_name' => 'Gıda', 'commission_rate' => 10.00],
            ['category_name' => 'İçecek', 'commission_rate' => 10.00],
            ['category_name' => 'Temizlik', 'commission_rate' => 11.00],
            ['category_name' => 'Pet Shop', 'commission_rate' => 12.00],
            
            // Kitap & Hobi
            ['category_name' => 'Kitap', 'commission_rate' => 12.00],
            ['category_name' => 'Kırtasiye', 'commission_rate' => 13.00],
            ['category_name' => 'Oyuncak', 'commission_rate' => 14.00],
            ['category_name' => 'Hobi & Oyun', 'commission_rate' => 14.00],
            
            // Otomotiv
            ['category_name' => 'Oto Aksesuar', 'commission_rate' => 12.00],
            ['category_name' => 'Motosiklet', 'commission_rate' => 11.00],
            ['category_name' => 'Oto Yedek Parça', 'commission_rate' => 10.00],
        ];

        foreach ($rates as $rate) {
            \DB::table('trendyol_commission_rates')->insert([
                'category_name' => $rate['category_name'],
                'commission_rate' => $rate['commission_rate'],
                'service_fee' => 4.99,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
