<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tables first to fix previous failed migration
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('price_alerts');
        Schema::dropIfExists('push_subscriptions');
        
        // Push bildirimleri için subscription kayıtları
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id'); // customers.id is int unsigned
            $table->text('subscription'); // JSON subscription object
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->index('customer_id');
        });

        // Fiyat düşüşü bildirimi takibi
        Schema::create('price_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id'); // customers.id is int unsigned
            $table->unsignedInteger('product_id'); // products.id is int unsigned
            $table->decimal('target_price', 12, 4)->nullable();
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->unique(['customer_id', 'product_id']);
        });

        // Stok bildirimi takibi
        Schema::create('stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id'); // customers.id is int unsigned
            $table->unsignedInteger('product_id'); // products.id is int unsigned
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->unique(['customer_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_alerts');
        Schema::dropIfExists('price_alerts');
        Schema::dropIfExists('push_subscriptions');
    }
};
