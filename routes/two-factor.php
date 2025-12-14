<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Two-Factor Authentication Routes
|--------------------------------------------------------------------------
*/

// Customer 2FA Routes
Route::group([
    'prefix' => 'customer/two-factor',
    'middleware' => ['web', 'customer'],
], function () {
    // Ayarlar
    Route::get('/settings', [TwoFactorController::class, 'settings'])->name('customer.two-factor.settings');
    
    // Etkinleştirme
    Route::get('/enable', [TwoFactorController::class, 'enable'])->name('customer.two-factor.enable');
    Route::post('/enable', [TwoFactorController::class, 'confirmEnable'])->name('customer.two-factor.confirm-enable');
    
    // Devre dışı bırakma
    Route::post('/disable', [TwoFactorController::class, 'disable'])->name('customer.two-factor.disable');
    
    // Recovery kodları yenileme
    Route::post('/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('customer.two-factor.recovery-codes');
});

// Customer 2FA Challenge (login sonrası doğrulama)
Route::group([
    'prefix' => 'customer/two-factor',
    'middleware' => ['web'],
], function () {
    Route::get('/challenge', [TwoFactorController::class, 'challenge'])->name('customer.two-factor.challenge');
    Route::post('/verify', [TwoFactorController::class, 'verify'])->name('customer.two-factor.verify');
});

// Admin 2FA Routes
Route::group([
    'prefix' => 'admin/two-factor',
    'middleware' => ['web', 'admin'],
], function () {
    // Ayarlar
    Route::get('/settings', [TwoFactorController::class, 'adminSettings'])->name('admin.two-factor.settings');
    
    // Etkinleştirme
    Route::get('/enable', [TwoFactorController::class, 'adminEnable'])->name('admin.two-factor.enable');
    Route::post('/enable', [TwoFactorController::class, 'adminConfirmEnable'])->name('admin.two-factor.confirm-enable');
    
    // Devre dışı bırakma
    Route::post('/disable', [TwoFactorController::class, 'adminDisable'])->name('admin.two-factor.disable');
});

// Admin 2FA Challenge
Route::group([
    'prefix' => 'admin/two-factor',
    'middleware' => ['web'],
], function () {
    Route::get('/challenge', [TwoFactorController::class, 'adminChallenge'])->name('admin.two-factor.challenge');
    Route::post('/verify', [TwoFactorController::class, 'adminVerify'])->name('admin.two-factor.verify');
});
