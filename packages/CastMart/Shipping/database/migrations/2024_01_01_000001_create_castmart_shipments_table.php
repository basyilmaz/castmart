<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop if exists to fix previous failed migration
        Schema::dropIfExists('castmart_shipments');
        
        Schema::create('castmart_shipments', function (Blueprint $table) {
            $table->id();
            
            // İlişki
            $table->unsignedInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            
            // Kargo bilgileri
            $table->string('carrier_code', 50); // aras, mng, yurtici
            $table->string('tracking_number', 100)->index();
            $table->string('cargo_key', 100)->nullable();
            
            // Durum
            $table->string('status', 50)->default('created');
            // created, pending, picked_up, in_transit, out_for_delivery, delivered, returned, cancelled
            
            // Alıcı bilgileri
            $table->string('receiver_name');
            $table->string('receiver_phone', 20);
            $table->text('receiver_address');
            $table->string('receiver_city', 50);
            $table->string('receiver_district', 100)->nullable();
            
            // Paket bilgileri
            $table->unsignedTinyInteger('piece_count')->default(1);
            $table->decimal('weight', 8, 2)->nullable(); // kg
            $table->decimal('desi', 8, 2)->nullable();
            
            // Kapıda ödeme
            $table->boolean('is_cod')->default(false);
            $table->decimal('cod_amount', 12, 2)->nullable();
            
            // Takip
            $table->string('last_location', 255)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Ek veriler
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // İndeksler
            $table->index(['carrier_code', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('castmart_shipments');
    }
};
