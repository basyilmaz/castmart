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
        'app' => config('app.name'),
        'version' => config('app.version', '1.0.0'),
        'environment' => app()->environment(),
    ];

    // Database check
    try {
        \DB::connection()->getPdo();
        $health['database'] = 'connected';
    } catch (\Exception $e) {
        $health['database'] = 'error';
        $health['status'] = 'unhealthy';
    }

    // Cache check
    try {
        cache()->put('health_check', true, 10);
        $health['cache'] = cache()->get('health_check') ? 'working' : 'error';
    } catch (\Exception $e) {
        $health['cache'] = 'error';
    }

    // Queue check (optional)
    try {
        $health['queue'] = config('queue.default');
    } catch (\Exception $e) {
        $health['queue'] = 'unknown';
    }

    $statusCode = $health['status'] === 'healthy' ? 200 : 503;

    return response()->json($health, $statusCode);
});

Route::get('/api/ping', function () {
    return response()->json(['pong' => true, 'time' => now()->toIso8601String()]);
});
