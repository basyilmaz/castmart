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
        // Intelligence Alerts tablosu
        Schema::create('trendyol_intelligence_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_account_id')->constrained()->onDelete('cascade');
            $table->string('type'); // critical, warning, opportunity, trend
            $table->string('category'); // buybox, stock, price, review, order
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('product_sku')->nullable();
            $table->json('data')->nullable(); // ek veriler
            $table->string('action_type')->nullable(); // update_price, update_stock, reply_review
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['marketplace_account_id', 'is_read'], 'ty_alerts_account_read_idx');
            $table->index(['type', 'created_at'], 'ty_alerts_type_created_idx');
        });

        // Price Rules tablosu
        Schema::create('trendyol_price_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_account_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('trigger'); // competitor_cheaper, buybox_lost, stock_low, competitor_stock_zero
            $table->string('action'); // match_minus, decrease_percent, increase_percent, set_price
            $table->decimal('action_value', 10, 2)->nullable(); // yüzde veya sabit değer
            $table->string('scope'); // all, category, selected
            $table->json('scope_data')->nullable(); // kategori veya ürün listesi
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->integer('trigger_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            
            $table->index(['marketplace_account_id', 'is_active'], 'ty_rules_account_active_idx');
        });

        // Price History tablosu
        Schema::create('trendyol_price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_account_id')->constrained()->onDelete('cascade');
            $table->string('product_sku');
            $table->decimal('old_price', 10, 2);
            $table->decimal('new_price', 10, 2);
            $table->decimal('competitor_price', 10, 2)->nullable();
            $table->string('change_reason'); // manual, auto_rule, campaign
            $table->foreignId('rule_id')->nullable();
            $table->boolean('buybox_won')->nullable();
            $table->timestamps();
            
            $table->index(['product_sku', 'created_at'], 'ty_price_history_sku_created_idx');
        });

        // BuyBox Tracking tablosu
        Schema::create('trendyol_buybox_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_account_id')->constrained()->onDelete('cascade');
            $table->string('product_sku');
            $table->string('barcode')->nullable();
            $table->decimal('our_price', 10, 2);
            $table->decimal('competitor_price', 10, 2)->nullable();
            $table->string('competitor_seller')->nullable();
            $table->string('status'); // won, lost, risk
            $table->integer('win_chance')->default(0); // 0-100
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
            
            $table->unique(['marketplace_account_id', 'product_sku'], 'ty_buybox_account_sku_unique');
            $table->index('status', 'ty_buybox_status_idx');
        });

        // Notification Settings tablosu
        Schema::create('trendyol_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_account_id')->constrained()->onDelete('cascade');
            $table->boolean('buybox_alerts')->default(true);
            $table->boolean('stock_alerts')->default(true);
            $table->boolean('price_alerts')->default(true);
            $table->boolean('review_alerts')->default(true);
            $table->boolean('order_alerts')->default(true);
            $table->boolean('email_notifications')->default(false);
            $table->string('email_address')->nullable();
            $table->integer('stock_threshold')->default(5); // kritik stok seviyesi
            $table->integer('check_interval')->default(30); // dakika
            $table->timestamps();
            
            $table->unique('marketplace_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trendyol_notification_settings');
        Schema::dropIfExists('trendyol_buybox_tracking');
        Schema::dropIfExists('trendyol_price_history');
        Schema::dropIfExists('trendyol_price_rules');
        Schema::dropIfExists('trendyol_intelligence_alerts');
    }
};
