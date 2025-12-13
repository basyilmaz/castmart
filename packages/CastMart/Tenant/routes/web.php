<?php

use Illuminate\Support\Facades\Route;
use CastMart\Tenant\Http\Controllers\TenantController;
use CastMart\Tenant\Http\Controllers\BillingController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
*/

// Public (Tenant kayıt ve bilgi sayfaları)
Route::group([
    'prefix' => 'tenant',
    'middleware' => ['web'],
    'as' => 'tenant.',
], function () {
    // Tenant bulunamadı sayfası
    Route::view('/not-found', 'castmart-tenant::errors.not-found')->name('not-found');
    
    // Tenant askıya alındı sayfası
    Route::view('/suspended', 'castmart-tenant::errors.suspended')->name('suspended');
    
    // Yeni tenant kayıt
    Route::get('/register', [TenantController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [TenantController::class, 'register'])->name('register.submit');
    
    // Billing callback (public - iyzico'dan döner)
    Route::get('/billing/callback', [BillingController::class, 'callback'])->name('billing.callback');
});

// Tenant Billing Routes (Tenant Admin için)
Route::group([
    'prefix' => 'admin/billing',
    'middleware' => ['web', 'admin', 'tenant'],
    'as' => 'tenant.billing.',
], function () {
    Route::get('/', [BillingController::class, 'index'])->name('index');
    Route::get('/plans', [BillingController::class, 'plans'])->name('plans');
    Route::post('/checkout', [BillingController::class, 'checkout'])->name('checkout');
    Route::post('/cancel', [BillingController::class, 'cancel'])->name('cancel');
    Route::post('/change-plan', [BillingController::class, 'changePlan'])->name('change-plan');
    Route::get('/invoice/{id}', [BillingController::class, 'invoice'])->name('invoice');
    Route::get('/invoice/{id}/download', [BillingController::class, 'downloadInvoice'])->name('invoice.download');
});

// Admin Routes (Super Admin için tenant yönetimi)
Route::group([
    'prefix' => 'admin/tenants',
    'middleware' => ['web', 'admin'],
    'as' => 'admin.tenants.',
], function () {
    Route::get('/', [TenantController::class, 'index'])->name('index');
    Route::get('/create', [TenantController::class, 'create'])->name('create');
    Route::post('/', [TenantController::class, 'store'])->name('store');
    Route::get('/{id}', [TenantController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TenantController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TenantController::class, 'update'])->name('update');
    Route::delete('/{id}', [TenantController::class, 'destroy'])->name('destroy');
    
    // Tenant aksiyonları
    Route::post('/{id}/suspend', [TenantController::class, 'suspend'])->name('suspend');
    Route::post('/{id}/activate', [TenantController::class, 'activate'])->name('activate');
    Route::post('/{id}/impersonate', [TenantController::class, 'impersonate'])->name('impersonate');
});

