<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trendyol_price_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('trendyol_price_rules', 'min_price')) {
                $table->decimal('min_price', 10, 2)->nullable()->after('scope_data');
            }
            if (!Schema::hasColumn('trendyol_price_rules', 'max_price')) {
                $table->decimal('max_price', 10, 2)->nullable()->after('min_price');
            }
            if (!Schema::hasColumn('trendyol_price_rules', 'sku_filter')) {
                $table->string('sku_filter')->nullable()->after('scope_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trendyol_price_rules', function (Blueprint $table) {
            $table->dropColumn(['min_price', 'max_price', 'sku_filter']);
        });
    }
};
