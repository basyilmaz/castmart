<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tables first to fix previous failed migration
        if (Schema::hasColumn('channels', 'tenant_id')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
        
        // Tenants tablosu
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('subdomain')->unique();
            $table->string('database')->nullable(); // Ayrı DB modu için
            $table->string('plan')->default('starter');
            $table->string('status')->default('pending'); // pending, active, suspended, cancelled
            
            // Sahip - admins.id is int unsigned, not bigint
            $table->unsignedInteger('owner_id')->nullable();
            $table->foreign('owner_id')->references('id')->on('admins')->nullOnDelete();
            
            // Ayarlar
            $table->json('settings')->nullable();
            $table->json('metadata')->nullable();
            
            // Tarihler
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // İndeksler
            $table->index('status');
            $table->index('plan');
        });

        // Tenant kullanıcıları
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedInteger('admin_id'); // admins.id is int unsigned
            $table->string('role')->default('staff'); // owner, admin, manager, staff, viewer
            $table->json('permissions')->nullable();
            $table->boolean('is_owner')->default(false);
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
            
            $table->unique(['tenant_id', 'admin_id']);
        });

        // Tenant abonelikleri
        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('plan');
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('TRY');
            $table->string('status')->default('pending'); // pending, active, cancelled, expired
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('cancelled_at')->nullable();
            
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            
            $table->index(['tenant_id', 'status']);
            $table->index('ends_at');
        });

        // Mevcut tablolara tenant_id ekle
        if (!Schema::hasColumn('channels', 'tenant_id')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
                $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        // tenant_id sütununu kaldır
        if (Schema::hasColumn('channels', 'tenant_id')) {
            Schema::table('channels', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('tenant_users');
        Schema::dropIfExists('tenants');
    }
};
