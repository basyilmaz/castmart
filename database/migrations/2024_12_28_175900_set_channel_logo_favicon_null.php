<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sets logo and favicon to NULL to avoid Storage::url error with R2 driver
     */
    public function up(): void
    {
        // Set logo and favicon to null for all channels
        // This bypasses the Storage::url issue completely
        DB::table('channels')->update([
            'logo' => null,
            'favicon' => null,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore original values
    }
};
