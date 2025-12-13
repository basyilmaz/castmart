<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kuponlar
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 32)->default('percentage'); // percentage, fixed, free_shipping, buy_x_get_y
            $table->decimal('value', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->nullable();
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_per_customer')->default(1);
            $table->integer('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->json('applicable_products')->nullable();
            $table->json('applicable_categories')->nullable();
            $table->json('excluded_products')->nullable();
            $table->json('applicable_customer_groups')->nullable();
            $table->boolean('first_order_only')->default(false);
            $table->boolean('free_shipping')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'starts_at', 'expires_at']);
        });

        // Kupon kullanımları
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            
            $table->index(['coupon_id', 'customer_id']);
        });

        // Sadakat hesapları
        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->integer('total_points')->default(0);
            $table->integer('available_points')->default(0);
            $table->integer('lifetime_points')->default(0);
            $table->string('tier', 32)->default('bronze');
            $table->string('referral_code', 16)->unique();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('referred_by')->references('id')->on('loyalty_accounts')->nullOnDelete();
            
            $table->index('tier');
        });

        // Sadakat işlemleri
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_account_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32); // earn, redeem, bonus, referral, adjustment, expire
            $table->integer('points');
            $table->integer('balance_after');
            $table->string('description');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['loyalty_account_id', 'type']);
        });

        // Referral ödülleri
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id'); // Davet eden
            $table->unsignedBigInteger('referee_id'); // Davet edilen
            $table->unsignedBigInteger('order_id')->nullable();
            $table->decimal('referrer_reward', 10, 2)->default(0);
            $table->decimal('referee_reward', 10, 2)->default(0);
            $table->string('status', 32)->default('pending'); // pending, approved, paid, cancelled
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->foreign('referrer_id')->references('id')->on('loyalty_accounts')->cascadeOnDelete();
            $table->foreign('referee_id')->references('id')->on('loyalty_accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
        Schema::dropIfExists('loyalty_transactions');
        Schema::dropIfExists('loyalty_accounts');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
