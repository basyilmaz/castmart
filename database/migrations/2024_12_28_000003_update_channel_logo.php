<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update channel logo to CastMart branding
     */
    public function up(): void
    {
        // Update channel with CastMart logo
        DB::table('channels')
            ->update([
                'logo' => '/images/logo.png',
                'favicon' => '/images/favicon.png',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse logo change
    }
};
