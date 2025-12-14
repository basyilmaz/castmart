<?php

use Illuminate\Support\Facades\Route;
use CastMart\Trendyol\Http\Controllers\TrendyolController;

Route::group([
    'prefix' => 'admin/marketplace/trendyol',
    'middleware' => ['web', 'admin'],
], function () {
    // Dashboard
    Route::get('/', [TrendyolController::class, 'dashboard'])->name('admin.marketplace.trendyol.dashboard');

    // Hesap Yönetimi
    Route::get('/accounts', [TrendyolController::class, 'accounts'])->name('admin.marketplace.trendyol.accounts');
    Route::get('/accounts/create', [TrendyolController::class, 'createAccount'])->name('admin.marketplace.trendyol.accounts.create');
    Route::post('/accounts', [TrendyolController::class, 'storeAccount'])->name('admin.marketplace.trendyol.accounts.store');
    Route::get('/accounts/{account}/edit', [TrendyolController::class, 'editAccount'])->name('admin.marketplace.trendyol.accounts.edit');
    Route::put('/accounts/{account}', [TrendyolController::class, 'updateAccount'])->name('admin.marketplace.trendyol.accounts.update');
    Route::delete('/accounts/{account}', [TrendyolController::class, 'deleteAccount'])->name('admin.marketplace.trendyol.accounts.delete');
    Route::post('/accounts/{account}/test', [TrendyolController::class, 'testConnection'])->name('admin.marketplace.trendyol.accounts.test');

    // Sipariş Yönetimi
    Route::get('/orders', [TrendyolController::class, 'orders'])->name('admin.marketplace.trendyol.orders');
    Route::get('/orders/{order}', [TrendyolController::class, 'orderDetail'])->name('admin.marketplace.trendyol.orders.detail');
    Route::post('/orders/{order}/tracking', [TrendyolController::class, 'updateTracking'])->name('admin.marketplace.trendyol.orders.tracking');
    Route::post('/accounts/{account}/sync-orders', [TrendyolController::class, 'syncOrders'])->name('admin.marketplace.trendyol.orders.sync');

    // Müşteri Soruları
    Route::get('/questions', [TrendyolController::class, 'questions'])->name('admin.marketplace.trendyol.questions');
    Route::post('/questions/{question}/answer', [TrendyolController::class, 'answerQuestion'])->name('admin.marketplace.trendyol.questions.answer');

    // Ürün Yönetimi
    Route::get('/products', [TrendyolController::class, 'products'])->name('admin.marketplace.trendyol.products');
    Route::get('/products/create', [TrendyolController::class, 'createProduct'])->name('admin.marketplace.trendyol.products.create');
    Route::post('/products/send', [TrendyolController::class, 'sendProduct'])->name('admin.marketplace.trendyol.products.send');
    Route::post('/products/link', [TrendyolController::class, 'linkProduct'])->name('admin.marketplace.trendyol.products.link');
    Route::post('/products/sync-stock', [TrendyolController::class, 'syncStock'])->name('admin.marketplace.trendyol.products.sync-stock');
    Route::post('/products/import', [TrendyolController::class, 'importProducts'])->name('admin.marketplace.trendyol.products.import');

    // API Endpoints (AJAX)
    Route::get('/api/categories', [TrendyolController::class, 'getCategories'])->name('admin.marketplace.trendyol.api.categories');
    Route::get('/api/brands', [TrendyolController::class, 'getBrands'])->name('admin.marketplace.trendyol.api.brands');
    Route::get('/api/category-attributes/{categoryId}', [TrendyolController::class, 'getCategoryAttributes'])->name('admin.marketplace.trendyol.api.category-attributes');
    Route::get('/api/search-categories', [TrendyolController::class, 'searchCategories'])->name('admin.marketplace.trendyol.api.search-categories');
    Route::get('/api/batch-status/{batchId}', [TrendyolController::class, 'getBatchStatus'])->name('admin.marketplace.trendyol.api.batch-status');
    Route::get('/api/buybox/{barcode}', [TrendyolController::class, 'checkBuybox'])->name('admin.marketplace.trendyol.api.buybox');

    // Batch İşlem Takibi
    Route::get('/batch-requests', [TrendyolController::class, 'batchRequests'])->name('admin.marketplace.trendyol.batch-requests');

    // Kategori Wizard
    Route::get('/category-wizard', [TrendyolController::class, 'categoryWizard'])->name('admin.marketplace.trendyol.category-wizard');

    // İade Yönetimi
    Route::get('/claims', [TrendyolController::class, 'claims'])->name('admin.marketplace.trendyol.claims');
    Route::post('/claims/{claimId}/approve', [TrendyolController::class, 'approveClaim'])->name('admin.marketplace.trendyol.claims.approve');

    // Varyant Yönetimi
    Route::get('/variants', [TrendyolController::class, 'variants'])->name('admin.marketplace.trendyol.variants');
    Route::post('/variants/save-mappings', [TrendyolController::class, 'saveVariantMappings'])->name('admin.marketplace.trendyol.variants.save');

    // Kargo Yönetimi
    Route::get('/cargo', [TrendyolController::class, 'cargo'])->name('admin.marketplace.trendyol.cargo');
    Route::post('/cargo/update-tracking', [TrendyolController::class, 'updateBulkTracking'])->name('admin.marketplace.trendyol.cargo.update-bulk');

    // Fiyat Analizi
    Route::get('/price-analysis', [TrendyolController::class, 'priceAnalysis'])->name('admin.marketplace.trendyol.price-analysis');
    Route::post('/price-analysis/rules', [TrendyolController::class, 'savePriceRules'])->name('admin.marketplace.trendyol.price-analysis.rules');

    // E-Fatura Yönetimi
    Route::get('/invoices', [TrendyolController::class, 'invoices'])->name('admin.marketplace.trendyol.invoices');
    Route::post('/invoices/settings', [TrendyolController::class, 'saveInvoiceSettings'])->name('admin.marketplace.trendyol.invoices.settings');
    Route::post('/invoices/connect-bizimhesap', [TrendyolController::class, 'connectBizimhesap'])->name('admin.marketplace.trendyol.invoices.connect-bizimhesap');
    Route::post('/invoices/test-bizimhesap', [TrendyolController::class, 'testBizimhesap'])->name('admin.marketplace.trendyol.invoices.test-bizimhesap');
    Route::post('/invoices/create-from-order', [TrendyolController::class, 'createInvoiceFromOrder'])->name('admin.marketplace.trendyol.invoices.create-from-order');
    Route::get('/invoices/{invoiceId}/download', [TrendyolController::class, 'downloadInvoice'])->name('admin.marketplace.trendyol.invoices.download');

    // Komisyon Hesaplayıcı
    Route::get('/commission-calculator', [TrendyolController::class, 'commissionCalculator'])->name('admin.marketplace.trendyol.commission-calculator');
    Route::get('/commission-calculator/bulk', [TrendyolController::class, 'bulkCommissionCalculator'])->name('admin.marketplace.trendyol.commission-calculator.bulk');
    Route::get('/commission-calculator/variants', [TrendyolController::class, 'variantCalculator'])->name('admin.marketplace.trendyol.commission-calculator.variants');
    Route::get('/commission-rates', [TrendyolController::class, 'commissionRates'])->name('admin.marketplace.trendyol.commission-rates');
    Route::post('/commission-rates/save', [TrendyolController::class, 'saveCommissionRate'])->name('admin.marketplace.trendyol.commission-rates.save');
    Route::post('/commission-rates/delete', [TrendyolController::class, 'deleteCommissionRate'])->name('admin.marketplace.trendyol.commission-rates.delete');

    // Scraping
    Route::post('/scrape/product', [TrendyolController::class, 'scrapeProduct'])->name('admin.marketplace.trendyol.scrape.product');
    Route::post('/scrape/reviews', [TrendyolController::class, 'scrapeReviews'])->name('admin.marketplace.trendyol.scrape.reviews');

    // Intelligence - 7. His
    Route::get('/intelligence', [TrendyolController::class, 'intelligence'])->name('admin.marketplace.trendyol.intelligence');
    Route::get('/intelligence/alerts', [TrendyolController::class, 'getAlerts'])->name('admin.marketplace.trendyol.intelligence.alerts');
    Route::post('/intelligence/dismiss-alert', [TrendyolController::class, 'dismissAlert'])->name('admin.marketplace.trendyol.intelligence.dismiss');

    // BuyBox Tracker
    Route::get('/buybox-tracker', [TrendyolController::class, 'buyboxTracker'])->name('admin.marketplace.trendyol.buybox-tracker');
    Route::post('/buybox-tracker/update-price', [TrendyolController::class, 'updateBuyboxPrice'])->name('admin.marketplace.trendyol.buybox.update-price');
    Route::get('/buybox-tracker/data', [TrendyolController::class, 'getBuyboxData'])->name('admin.marketplace.trendyol.buybox.data');

    // Price Rules
    Route::get('/price-rules', [TrendyolController::class, 'priceRules'])->name('admin.marketplace.trendyol.price-rules');
    Route::post('/price-rules/save', [TrendyolController::class, 'savePriceRule'])->name('admin.marketplace.trendyol.price-rules.save');
    Route::post('/price-rules/delete', [TrendyolController::class, 'deletePriceRule'])->name('admin.marketplace.trendyol.price-rules.delete');
    Route::post('/price-rules/toggle', [TrendyolController::class, 'togglePriceRule'])->name('admin.marketplace.trendyol.price-rules.toggle');

    // Predictions
    Route::get('/predictions', [TrendyolController::class, 'predictions'])->name('admin.marketplace.trendyol.predictions');

    // Notifications
    Route::get('/notifications', [TrendyolController::class, 'notifications'])->name('admin.marketplace.trendyol.notifications');
    Route::post('/notifications/mark-read', [TrendyolController::class, 'markNotificationRead'])->name('admin.marketplace.trendyol.notifications.mark-read');
    Route::post('/notifications/mark-all-read', [TrendyolController::class, 'markAllNotificationsRead'])->name('admin.marketplace.trendyol.notifications.mark-all-read');

    // Excel Import/Export
    Route::get('/excel/export', [TrendyolController::class, 'excelExportPage'])->name('admin.marketplace.trendyol.excel.export');
    Route::post('/excel/export-products', [TrendyolController::class, 'exportProducts'])->name('admin.marketplace.trendyol.excel.export-products');
    Route::post('/excel/export-orders', [TrendyolController::class, 'exportOrders'])->name('admin.marketplace.trendyol.excel.export-orders');
    Route::post('/excel/export-price-template', [TrendyolController::class, 'exportPriceTemplate'])->name('admin.marketplace.trendyol.excel.export-price-template');
    Route::post('/excel/export-commission-report', [TrendyolController::class, 'exportCommissionReport'])->name('admin.marketplace.trendyol.excel.export-commission');
    Route::get('/excel/import', [TrendyolController::class, 'excelImportPage'])->name('admin.marketplace.trendyol.excel.import');
    Route::post('/excel/import-price-update', [TrendyolController::class, 'importPriceUpdate'])->name('admin.marketplace.trendyol.excel.import-price');
    Route::post('/excel/import-products', [TrendyolController::class, 'importProducts'])->name('admin.marketplace.trendyol.excel.import-products');

    // Chart.js API Endpoints
    Route::get('/api/charts/all', [TrendyolController::class, 'getChartData'])->name('admin.marketplace.trendyol.charts.all');
    Route::get('/api/charts/sales', [TrendyolController::class, 'getSalesChartData'])->name('admin.marketplace.trendyol.charts.sales');
    Route::get('/api/charts/categories', [TrendyolController::class, 'getCategoryChartData'])->name('admin.marketplace.trendyol.charts.categories');
    Route::get('/api/charts/buybox', [TrendyolController::class, 'getBuyboxChartData'])->name('admin.marketplace.trendyol.charts.buybox');
    Route::get('/api/charts/stock', [TrendyolController::class, 'getStockChartData'])->name('admin.marketplace.trendyol.charts.stock');
    Route::get('/api/charts/commission', [TrendyolController::class, 'getCommissionChartData'])->name('admin.marketplace.trendyol.charts.commission');
    Route::get('/api/charts/order-status', [TrendyolController::class, 'getOrderStatusChartData'])->name('admin.marketplace.trendyol.charts.order-status');
});

