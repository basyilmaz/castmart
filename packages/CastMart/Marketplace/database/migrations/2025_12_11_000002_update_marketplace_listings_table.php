<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table doesn't exist (first migration handles it)
        if (!Schema::hasTable('marketplace_listings')) {
            return;
        }
        
        // Drop the old unique constraint on account_id + product_id if exists
        try {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->dropUnique(['account_id', 'product_id']);
            });
        } catch (\Exception $e) {
            // Constraint might not exist
        }

        // Make product_id nullable if not already - using unsignedInteger to match products.id
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable()->change();
        });

        // Add new unique constraint on account_id + external_id
        try {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->unique(['account_id', 'external_id']);
            });
        } catch (\Exception $e) {
            // Constraint might already exist
        }

        // Add missing columns if they don't exist
        if (!Schema::hasColumn('marketplace_listings', 'external_sku')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->string('external_sku', 100)->nullable()->after('external_id');
            });
        }

        if (!Schema::hasColumn('marketplace_listings', 'price')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->decimal('price', 12, 2)->default(0)->after('status');
            });
        }

        if (!Schema::hasColumn('marketplace_listings', 'stock')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->integer('stock')->default(0)->after('price');
            });
        }

        if (!Schema::hasColumn('marketplace_listings', 'listing_data')) {
            Schema::table('marketplace_listings', function (Blueprint $table) {
                $table->json('listing_data')->nullable()->after('extra_data');
            });
        }
    }

    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropUnique(['account_id', 'external_id']);
            $table->unique(['account_id', 'product_id']);
        });
    }
};
