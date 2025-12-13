<?php

use Illuminate\Support\Facades\Route;
use CastMart\SMS\Http\Controllers\SmsController;

/*
|--------------------------------------------------------------------------
| CastMart SMS Routes
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::group([
    'prefix' => 'admin/sms',
    'middleware' => ['web', 'admin'],
    'as' => 'admin.sms.',
], function () {
    // Dashboard
    Route::get('/', [SmsController::class, 'index'])->name('index');
    
    // SMS Gönder
    Route::post('/send', [SmsController::class, 'send'])->name('send');
    Route::post('/send-bulk', [SmsController::class, 'sendBulk'])->name('send-bulk');
    
    // Durum sorgula
    Route::post('/status', [SmsController::class, 'getStatus'])->name('status');
    
    // Bakiye
    Route::get('/balance', [SmsController::class, 'getBalance'])->name('balance');
    
    // İstatistikler
    Route::get('/statistics', [SmsController::class, 'statistics'])->name('statistics');
    
    // Şablonlar
    Route::get('/templates', [SmsController::class, 'templates'])->name('templates');
    
    // Test SMS
    Route::post('/test', [SmsController::class, 'testSms'])->name('test');
});

// API Routes (OTP için)
Route::group([
    'prefix' => 'api/sms',
    'middleware' => ['api', 'throttle:10,1'], // 1 dakikada max 10 istek
    'as' => 'api.sms.',
], function () {
    Route::post('/send-otp', [SmsController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify-otp', [SmsController::class, 'verifyOtp'])->name('verify-otp');
});
