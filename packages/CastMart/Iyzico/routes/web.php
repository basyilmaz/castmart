<?php

use Illuminate\Support\Facades\Route;
use CastMart\Iyzico\Http\Controllers\IyzicoController;

/*
|--------------------------------------------------------------------------
| iyzico Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web']], function () {
    // Ödeme sayfasına yönlendirme
    Route::get('iyzico/redirect', [IyzicoController::class, 'redirect'])
        ->name('iyzico.redirect');
    
    // 3D Secure callback
    Route::post('iyzico/callback', [IyzicoController::class, 'callback'])
        ->name('iyzico.callback');
    
    // GET callback (bazı bankalar GET ile döner)
    Route::get('iyzico/callback', [IyzicoController::class, 'callback'])
        ->name('iyzico.callback.get');
    
    // Taksit seçenekleri (AJAX)
    Route::post('iyzico/installments', [IyzicoController::class, 'getInstallments'])
        ->name('iyzico.installments');
});

// Admin routes
Route::group([
    'prefix' => 'admin/iyzico',
    'middleware' => ['web', 'admin'],
], function () {
    // İade işlemi
    Route::post('refund', [IyzicoController::class, 'refund'])
        ->name('admin.iyzico.refund');
    
    // İptal işlemi
    Route::post('cancel', [IyzicoController::class, 'cancel'])
        ->name('admin.iyzico.cancel');
});
