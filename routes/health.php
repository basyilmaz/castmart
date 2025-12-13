<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Health Check Route
|--------------------------------------------------------------------------
| Railway ve diğer orchestration sistemleri için health check endpoint
*/

Route::get('/api/health', function () {
    $health = [
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name', 'CastMart'),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
    ];

    // Database check (non-blocking)
    try {
        \DB::connection()->getPdo();
        $health['database'] = 'connected';
    } catch (\Exception $e) {
        $health['database'] = 'not_configured';
    }

    // Always return 200 for Railway healthcheck
    return response()->json($health, 200);
});

Route::get('/api/ping', function () {
    return response()->json(['pong' => true, 'time' => now()->toIso8601String()]);
});

// Simple health check that always works
Route::get('/health', function () {
    return 'OK';
});

