<?php

use Illuminate\Support\Facades\Route;
use CastMart\PayTR\Http\Controllers\PayTRController;

/*
|--------------------------------------------------------------------------
| PayTR Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'paytr',
    'middleware' => ['web'],
], function () {
    // Ödeme sayfasına yönlendirme
    Route::get('/redirect', [PayTRController::class, 'redirect'])->name('paytr.redirect');
    
    // Başarılı ödeme
    Route::get('/success', [PayTRController::class, 'success'])->name('paytr.success');
    
    // Başarısız ödeme
    Route::get('/fail', [PayTRController::class, 'fail'])->name('paytr.fail');
    
    // PayTR callback (POST - no CSRF)
    Route::post('/callback', [PayTRController::class, 'callback'])
        ->name('paytr.callback')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
    
    // Taksit sorgulama (AJAX)
    Route::post('/installments', [PayTRController::class, 'installments'])->name('paytr.installments');
});
