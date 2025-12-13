<?php

use Illuminate\Support\Facades\Route;
use CastMart\Marketing\Http\Controllers\CouponController;
use CastMart\Marketing\Http\Controllers\LoyaltyController;
use CastMart\Marketing\Http\Controllers\ChatbotController;

/*
|--------------------------------------------------------------------------
| Marketing Admin Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'admin/marketing',
    'middleware' => ['web', 'admin'],
    'as' => 'admin.marketing.',
], function () {
    // Dashboard
    Route::get('/', [CouponController::class, 'dashboard'])->name('dashboard');

    // Kuponlar
    Route::resource('coupons', CouponController::class);
    Route::post('coupons/{id}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
    Route::post('coupons/generate-code', [CouponController::class, 'generateCode'])->name('coupons.generate-code');

    // Sadakat
    Route::get('loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
    Route::get('loyalty/members', [LoyaltyController::class, 'members'])->name('loyalty.members');
    Route::get('loyalty/members/{id}', [LoyaltyController::class, 'memberDetail'])->name('loyalty.member');
    Route::post('loyalty/members/{id}/adjust', [LoyaltyController::class, 'adjustPoints'])->name('loyalty.adjust');
    Route::get('loyalty/transactions', [LoyaltyController::class, 'transactions'])->name('loyalty.transactions');
});

/*
|--------------------------------------------------------------------------
| Marketing API Routes (Storefront)
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix' => 'api/marketing',
    'middleware' => ['api'],
    'as' => 'api.marketing.',
], function () {
    // Kupon doğrula
    Route::post('coupons/validate', [CouponController::class, 'validateApi'])->name('coupons.validate');
    
    // Chatbot
    Route::post('chatbot', [ChatbotController::class, 'message'])->name('chatbot.message');
    Route::get('chatbot/widget.js', [ChatbotController::class, 'widget'])->name('chatbot.widget');
    
    // Sadakat (authenticated)
    Route::middleware('auth:customer')->group(function () {
        Route::get('loyalty/account', [LoyaltyController::class, 'account'])->name('loyalty.account');
        Route::get('loyalty/transactions', [LoyaltyController::class, 'myTransactions'])->name('loyalty.my-transactions');
        Route::post('loyalty/redeem', [LoyaltyController::class, 'redeem'])->name('loyalty.redeem');
    });
});

