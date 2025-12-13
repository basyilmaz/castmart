<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('marketplace', 50)->index(); // trendyol, hepsiburada, n11
            $table->string('name');
            $table->text('credentials'); // encrypted JSON
            $table->boolean('is_active')->default(true);
            $table->json('sync_settings')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'marketplace']);
        });

        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('marketplace_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('external_id', 100)->nullable()->index();
            $table->string('external_url', 500)->nullable();
            $table->enum('status', ['pending', 'active', 'rejected', 'passive'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'product_id']);
            $table->index(['account_id', 'status']);
        });

        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('marketplace_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('external_order_id', 100)->index();
            $table->string('external_order_number', 100)->nullable();
            $table->string('package_id', 100)->nullable();
            $table->string('status', 50)->default('new')->index();
            $table->string('cargo_provider', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->json('order_data')->nullable();
            $table->json('customer_data')->nullable();
            $table->json('items_data')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'external_order_id']);
        });

        Schema::create('customer_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('marketplace_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('external_question_id', 100)->index();
            $table->string('external_product_id', 100)->nullable();
            $table->text('question_text');
            $table->text('answer_text')->nullable();
            $table->enum('status', ['pending', 'answered'])->default('pending')->index();
            $table->timestamp('asked_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['account_id', 'external_question_id']);
        });

        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('marketplace_accounts')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('external_product_id', 100)->index();
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->string('reviewer_name', 255)->nullable();
            $table->boolean('has_purchase')->default(false);
            $table->date('review_date')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'external_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
        Schema::dropIfExists('customer_questions');
        Schema::dropIfExists('marketplace_orders');
        Schema::dropIfExists('marketplace_listings');
        Schema::dropIfExists('marketplace_accounts');
    }
};
