<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the old unique constraint on account_id + product_id
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropUnique(['account_id', 'product_id']);
        });

        // Make product_id nullable if not already
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
        });

        // Add new unique constraint on account_id + external_id
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->unique(['account_id', 'external_id']);
        });

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
