<?php

namespace CastMart\Trendyol\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\CustomerQuestion;
use CastMart\Trendyol\Services\TrendyolService;
use CastMart\Trendyol\Services\TrendyolScraperService;

class TrendyolController extends Controller
{
    // ===== HESAP YÖNETİMİ =====

    public function accounts()
    {
        $accounts = MarketplaceAccount::marketplace('trendyol')
            ->with(['listings', 'orders'])
            ->get();

        return view('trendyol::admin.accounts.index', compact('accounts'));
    }

    public function createAccount()
    {
        return view('trendyol::admin.accounts.create');
    }

    public function storeAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|string',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $account = MarketplaceAccount::create([
            'marketplace' => 'trendyol',
            'name' => $request->name,
            'credentials' => [
                'supplier_id' => $request->supplier_id,
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
            ],
            'is_active' => true,
        ]);

        return redirect()->route('admin.marketplace.trendyol.accounts')
            ->with('success', 'Trendyol hesabı başarıyla eklendi.');
    }

    public function testConnection(MarketplaceAccount $account)
    {
        $service = new TrendyolService($account);
        $result = $service->testConnection();

        if ($result === true) {
            return response()->json([
                'success' => true,
                'message' => 'Bağlantı başarılı!',
            ]);
        }

        // Detaylı hata mesajı
        $errorMessage = 'Bağlantı başarısız.';
        if (is_array($result)) {
            if (isset($result['status'])) {
                $errorMessage .= " HTTP {$result['status']}.";
            }
            if (isset($result['error'])) {
                $errorMessage .= " Hata: " . (is_string($result['error']) ? $result['error'] : json_encode($result['error']));
            }
        }

        return response()->json([
            'success' => false,
            'message' => $errorMessage,
            'details' => is_array($result) ? $result : null,
        ]);
    }

    public function editAccount(MarketplaceAccount $account)
    {
        return view('trendyol::admin.accounts.edit', compact('account'));
    }

    public function updateAccount(Request $request, MarketplaceAccount $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|string',
            'api_key' => 'required|string',
            'api_secret' => 'required|string',
        ]);

        $account->update([
            'name' => $request->name,
            'credentials' => [
                'supplier_id' => $request->supplier_id,
                'api_key' => $request->api_key,
                'api_secret' => $request->api_secret,
            ],
        ]);

        return redirect()->route('admin.marketplace.trendyol.accounts')
            ->with('success', 'Trendyol hesabı başarıyla güncellendi.');
    }

    public function deleteAccount(MarketplaceAccount $account)
    {
        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hesap başarıyla silindi.',
        ]);
    }

    // ===== SİPARİŞ YÖNETİMİ =====

    public function orders(Request $request)
    {
        $orders = MarketplaceOrder::whereHas('account', function ($q) {
            $q->where('marketplace', 'trendyol');
        })
        ->with('account')
        ->orderByDesc('created_at')
        ->paginate(20);

        return view('trendyol::admin.orders.index', compact('orders'));
    }

    public function orderDetail(MarketplaceOrder $order)
    {
        return view('trendyol::admin.orders.detail', compact('order'));
    }

    public function updateTracking(Request $request, MarketplaceOrder $order)
    {
        $request->validate([
            'tracking_number' => 'required|string',
            'cargo_provider' => 'required|string',
        ]);

        $service = new TrendyolService($order->account);
        $success = $service->sendTrackingNumber(
            $order->package_id,
            $request->tracking_number,
            $request->cargo_provider
        );

        if ($success) {
            $order->update([
                'tracking_number' => $request->tracking_number,
                'cargo_provider' => $request->cargo_provider,
                'status' => MarketplaceOrder::STATUS_SHIPPED,
                'shipped_at' => now(),
            ]);

            return back()->with('success', 'Kargo bilgileri güncellendi.');
        }

        return back()->with('error', 'Kargo bilgileri güncellenemedi.');
    }

    public function syncOrders(MarketplaceAccount $account)
    {
        $service = new TrendyolService($account);
        $count = $service->syncOrders();

        return response()->json([
            'success' => true,
            'message' => "{$count} yeni sipariş senkronize edildi.",
        ]);
    }

    // ===== MÜŞTERİ SORULARI =====

    public function questions(Request $request)
    {
        $questions = CustomerQuestion::whereHas('account', function ($q) {
            $q->where('marketplace', 'trendyol');
        })
        ->with('account')
        ->orderByDesc('asked_at')
        ->paginate(20);

        return view('trendyol::admin.questions.index', compact('questions'));
    }

    public function answerQuestion(Request $request, CustomerQuestion $question)
    {
        $request->validate([
            'answer' => 'required|string|min:10',
        ]);

        $service = new TrendyolService($question->account);
        $success = $service->answerQuestion($question->external_question_id, $request->answer);

        if ($success) {
            $question->update([
                'answer_text' => $request->answer,
                'status' => CustomerQuestion::STATUS_ANSWERED,
                'answered_at' => now(),
            ]);

            return back()->with('success', 'Soru başarıyla cevaplandı.');
        }

        return back()->with('error', 'Soru cevaplanamadı.');
    }

    // ===== ÜRÜN SCRAPING =====

    public function scrapeProduct(Request $request)
    {
        $request->validate([
            'url' => 'required|url|regex:/trendyol\.com/',
        ]);

        $url = $request->url;

        try {
            // Önce scraping dene
            $scraper = app(TrendyolScraperService::class);
            $productData = $scraper->extractProductFromUrl($url);

            if ($productData && !empty($productData['price'])) {
                return response()->json([
                    'success' => true,
                    'data' => $productData,
                ]);
            }
        } catch (\Exception $e) {
            // Scraping başarısız, fallback'e geç
            \Log::warning('Scraping failed, using fallback', ['error' => $e->getMessage()]);
        }

        // Fallback: URL'den bilgi çıkart
        $fallbackData = $this->extractProductInfoFromUrl($url);

        if ($fallbackData) {
            return response()->json([
                'success' => true,
                'data' => $fallbackData,
                'source' => 'fallback',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Ürün bilgileri otomatik alınamadı. Lütfen fiyatı manuel girin.',
        ], 400);
    }

    /**
     * URL'den ürün bilgilerini çıkart (fallback)
     */
    protected function extractProductInfoFromUrl(string $url): ?array
    {
        // URL yapısı: https://www.trendyol.com/marka/urun-adi-p-123456789
        
        // Ürün ID'sini çıkart
        $productId = null;
        if (preg_match('/-p-(\d+)/', $url, $matches)) {
            $productId = $matches[1];
        }

        // URL'den marka ve ürün adını çıkart
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        $segments = array_filter(explode('/', $path));
        
        $brand = null;
        $productName = null;
        
        if (count($segments) >= 2) {
            $brand = ucfirst(str_replace('-', ' ', array_values($segments)[0] ?? ''));
            $productSlug = array_values($segments)[1] ?? '';
            // -p-123456789 kısmını kaldır
            $productSlug = preg_replace('/-p-\d+$/', '', $productSlug);
            $productName = ucwords(str_replace('-', ' ', $productSlug));
        }

        // Kategoriyi tahmin et
        $category = $this->guessCategoryFromProductName($productName ?? '');

        return [
            'name' => $productName,
            'brand' => $brand,
            'product_id' => $productId,
            'price' => null, // Manual girilecek
            'category_path' => [$category],
            'source' => 'url_parse',
            'message' => 'Fiyat otomatik alınamadı. Lütfen ürün sayfasından fiyatı kopyalayın.',
        ];
    }

    /**
     * Ürün adından kategoriyi tahmin et
     */
    protected function guessCategoryFromProductName(string $name): string
    {
        $name = mb_strtolower($name);
        
        $categoryKeywords = [
            'Kadın Giyim' => ['elbise', 'bluz', 'gömlek kadın', 'tunik', 'kadın pantolon', 'etek'],
            'Erkek Giyim' => ['erkek gömlek', 'erkek pantolon', 'erkek tişört', 'erkek mont'],
            'Ayakkabı' => ['ayakkabı', 'bot', 'sneaker', 'topuklu', 'sandalet', 'terlik'],
            'Çanta' => ['çanta', 'sırt çantası', 'cüzdan', 'el çantası'],
            'Elektronik' => ['telefon', 'laptop', 'tablet', 'kulaklık', 'şarj', 'kablo'],
            'Kozmetik' => ['parfüm', 'makyaj', 'ruj', 'fondöten', 'serum', 'krem'],
            'Ev & Yaşam' => ['mobilya', 'dekorasyon', 'halı', 'perde', 'yastık', 'nevresim'],
            'Spor' => ['spor', 'fitness', 'koşu', 'yoga', 'pilates'],
        ];

        foreach ($categoryKeywords as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($name, $keyword)) {
                    return $category;
                }
            }
        }

        return 'Genel';
    }


    public function scrapeReviews(Request $request)
    {
        $request->validate([
            'url' => 'required|url|regex:/trendyol\.com/',
        ]);

        $scraper = app(TrendyolScraperService::class);
        $reviews = $scraper->scrapeReviews($request->url);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    // ===== DASHBOARD =====

    public function dashboard()
    {
        $stats = [
            'accounts' => MarketplaceAccount::marketplace('trendyol')->active()->count(),
            'products' => MarketplaceAccount::marketplace('trendyol')
                ->join('marketplace_listings', 'marketplace_accounts.id', '=', 'marketplace_listings.account_id')
                ->where('marketplace_listings.status', 'active')
                ->count(),
            'pending_orders' => MarketplaceOrder::whereHas('account', fn($q) => $q->where('marketplace', 'trendyol'))
                ->where('status', 'new')
                ->count(),
            'pending_questions' => CustomerQuestion::whereHas('account', fn($q) => $q->where('marketplace', 'trendyol'))
                ->where('status', 'pending')
                ->count(),
        ];

        return view('trendyol::admin.dashboard', compact('stats'));
    }

    // ===== ÜRÜN YÖNETİMİ =====

    public function products(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        
        if (!$account) {
            return redirect()->route('admin.marketplace.trendyol.accounts')
                ->with('error', 'Aktif Trendyol hesabı bulunamadı.');
        }

        $service = new TrendyolService($account);
        $params = [
            'size' => 20,
            'page' => $request->get('page', 0),
        ];

        if ($request->has('approved')) {
            $params['approved'] = $request->approved === '1';
        }

        $products = $service->getProducts($params);

        return view('trendyol::admin.products.index', compact('products'));
    }

    public function createProduct()
    {
        // CastMart ürünlerini al
        $castmartProducts = \Webkul\Product\Models\Product::with('images')
            ->where('type', 'simple')
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return view('trendyol::admin.products.create', compact('castmartProducts'));
    }

    public function sendProduct(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'category_id' => 'required|integer',
            'brand_name' => 'required|string',
            'barcode' => 'required|string',
            'list_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
        ]);

        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return back()->with('error', 'Aktif Trendyol hesabı bulunamadı.');
        }

        $product = \Webkul\Product\Models\Product::findOrFail($request->product_id);
        $service = new TrendyolService($account);

        $productData = [
            'barcode' => $request->barcode,
            'title' => $product->name,
            'productMainId' => $product->sku,
            'brandId' => $this->getBrandId($service, $request->brand_name),
            'categoryId' => $request->category_id,
            'quantity' => $request->quantity,
            'stockCode' => $product->sku,
            'dimensionalWeight' => 1,
            'description' => strip_tags($product->description ?? $product->short_description ?? $product->name),
            'currencyType' => 'TRY',
            'listPrice' => $request->list_price,
            'salePrice' => $request->sale_price,
            'vatRate' => 20,
            'cargoCompanyId' => $request->cargo_company_id ?? 10,
            'images' => [],
            'attributes' => [],
        ];

        // Ürün görselleri
        foreach ($product->images as $image) {
            $productData['images'][] = ['url' => asset('storage/' . $image->path)];
        }

        $result = $service->createProduct($productData);

        if (isset($result['batchRequestId'])) {
            // Eşleştirme kaydı oluştur
            \CastMart\Marketplace\Models\MarketplaceListing::create([
                'account_id' => $account->id,
                'product_id' => $product->id,
                'external_id' => $request->barcode,
                'external_sku' => $product->sku,
                'status' => 'pending',
                'price' => $request->sale_price,
                'stock' => $request->quantity,
            ]);

            return redirect()->route('admin.marketplace.trendyol.products')
                ->with('success', 'Ürün Trendyol\'a gönderildi. Batch ID: ' . $result['batchRequestId']);
        }

        return back()->with('error', 'Ürün gönderilemedi: ' . json_encode($result['errors'] ?? $result));
    }

    public function linkProduct(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string',
            'product_id' => 'required|exists:products,id',
        ]);

        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.']);
        }

        $existing = \CastMart\Marketplace\Models\MarketplaceListing::where('account_id', $account->id)
            ->where('external_id', $request->barcode)
            ->first();

        if ($existing) {
            $existing->update(['product_id' => $request->product_id]);
        } else {
            \CastMart\Marketplace\Models\MarketplaceListing::create([
                'account_id' => $account->id,
                'product_id' => $request->product_id,
                'external_id' => $request->barcode,
                'status' => 'active',
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Ürün eşleştirildi.']);
    }

    public function syncStock(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.']);
        }

        $service = new TrendyolService($account);

        // Eğer request'te items varsa (tekli güncelleme modal'ından)
        if ($request->has('items')) {
            $items = $request->items;
        } else {
            // Eşleştirilmiş ürünlerden toplu güncelleme
            $listings = \CastMart\Marketplace\Models\MarketplaceListing::where('account_id', $account->id)
                ->whereNotNull('product_id')
                ->with('product')
                ->get();

            $items = [];
            foreach ($listings as $listing) {
                if ($listing->product) {
                    $items[] = [
                        'barcode' => $listing->external_id,
                        'quantity' => $listing->product->totalQuantity(),
                        'salePrice' => $listing->product->price,
                        'listPrice' => $listing->product->price,
                    ];
                }
            }
        }

        if (empty($items)) {
            return response()->json(['success' => false, 'message' => 'Güncellenecek ürün yok.']);
        }

        $result = $service->updateInventory($items);

        return response()->json([
            'success' => isset($result['batchRequestId']),
            'message' => isset($result['batchRequestId']) 
                ? count($items) . ' ürün Trendyol\'a gönderildi. Batch: ' . $result['batchRequestId']
                : 'Güncelleme başarısız: ' . json_encode($result),
        ]);
    }

    public function importProducts(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.']);
        }

        $service = new TrendyolService($account);
        $products = $service->getProducts(['size' => 100]);
        
        $imported = 0;
        foreach ($products['content'] ?? [] as $trendyolProduct) {
            // Mevcut eşleştirme var mı kontrol et
            $existing = \CastMart\Marketplace\Models\MarketplaceListing::where('account_id', $account->id)
                ->where('external_id', $trendyolProduct['barcode'])
                ->first();

            if (!$existing) {
                \CastMart\Marketplace\Models\MarketplaceListing::create([
                    'account_id' => $account->id,
                    'external_id' => $trendyolProduct['barcode'],
                    'external_sku' => $trendyolProduct['stockCode'] ?? $trendyolProduct['barcode'],
                    'status' => $trendyolProduct['approved'] ? 'active' : 'pending',
                    'price' => $trendyolProduct['salePrice'] ?? 0,
                    'stock' => $trendyolProduct['quantity'] ?? 0,
                    'listing_data' => $trendyolProduct,
                ]);
                $imported++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$imported} ürün içe aktarıldı.",
        ]);
    }

    public function getCategories()
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['categories' => []]);
        }

        $service = new TrendyolService($account);
        return response()->json($service->getCategories());
    }

    public function getBrands(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['brands' => []]);
        }

        $service = new TrendyolService($account);
        $params = [];
        if ($request->has('name')) {
            $params['name'] = $request->name;
        }
        return response()->json($service->getBrands($params));
    }

    private function getBrandId(TrendyolService $service, string $brandName): int
    {
        $brands = $service->getBrands(['name' => $brandName]);
        if (!empty($brands['brands'])) {
            return $brands['brands'][0]['id'];
        }
        return 0; // Varsayılan
    }

    // ===== GELİŞMİŞ ÖZELLİKLER =====

    public function getCategoryAttributes($categoryId)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['error' => 'Aktif hesap bulunamadı.']);
        }

        $service = new TrendyolService($account);
        return response()->json($service->getCategoryAttributes((int) $categoryId));
    }

    public function searchCategories(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['categories' => []]);
        }

        $service = new TrendyolService($account);
        $keyword = $request->get('q', '');
        return response()->json($service->searchCategories($keyword));
    }

    public function getBatchStatus($batchId)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['error' => 'Aktif hesap bulunamadı.']);
        }

        $service = new TrendyolService($account);
        return response()->json($service->getBatchRequestResult($batchId));
    }

    public function checkBuybox($barcode)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['error' => 'Aktif hesap bulunamadı.']);
        }

        $service = new TrendyolService($account);
        return response()->json($service->checkBuybox($barcode));
    }

    public function batchRequests()
    {
        // Son batch request'leri veritabanından çek (henüz kaydetmiyoruz, session veya cache kullanılabilir)
        $batchRequests = session('trendyol_batch_requests', []);
        return view('trendyol::admin.batch-requests.index', compact('batchRequests'));
    }

    public function categoryWizard()
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        $categories = [];
        
        if ($account) {
            $service = new TrendyolService($account);
            $categories = $service->getCategories();
        }

        return view('trendyol::admin.category-wizard.index', compact('categories'));
    }

    public function claims()
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        
        if (!$account) {
            return redirect()->route('admin.marketplace.trendyol.accounts')
                ->with('error', 'Aktif Trendyol hesabı bulunamadı.');
        }

        $service = new TrendyolService($account);
        $claims = $service->getClaims(['size' => 50]);

        return view('trendyol::admin.claims.index', compact('claims'));
    }

    public function approveClaim($claimId)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.']);
        }

        $service = new TrendyolService($account);
        $result = $service->approveClaim((int) $claimId);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'İade onaylandı.' : 'İade onaylanamadı.',
        ]);
    }

    // ===== VARYANT YÖNETİMİ =====

    public function variants()
    {
        return view('trendyol::admin.variants.index');
    }

    public function saveVariantMappings(Request $request)
    {
        $colorMappings = $request->get('color_mappings', []);
        $sizeMappings = $request->get('size_mappings', []);

        // Cache'e kaydet (veritabanına da kaydedilebilir)
        cache(['trendyol_color_mappings' => $colorMappings], now()->addYear());
        cache(['trendyol_size_mappings' => $sizeMappings], now()->addYear());

        return response()->json([
            'success' => true,
            'message' => 'Varyant eşleştirmeleri kaydedildi.',
        ]);
    }

    // ===== KARGO YÖNETİMİ =====

    public function cargo()
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        $orders = [];
        
        if ($account) {
            // Kargo bekleyen siparişleri çek
            $orders = MarketplaceOrder::where('account_id', $account->id)
                ->whereIn('status', ['new', 'processing'])
                ->whereNull('tracking_number')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        return view('trendyol::admin.cargo.index', compact('orders'));
    }

    public function updateBulkTracking(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.']);
        }

        $updates = $request->get('updates', []);
        $service = new TrendyolService($account);
        $successCount = 0;

        foreach ($updates as $update) {
            if (!empty($update['order_id']) && !empty($update['tracking_number'])) {
                $result = $service->sendTrackingNumber(
                    $update['order_id'],
                    $update['tracking_number'],
                    $update['cargo_provider'] ?? ''
                );
                
                if ($result) {
                    // Veritabanını da güncelle
                    MarketplaceOrder::where('id', $update['order_id'])
                        ->update([
                            'tracking_number' => $update['tracking_number'],
                            'cargo_provider' => $update['cargo_provider'] ?? null,
                            'status' => 'shipped',
                        ]);
                    $successCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$successCount} sipariş güncellendi.",
        ]);
    }

    // ===== FİYAT ANALİZİ =====

    public function priceAnalysis()
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        $stats = [
            'total_products' => 0,
            'buybox_winning' => 0,
            'buybox_losing' => 0,
            'active_alarms' => 0,
        ];
        
        if ($account) {
            $listings = \CastMart\Marketplace\Models\MarketplaceListing::where('account_id', $account->id)->count();
            $stats['total_products'] = $listings;
            // Demo veriler
            $stats['buybox_winning'] = (int)($listings * 0.8);
            $stats['buybox_losing'] = $listings - $stats['buybox_winning'];
            $stats['active_alarms'] = 3;
        }

        return view('trendyol::admin.price-analysis.index', compact('stats'));
    }

    public function savePriceRules(Request $request)
    {
        $rules = $request->get('rules', []);
        
        // Cache'e kaydet (veritabanına da kaydedilebilir)
        cache(['trendyol_price_rules' => $rules], now()->addYear());

        return response()->json([
            'success' => true,
            'message' => 'Fiyat kuralları kaydedildi.',
        ]);
    }

    // ===== E-FATURA YÖNETİMİ =====

    public function invoices()
    {
        $bizimhesapCredentials = cache('bizimhesap_credentials');
        $bizimhesapConnected = !empty($bizimhesapCredentials);
        $invoices = [];
        $companyInfo = cache('trendyol_invoice_settings') ?? [];

        if ($bizimhesapConnected) {
            try {
                $service = new \CastMart\Trendyol\Services\BizimHesapService($bizimhesapCredentials);
                $invoices = $service->getInvoices(['limit' => 20]);
            } catch (\Exception $e) {
                // Hata durumunda boş liste
            }
        }

        return view('trendyol::admin.invoices.index', compact('bizimhesapConnected', 'invoices', 'companyInfo'));
    }

    public function saveInvoiceSettings(Request $request)
    {
        $settings = [
            'default_vat_rate' => $request->get('default_vat_rate', 20),
            'invoice_series' => $request->get('invoice_series', 'TRD'),
            'auto_invoice' => $request->boolean('auto_invoice'),
            'use_trendyol_vat' => $request->boolean('use_trendyol_vat'),
            'auto_determine_invoice_type' => $request->boolean('auto_determine_invoice_type'),
            'company_name' => $request->get('company_name'),
            'tax_office' => $request->get('tax_office'),
            'tax_number' => $request->get('tax_number'),
            'address' => $request->get('address'),
        ];

        cache(['trendyol_invoice_settings' => $settings], now()->addYear());

        return response()->json([
            'success' => true,
            'message' => 'Fatura ayarları kaydedildi.',
        ]);
    }

    public function connectBizimhesap(Request $request)
    {
        $credentials = [
            'api_key' => $request->get('api_key'),
            'api_secret' => $request->get('api_secret'),
            'company_id' => $request->get('company_id'),
        ];

        // Bağlantıyı test et
        $service = new \CastMart\Trendyol\Services\BizimHesapService($credentials);
        $result = $service->testConnection();

        if ($result['success']) {
            cache(['bizimhesap_credentials' => $credentials], now()->addYear());
            return response()->json([
                'success' => true,
                'message' => 'BizimHesap bağlantısı başarılı!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Bağlantı başarısız. API bilgilerini kontrol edin.',
        ]);
    }

    public function testBizimhesap()
    {
        $credentials = cache('bizimhesap_credentials');
        if (!$credentials) {
            return response()->json([
                'success' => false,
                'message' => 'BizimHesap bağlantısı bulunamadı.',
            ]);
        }

        $service = new \CastMart\Trendyol\Services\BizimHesapService($credentials);
        $result = $service->testConnection();

        return response()->json($result);
    }

    public function createInvoiceFromOrder(Request $request)
    {
        $orderNumber = $request->get('order_number');
        
        $credentials = cache('bizimhesap_credentials');
        if (!$credentials) {
            return response()->json([
                'success' => false,
                'message' => 'BizimHesap bağlantısı bulunamadı.',
            ]);
        }

        // Trendyol siparişini bul
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        if (!$account) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif Trendyol hesabı bulunamadı.',
            ]);
        }

        $trendyolService = new TrendyolService($account);
        $orders = $trendyolService->getOrders(['orderNumber' => $orderNumber]);
        
        if (empty($orders)) {
            return response()->json([
                'success' => false,
                'message' => 'Sipariş bulunamadı.',
            ]);
        }

        $order = $orders[0];

        // BizimHesap'ta fatura oluştur
        $bizimhesapService = new \CastMart\Trendyol\Services\BizimHesapService($credentials);
        $result = $bizimhesapService->createInvoiceFromTrendyolOrder($order);

        return response()->json($result);
    }

    public function downloadInvoice($invoiceId)
    {
        $credentials = cache('bizimhesap_credentials');
        if (!$credentials) {
            abort(404, 'BizimHesap bağlantısı bulunamadı.');
        }

        $service = new \CastMart\Trendyol\Services\BizimHesapService($credentials);
        $pdf = $service->downloadInvoicePdf($invoiceId);

        if (!$pdf) {
            abort(404, 'Fatura PDF bulunamadı.');
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"fatura-{$invoiceId}.pdf\"",
        ]);
    }

    // ===== KOMİSYON HESAPLAYICI =====

    public function commissionCalculator()
    {
        return view('trendyol::admin.commission-calculator.index');
    }

    public function bulkCommissionCalculator()
    {
        return view('trendyol::admin.commission-calculator.bulk');
    }

    public function variantCalculator()
    {
        return view('trendyol::admin.commission-calculator.variants');
    }

    public function commissionRates()
    {
        $rates = \CastMart\Trendyol\Models\CommissionRate::orderBy('category_name')->get();
        return view('trendyol::admin.commission-calculator.rates', compact('rates'));
    }

    public function saveCommissionRate(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'category_name' => 'required|string|max:255',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'service_fee' => 'nullable|numeric|min:0',
        ]);

        if ($request->id) {
            $rate = \CastMart\Trendyol\Models\CommissionRate::findOrFail($request->id);
            $rate->update($data);
            $message = 'Komisyon oranı güncellendi.';
        } else {
            \CastMart\Trendyol\Models\CommissionRate::create($data);
            $message = 'Yeni komisyon oranı eklendi.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function deleteCommissionRate(Request $request)
    {
        $rate = \CastMart\Trendyol\Models\CommissionRate::findOrFail($request->id);
        $rate->delete();

        return response()->json(['success' => true, 'message' => 'Komisyon oranı silindi.']);
    }

    // ===== INTELLIGENCE - 7. HIS =====

    public function intelligence()
    {
        // Gerçek verileri çek
        $data = $this->getIntelligenceData();
        return view('trendyol::admin.intelligence.index', $data);
    }

    protected function getIntelligenceData(): array
    {
        // BuyBox istatistikleri
        $buyboxStats = \CastMart\Trendyol\Models\BuyboxTracking::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "won" THEN 1 ELSE 0 END) as won,
            SUM(CASE WHEN status = "lost" THEN 1 ELSE 0 END) as lost,
            SUM(CASE WHEN status = "risk" THEN 1 ELSE 0 END) as risk
        ')->first();

        $buyboxRate = $buyboxStats && $buyboxStats->total > 0 
            ? round(($buyboxStats->won / $buyboxStats->total) * 100) 
            : 0;

        // Uyarı sayıları - gerçek veritabanından
        $alertCounts = \CastMart\Trendyol\Models\IntelligenceAlert::selectRaw('
            type,
            COUNT(*) as count
        ')->where('is_dismissed', false)->groupBy('type')->pluck('count', 'type')->toArray();

        // Son uyarılar - gerçek veritabanından
        $recentAlerts = \CastMart\Trendyol\Models\IntelligenceAlert::where('is_dismissed', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $alerts = $recentAlerts->map(function($alert) {
            return [
                'id' => $alert->id,
                'type' => $alert->type,
                'category' => $alert->category,
                'title' => $alert->title,
                'description' => $alert->description,
                'product_sku' => $alert->product_sku,
                'action_type' => $alert->action_type,
                'created_at' => $alert->created_at,
                'time_ago' => $alert->created_at->diffForHumans(),
            ];
        })->toArray();

        // Haftalık satış istatistikleri - gerçek veritabanından
        $weeklyStats = $this->getWeeklyStats();

        // En çok satanlar - gerçek veritabanından  
        $topProducts = $this->getTopProducts();

        // Stok sağlığı - düşük stoklu ürünler
        try {
            $totalProducts = MarketplaceListing::whereHas('account', function($q) {
                $q->where('marketplace', 'trendyol');
            })->count();
            
            $lowStockProducts = MarketplaceListing::whereHas('account', function($q) {
                $q->where('marketplace', 'trendyol');
            })->whereHas('product', function($q) {
                $q->whereHas('inventories', function($q2) {
                    $q2->where('qty', '<=', 5);
                });
            })->count();
            
            $stockHealth = $totalProducts > 0 ? round((($totalProducts - $lowStockProducts) / $totalProducts) * 100) : 100;
        } catch (\Exception $e) {
            $stockHealth = 100;
        }

        // Ortalama kar marjı hesapla
        $profitMargin = $this->calculateAverageProfitMargin();

        // Müşteri puanı - gerçek yorumlardan (şimdilik varsayılan)
        $customerRating = 4.5;

        // Kargo performansı - zamanında gönderilen siparişler
        $cargoPerformance = $this->calculateCargoPerformance();

        // Sağlık skoru hesapla
        $healthScore = $this->calculateHealthScore($buyboxRate, $alertCounts, $stockHealth, $profitMargin, $cargoPerformance);

        return [
            'healthScore' => $healthScore,
            'buyboxRate' => $buyboxRate,
            'buyboxStats' => $buyboxStats ?? (object)['total' => 0, 'won' => 0, 'lost' => 0, 'risk' => 0],
            'alertCounts' => [
                'critical' => $alertCounts['critical'] ?? 0,
                'warning' => $alertCounts['warning'] ?? 0,
                'opportunity' => $alertCounts['opportunity'] ?? 0,
                'trend' => $alertCounts['trend'] ?? 0,
            ],
            'alerts' => $alerts,
            'profitMargin' => $profitMargin,
            'stockHealth' => $stockHealth,
            'customerRating' => $customerRating,
            'cargoPerformance' => $cargoPerformance,
            'lastUpdated' => now()->format('H:i'),
            'weeklyStats' => $weeklyStats,
            'topProducts' => $topProducts,
            'criticalStockCount' => $lowStockProducts,
            'lowMarginCount' => 0,
            'buyboxLostCount' => $buyboxStats->lost ?? 0,
        ];
    }

    protected function getWeeklyStats(): array
    {
        // Son 7 gün sipariş verileri
        $startDate = now()->subDays(7);
        $previousStartDate = now()->subDays(14);
        
        // Trendyol hesaplarına ait siparişler
        $trendyolFilter = function($query) {
            $query->whereHas('account', function($q) {
                $q->where('marketplace', 'trendyol');
            });
        };
        
        $currentWeekOrders = MarketplaceOrder::where('created_at', '>=', $startDate)
            ->whereHas('account', function($q) {
                $q->where('marketplace', 'trendyol');
            })
            ->get();
            
        $previousWeekOrders = MarketplaceOrder::where('created_at', '>=', $previousStartDate)
            ->where('created_at', '<', $startDate)
            ->whereHas('account', function($q) {
                $q->where('marketplace', 'trendyol');
            })
            ->get();
        
        $totalSales = $currentWeekOrders->count();
        $previousWeekSales = $previousWeekOrders->count();
        
        // total_amount yoksa order_data'dan hesapla
        $totalRevenue = $currentWeekOrders->sum(function($order) {
            if (isset($order->order_data['totalPrice'])) {
                return $order->order_data['totalPrice'];
            }
            return 0;
        });
        
        // Son 7 gün günlük satışlar
        $dailySales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailySales[] = MarketplaceOrder::whereDate('created_at', $date)
                ->whereHas('account', function($q) {
                    $q->where('marketplace', 'trendyol');
                })
                ->count();
        }
        
        // İade sayısı
        $returns = MarketplaceOrder::where('created_at', '>=', $startDate)
            ->where('status', 'returned')
            ->whereHas('account', function($q) {
                $q->where('marketplace', 'trendyol');
            })
            ->count();
        
        // Değişim yüzdesi
        $salesChange = $previousWeekSales > 0 
            ? round((($totalSales - $previousWeekSales) / $previousWeekSales) * 100, 1)
            : 0;
        
        return [
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'netProfit' => round($totalRevenue * 0.18, 2), // Ortalama kar marjı %18
            'returns' => $returns,
            'previousWeekSales' => $previousWeekSales,
            'salesChange' => $salesChange,
            'dailySales' => $dailySales,
        ];
    }

    protected function getTopProducts(): array
    {
        // Son 30 gün en çok satan ürünler - basitleştirilmiş
        try {
            $orders = MarketplaceOrder::where('created_at', '>=', now()->subDays(30))
                ->whereHas('account', function($q) {
                    $q->where('marketplace', 'trendyol');
                })
                ->whereNotNull('items_data')
                ->get();
            
            $productCounts = [];
            foreach ($orders as $order) {
                $items = $order->items_data ?? [];
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $name = $item['productTitle'] ?? $item['productName'] ?? 'Bilinmeyen Ürün';
                        if (!isset($productCounts[$name])) {
                            $productCounts[$name] = 0;
                        }
                        $productCounts[$name]++;
                    }
                }
            }
            
            arsort($productCounts);
            $topProducts = [];
            $count = 0;
            foreach ($productCounts as $name => $quantity) {
                if ($count >= 5) break;
                $topProducts[] = [
                    'name' => $name,
                    'sku' => '-',
                    'quantity' => $quantity,
                    'profit' => round($quantity * 50, 2),
                ];
                $count++;
            }
            
            return $topProducts;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function calculateAverageProfitMargin(): float
    {
        // Komisyon oranlarından ortalama hesapla
        $avgCommission = \CastMart\Trendyol\Models\CommissionRate::avg('commission_rate');
        
        if ($avgCommission) {
            return round(100 - $avgCommission - 5, 1); // Komisyon + kargo maliyeti çıkarılır
        }
        
        return 15.0; // Varsayılan kar marjı
    }

    protected function calculateCargoPerformance(): int
    {
        // Son 30 günde zamanında gönderilen siparişler
        try {
            $totalOrders = MarketplaceOrder::where('created_at', '>=', now()->subDays(30))
                ->whereHas('account', function($q) {
                    $q->where('marketplace', 'trendyol');
                })
                ->whereIn('status', ['shipped', 'delivered'])
                ->count();
                
            if ($totalOrders == 0) {
                return 100;
            }
            
            $onTimeOrders = MarketplaceOrder::where('created_at', '>=', now()->subDays(30))
                ->whereHas('account', function($q) {
                    $q->where('marketplace', 'trendyol');
                })
                ->whereIn('status', ['shipped', 'delivered'])
                ->whereNotNull('shipped_at')
                ->whereRaw('shipped_at <= DATE_ADD(created_at, INTERVAL 48 HOUR)')
                ->count();
            
            return round(($onTimeOrders / $totalOrders) * 100);
        } catch (\Exception $e) {
            return 100;
        }
    }


    protected function calculateHealthScore(float $buyboxRate, array $alertCounts, int $stockHealth = 100, float $profitMargin = 15, int $cargoPerformance = 100): int
    {
        $score = 0;
        
        // BuyBox oranı (%30 ağırlık)
        $score += ($buyboxRate / 100) * 30;
        
        // Kar marjı (%20 ağırlık) - %15+ = %100 sağlıklı
        $profitScore = min(1, $profitMargin / 15);
        $score += $profitScore * 20;
        
        // Stok sağlığı (%20 ağırlık)
        $score += ($stockHealth / 100) * 20;
        
        // Müşteri puanı (%15 ağırlık) - varsayılan 4.5/5 = %90
        $score += 0.90 * 15;
        
        // Kargo performansı (%15 ağırlık)
        $score += ($cargoPerformance / 100) * 15;
        
        // Kritik uyarı cezası
        $criticalPenalty = ($alertCounts['critical'] ?? 0) * 3;
        $score = max(0, $score - $criticalPenalty);
        
        return min(100, round($score));
    }

    public function getAlerts(Request $request)
    {
        // Gerçek veritabanından uyarıları çek
        $type = $request->input('type');
        
        $query = \CastMart\Trendyol\Models\IntelligenceAlert::where('is_dismissed', false)
            ->orderBy('created_at', 'desc');
        
        if ($type && $type !== 'all') {
            $query->where('type', $type);
        }
        
        $alerts = $query->limit(50)->get()->map(function($alert) {
            return [
                'id' => $alert->id,
                'type' => $alert->type,
                'category' => $alert->category,
                'title' => $alert->title,
                'description' => $alert->description,
                'product_sku' => $alert->product_sku,
                'action' => $alert->action_type,
                'created_at' => $alert->created_at,
                'time_ago' => $alert->created_at->diffForHumans(),
            ];
        });

        return response()->json(['success' => true, 'alerts' => $alerts]);
    }

    public function dismissAlert(Request $request)
    {
        $alertId = $request->input('alert_id');
        
        $alert = \CastMart\Trendyol\Models\IntelligenceAlert::find($alertId);
        
        if ($alert) {
            $alert->update(['is_dismissed' => true]);
            return response()->json(['success' => true, 'message' => 'Uyarı kapatıldı']);
        }
        
        return response()->json(['success' => false, 'message' => 'Uyarı bulunamadı']);
    }

    // ===== BUYBOX TRACKER =====

    public function buyboxTracker()
    {
        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();
        
        $selectedAccountId = request('account_id', $accounts->first()?->id);
        
        $buyboxData = \CastMart\Trendyol\Models\BuyboxTracking::when($selectedAccountId, function($q) use ($selectedAccountId) {
            $q->where('marketplace_account_id', $selectedAccountId);
        })
        ->orderBy('status')
        ->orderBy('last_checked_at', 'desc')
        ->get();
        
        // İstatistikler
        $stats = [
            'total' => $buyboxData->count(),
            'won' => $buyboxData->where('status', 'won')->count(),
            'lost' => $buyboxData->where('status', 'lost')->count(),
            'risk' => $buyboxData->where('status', 'risk')->count(),
        ];
        $stats['win_rate'] = $stats['total'] > 0 ? round(($stats['won'] / $stats['total']) * 100) : 0;
        
        return view('trendyol::admin.intelligence.buybox-tracker', compact('accounts', 'buyboxData', 'stats', 'selectedAccountId'));
    }

    public function updateBuyboxPrice(Request $request)
    {
        $request->validate([
            'sku' => 'required|string',
            'new_price' => 'required|numeric|min:0.01',
            'account_id' => 'required|integer',
        ]);
        
        $account = MarketplaceAccount::findOrFail($request->account_id);
        $service = new TrendyolService($account);
        
        // BuyBox kaydını bul
        $buybox = \CastMart\Trendyol\Models\BuyboxTracking::where('product_sku', $request->sku)
            ->where('marketplace_account_id', $account->id)
            ->first();
        
        if (!$buybox || !$buybox->barcode) {
            return response()->json(['success' => false, 'message' => 'Ürün barkodu bulunamadı']);
        }
        
        try {
            $result = $service->updateInventory([
                [
                    'barcode' => $buybox->barcode,
                    'salePrice' => $request->new_price,
                    'listPrice' => $request->new_price,
                ]
            ]);
            
            // BuyBox kaydını güncelle
            $buybox->update([
                'our_price' => $request->new_price,
                'status' => $request->new_price <= ($buybox->competitor_price ?? 0) ? 'won' : 'lost',
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => "Fiyat güncellendi: {$request->sku} → {$request->new_price} ₺"
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }

    public function getBuyboxData(Request $request)
    {
        $accountId = $request->input('account_id');
        
        $buyboxData = \CastMart\Trendyol\Models\BuyboxTracking::when($accountId, function($q) use ($accountId) {
            $q->where('marketplace_account_id', $accountId);
        })
        ->orderBy('status')
        ->orderBy('last_checked_at', 'desc')
        ->get()
        ->map(function($item) {
            return [
                'id' => $item->id,
                'sku' => $item->product_sku,
                'barcode' => $item->barcode,
                'our_price' => $item->our_price,
                'competitor_price' => $item->competitor_price,
                'competitor_seller' => $item->competitor_seller,
                'status' => $item->status,
                'status_label' => $item->getStatusLabel(),
                'win_chance' => $item->win_chance,
                'price_diff' => $item->getPriceDifference(),
                'price_diff_percent' => round($item->getPriceDifferencePercent(), 1),
                'last_checked' => $item->last_checked_at?->diffForHumans(),
            ];
        });
        
        return response()->json(['success' => true, 'data' => $buyboxData]);
    }

    // ===== PRICE RULES =====

    public function priceRules()
    {
        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();
        
        $selectedAccountId = request('account_id', $accounts->first()?->id);
        
        $rules = \CastMart\Trendyol\Models\PriceRule::when($selectedAccountId, function($q) use ($selectedAccountId) {
            $q->where('marketplace_account_id', $selectedAccountId);
        })
        ->orderBy('priority', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
        
        return view('trendyol::admin.intelligence.price-rules', compact('accounts', 'rules', 'selectedAccountId'));
    }

    public function savePriceRule(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer',
            'account_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'trigger' => 'required|string',
            'action' => 'required|string',
            'action_value' => 'nullable|numeric',
            'scope' => 'nullable|string',
            'scope_data' => 'nullable|array',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'priority' => 'nullable|integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);
        
        if ($request->id) {
            $rule = \CastMart\Trendyol\Models\PriceRule::findOrFail($request->id);
            $rule->update([
                'name' => $data['name'],
                'trigger' => $data['trigger'],
                'action' => $data['action'],
                'action_value' => $data['action_value'] ?? 0,
                'scope' => $data['scope'] ?? 'all',
                'scope_data' => $data['scope_data'] ?? null,
                'priority' => $data['priority'] ?? 50,
                'is_active' => $data['is_active'] ?? true,
            ]);
            $message = 'Kural güncellendi';
        } else {
            \CastMart\Trendyol\Models\PriceRule::create([
                'marketplace_account_id' => $data['account_id'],
                'name' => $data['name'],
                'trigger' => $data['trigger'],
                'action' => $data['action'],
                'action_value' => $data['action_value'] ?? 0,
                'scope' => $data['scope'] ?? 'all',
                'scope_data' => $data['scope_data'] ?? null,
                'priority' => $data['priority'] ?? 50,
                'is_active' => $data['is_active'] ?? true,
            ]);
            $message = 'Kural oluşturuldu';
        }
        
        return response()->json(['success' => true, 'message' => $message]);
    }

    public function deletePriceRule(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        
        $rule = \CastMart\Trendyol\Models\PriceRule::findOrFail($request->id);
        $rule->delete();
        
        return response()->json(['success' => true, 'message' => 'Kural silindi']);
    }

    public function togglePriceRule(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        
        $rule = \CastMart\Trendyol\Models\PriceRule::findOrFail($request->id);
        $rule->update(['is_active' => !$rule->is_active]);
        
        return response()->json([
            'success' => true, 
            'message' => $rule->is_active ? 'Kural aktifleştirildi' : 'Kural devre dışı bırakıldı',
            'is_active' => $rule->is_active
        ]);
    }

    // ===== PREDICTIONS =====

    public function predictions()
    {
        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();
        
        // Basit tahminler oluştur
        $predictions = $this->generatePredictions();
        
        return view('trendyol::admin.intelligence.predictions', compact('accounts', 'predictions'));
    }

    protected function generatePredictions(): array
    {
        // Son 30 gün satış verileri analizi
        $totalSales = MarketplaceOrder::whereHas('account', function($q) {
            $q->where('marketplace', 'trendyol');
        })->where('created_at', '>=', now()->subDays(30))->count();
        
        $avgDailySales = $totalSales / 30;
        
        return [
            'next_week_sales' => round($avgDailySales * 7),
            'next_month_sales' => round($avgDailySales * 30),
            'trend' => $avgDailySales > 0 ? 'up' : 'stable',
            'confidence' => min(85, max(50, $totalSales * 2)),
            'recommendations' => [
                [
                    'type' => 'price',
                    'title' => 'Fiyat Optimizasyonu',
                    'description' => 'BuyBox kaybedilen ürünlerde fiyat düşürme önerilir.',
                ],
                [
                    'type' => 'stock',
                    'title' => 'Stok Yönetimi', 
                    'description' => 'Kritik stoklu ürünler için tedarik planı oluşturun.',
                ],
                [
                    'type' => 'performance',
                    'title' => 'Kargo Performansı',
                    'description' => 'Zamanında gönderim oranını artırın.',
                ],
            ],
        ];
    }

    // ===== NOTIFICATIONS =====

    public function notifications()
    {
        $accounts = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->get();
        
        $notifications = \CastMart\Trendyol\Models\IntelligenceAlert::whereIn('marketplace_account_id', $accounts->pluck('id'))
            ->where('is_dismissed', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $unreadCount = \CastMart\Trendyol\Models\IntelligenceAlert::whereIn('marketplace_account_id', $accounts->pluck('id'))
            ->where('is_dismissed', false)
            ->where('is_read', false)
            ->count();
        
        return view('trendyol::admin.intelligence.notifications', compact('accounts', 'notifications', 'unreadCount'));
    }

    public function markNotificationRead(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        
        $alert = \CastMart\Trendyol\Models\IntelligenceAlert::findOrFail($request->id);
        $alert->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $accountIds = MarketplaceAccount::where('marketplace', 'trendyol')
            ->where('is_active', true)
            ->pluck('id');
        
        \CastMart\Trendyol\Models\IntelligenceAlert::whereIn('marketplace_account_id', $accountIds)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        
        return response()->json(['success' => true, 'message' => 'Tüm bildirimler okundu olarak işaretlendi']);
    }

    // ===== FİYAT KURALLARI (PRICE RULES) API =====

    /**
     * Fiyat kuralları listesini getir (API)
     */
    public function getPriceRules(Request $request)
    {
        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.']);
        }

        $rules = \CastMart\Trendyol\Models\PriceRule::where('marketplace_account_id', $account->id)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'trigger' => $rule->trigger,
                    'trigger_label' => $rule->getTriggerLabel(),
                    'action' => $rule->action,
                    'action_label' => $rule->getActionLabel(),
                    'action_value' => $rule->action_value,
                    'scope' => $rule->scope,
                    'scope_data' => $rule->scope_data,
                    'sku_filter' => $rule->sku_filter,
                    'min_price' => $rule->min_price,
                    'max_price' => $rule->max_price,
                    'is_active' => $rule->is_active,
                    'priority' => $rule->priority,
                    'trigger_count' => $rule->trigger_count,
                    'last_triggered_at' => $rule->last_triggered_at?->format('d.m.Y H:i'),
                    'created_at' => $rule->created_at->format('d.m.Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    /**
     * Yeni fiyat kuralı oluştur
     */
    public function storePriceRule(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger' => 'required|in:competitor_cheaper,buybox_lost,stock_low,competitor_stock_zero,time_based',
            'action' => 'required|in:match_minus,decrease_percent,increase_percent,set_price',
            'action_value' => 'nullable|numeric|min:0',
            'scope' => 'nullable|in:all,category,sku',
            'sku_filter' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'priority' => 'nullable|integer|min:1|max:100',
        ]);

        $account = MarketplaceAccount::marketplace('trendyol')->active()->first();
        
        if (!$account) {
            return response()->json(['success' => false, 'message' => 'Aktif hesap bulunamadı.'], 400);
        }

        $rule = \CastMart\Trendyol\Models\PriceRule::create([
            'marketplace_account_id' => $account->id,
            'name' => $request->name,
            'trigger' => $request->trigger,
            'action' => $request->action,
            'action_value' => $request->action_value ?? 0,
            'scope' => $request->scope ?? 'all',
            'scope_data' => $request->scope_data,
            'sku_filter' => $request->sku_filter,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'priority' => $request->priority ?? 50,
            'is_active' => true,
            'trigger_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fiyat kuralı başarıyla oluşturuldu.',
            'data' => $rule,
        ]);
    }

    // ===== EXCEL IMPORT/EXPORT =====

    /**
     * Excel export sayfası
     */
    public function excelExportPage()
    {
        $accounts = MarketplaceAccount::marketplace('trendyol')->active()->get();
        return view('trendyol::admin.excel.export', compact('accounts'));
    }

    /**
     * Ürünleri export et
     */
    public function exportProducts(Request $request)
    {
        $account = MarketplaceAccount::find($request->account_id);
        
        if (!$account) {
            return back()->with('error', 'Hesap bulunamadı.');
        }

        $exporter = new \CastMart\Trendyol\Services\ExcelExportService($account);
        $path = $exporter->exportProducts($request->only(['status', 'category_id']));

        return response()->download(storage_path('app/' . $path), basename($path), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend();
    }

    /**
     * Siparişleri export et
     */
    public function exportOrders(Request $request)
    {
        $account = MarketplaceAccount::find($request->account_id);
        
        if (!$account) {
            return back()->with('error', 'Hesap bulunamadı.');
        }

        $exporter = new \CastMart\Trendyol\Services\ExcelExportService($account);
        $path = $exporter->exportOrders($request->only(['status', 'from', 'to']));

        return response()->download(storage_path('app/' . $path), basename($path), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend();
    }

    /**
     * Fiyat güncelleme şablonu indir
     */
    public function exportPriceTemplate(Request $request)
    {
        $account = MarketplaceAccount::find($request->account_id);
        
        if (!$account) {
            return back()->with('error', 'Hesap bulunamadı.');
        }

        $exporter = new \CastMart\Trendyol\Services\ExcelExportService($account);
        $path = $exporter->exportPriceTemplate();

        return response()->download(storage_path('app/' . $path), basename($path), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend();
    }

    /**
     * Komisyon raporu export et
     */
    public function exportCommissionReport(Request $request)
    {
        $account = MarketplaceAccount::find($request->account_id);
        
        if (!$account) {
            return back()->with('error', 'Hesap bulunamadı.');
        }

        $exporter = new \CastMart\Trendyol\Services\ExcelExportService($account);
        $path = $exporter->exportCommissionReport($request->only(['from', 'to']));

        return response()->download(storage_path('app/' . $path), basename($path), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ])->deleteFileAfterSend();
    }

    /**
     * Excel import sayfası
     */
    public function excelImportPage()
    {
        $accounts = MarketplaceAccount::marketplace('trendyol')->active()->get();
        return view('trendyol::admin.excel.import', compact('accounts'));
    }

    /**
     * Fiyat/stok güncelleme import et
     */
    public function importPriceUpdate(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:marketplace_accounts,id',
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $account = MarketplaceAccount::find($request->account_id);
        $importer = new \CastMart\Trendyol\Services\ExcelImportService($account);
        $result = $importer->importPriceUpdate($request->file('file'));

        if ($result['success']) {
            return back()->with('success', $result['message'])
                         ->with('warnings', $result['warnings']);
        }

        return back()->with('error', $result['message'])
                     ->with('errors_list', $result['errors']);
    }

    /**
     * Toplu ürün import et
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:marketplace_accounts,id',
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $account = MarketplaceAccount::find($request->account_id);
        $importer = new \CastMart\Trendyol\Services\ExcelImportService($account);
        $result = $importer->importProducts($request->file('file'));

        if ($result['success']) {
            return back()->with('success', $result['message'])
                         ->with('warnings', $result['warnings']);
        }

        return back()->with('error', $result['message'])
                     ->with('errors_list', $result['errors']);
    }
}

