<?php

use Illuminate\Support\Facades\Route;
use CastMart\Param\Http\Controllers\ParamController;

Route::group([
    'prefix' => 'param',
    'middleware' => ['web'],
], function () {
    // Ödeme sayfası
    Route::get('/redirect', [ParamController::class, 'redirect'])->name('param.redirect');
    Route::post('/initiate', [ParamController::class, 'initiate'])->name('param.initiate');
    
    // Callbacks (CSRF koruması olmadan)
    Route::post('/callback/success', [ParamController::class, 'callbackSuccess'])
        ->name('param.callback.success')
        ->withoutMiddleware(['web']);
    
    Route::post('/callback/fail', [ParamController::class, 'callbackFail'])
        ->name('param.callback.fail')
        ->withoutMiddleware(['web']);
    
    // API endpoints
    Route::post('/api/installments', [ParamController::class, 'getInstallments'])->name('param.api.installments');
    Route::post('/api/bin-query', [ParamController::class, 'queryBin'])->name('param.api.bin-query');
});
