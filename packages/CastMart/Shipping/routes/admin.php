<?php

use Illuminate\Support\Facades\Route;
use CastMart\Shipping\Http\Controllers\ShippingController;

/*
|--------------------------------------------------------------------------
| CastMart Shipping Routes
|--------------------------------------------------------------------------
*/

// Admin Routes
Route::group([
    'prefix' => 'admin/shipping',
    'middleware' => ['web', 'admin'],
    'as' => 'admin.shipping.',
], function () {
    // Dashboard
    Route::get('/', [ShippingController::class, 'index'])->name('index');
    
    // Gönderi detay
    Route::get('/{id}', [ShippingController::class, 'show'])->name('show');
    
    // Gönderi oluştur (sipariş için)
    Route::post('/create-from-order/{orderId}', [ShippingController::class, 'createFromOrder'])->name('create-from-order');
    
    // Manuel gönderi oluştur
    Route::post('/create', [ShippingController::class, 'create'])->name('create');
    
    // Takip sorgula
    Route::post('/track', [ShippingController::class, 'track'])->name('track');
    
    // Etiket indir
    Route::get('/{id}/label/download', [ShippingController::class, 'downloadLabel'])->name('label.download');
    Route::get('/{id}/label/view', [ShippingController::class, 'viewLabel'])->name('label.view');
    
    // İptal
    Route::post('/{id}/cancel', [ShippingController::class, 'cancel'])->name('cancel');
    
    // Toplu işlemler
    Route::post('/bulk-update-tracking', [ShippingController::class, 'bulkUpdateTracking'])->name('bulk-update-tracking');
    
    // Fiyat karşılaştırma
    Route::post('/compare-rates', [ShippingController::class, 'compareRates'])->name('compare-rates');
    
    // Kargo firmaları (AJAX)
    Route::get('/api/carriers', [ShippingController::class, 'getCarriers'])->name('carriers');
});

// Public API Routes (tracking)
Route::group([
    'prefix' => 'api/shipping',
    'middleware' => ['api'],
    'as' => 'api.shipping.',
], function () {
    Route::post('/track', [ShippingController::class, 'track'])->name('track');
});
