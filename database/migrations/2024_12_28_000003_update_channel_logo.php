<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update channel - clear logo/favicon to use default from blade
     * New logo is in public/images/logo.png and referenced directly in templates
     */
    public function up(): void
    {
        // Clear channel logo/favicon to avoid Storage::url error with R2
        // The new logo is served directly from public/images/
        DB::table('channels')
            ->update([
                'logo' => null,
                'favicon' => null,
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
