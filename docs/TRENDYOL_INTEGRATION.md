# 🛍️ Trendyol Entegrasyonu Dokümantasyonu

Bu döküman, CastMart'ın Trendyol Marketplace entegrasyonunu detaylı olarak açıklar.

---

## 📋 İçindekiler

1. [Genel Bakış](#genel-bakış)
2. [Kurulum](#kurulum)
3. [API Yapılandırması](#api-yapılandırması)
4. [Ürün Yönetimi](#ürün-yönetimi)
5. [Sipariş Yönetimi](#sipariş-yönetimi)
6. [Fiyat Yönetimi](#fiyat-yönetimi)
7. [Buybox Takibi](#buybox-takibi)
8. [Akıllı Fiyat Kuralları](#akıllı-fiyat-kuralları)
9. [Raporlama](#raporlama)
10. [Zamanlanmış Görevler](#zamanlanmış-görevler)

---

## 🎯 Genel Bakış

CastMart Trendyol entegrasyonu şu özellikleri sağlar:

- ✅ Ürün listeleme ve güncelleme
- ✅ Stok ve fiyat senkronizasyonu
- ✅ Sipariş çekme ve durum güncelleme
- ✅ Müşteri soruları yanıtlama
- ✅ Buybox takibi ve analizi
- ✅ Akıllı fiyat kuralları
- ✅ Komisyon hesaplama
- ✅ Performans raporları

---

## 🚀 Kurulum

### Modül Dosyaları

```
packages/CastMart/Trendyol/
├── src/
│   ├── Config/
│   ├── Console/Commands/      # Artisan komutları
│   ├── Http/Controllers/      # Controller'lar
│   ├── Models/               # Eloquent modelleri
│   ├── Providers/            # Service provider
│   └── Services/             # İş mantığı servisleri
├── Resources/views/          # Blade view'lar
├── routes/admin.php          # Route tanımları
└── database/migrations/      # Veritabanı migration'ları
```

### Migration Çalıştırma

```bash
php artisan migrate
```

---

## ⚙️ API Yapılandırması

### Trendyol Seller API Bilgileri

Trendyol Seller Center'dan API bilgilerinizi alın:
- Satıcı ID (Supplier ID)
- API Key
- API Secret

### ENV Yapılandırması

```env
TRENDYOL_API_URL=https://api.trendyol.com/sapigw
TRENDYOL_SUPPLIER_ID=123456
TRENDYOL_API_KEY=your_api_key
TRENDYOL_API_SECRET=your_api_secret
```

### Config Dosyası

`config/trendyol.php`:

```php
return [
    'api_url' => env('TRENDYOL_API_URL'),
    'supplier_id' => env('TRENDYOL_SUPPLIER_ID'),
    'api_key' => env('TRENDYOL_API_KEY'),
    'api_secret' => env('TRENDYOL_API_SECRET'),
    
    'sync_interval' => 15, // dakika
    'batch_size' => 100,
];
```

---

## 📦 Ürün Yönetimi

### Ürün Gönderme

```php
use CastMart\Trendyol\Services\TrendyolService;

$service = new TrendyolService($account);

// Tek ürün gönder
$result = $service->createProduct([
    'barcode' => '1234567890123',
    'title' => 'Örnek Ürün',
    'productMainId' => 'SKU-001',
    'brandId' => 1234,
    'categoryId' => 5678,
    'quantity' => 100,
    'stockCode' => 'STK-001',
    'listPrice' => 199.90,
    'salePrice' => 149.90,
    'vatRate' => 18,
    'cargoCompanyId' => 10,
    'images' => [
        ['url' => 'https://cdn.example.com/image1.jpg'],
    ],
    'attributes' => [
        ['attributeId' => 338, 'attributeValueId' => 1234], // Renk
        ['attributeId' => 339, 'customAttributeValue' => 'XL'], // Beden
    ],
]);
```

### Stok Güncelleme

```php
// Toplu stok güncelleme
$service->updateInventory([
    ['barcode' => '1234567890123', 'quantity' => 50],
    ['barcode' => '1234567890124', 'quantity' => 30],
]);
```

### Fiyat Güncelleme

```php
// Toplu fiyat güncelleme
$service->updatePrices([
    [
        'barcode' => '1234567890123',
        'listPrice' => 199.90,
        'salePrice' => 149.90,
    ],
]);
```

---

## 🛒 Sipariş Yönetimi

### Siparişleri Çekme

```php
// Son siparişleri çek
$orders = $service->getOrders([
    'status' => 'Created',
    'startDate' => now()->subDays(7)->timestamp * 1000,
    'endDate' => now()->timestamp * 1000,
]);

// Veritabanına kaydet
$service->syncOrders();
```

### Sipariş Durumu Güncelleme

```php
// Siparişi kargoya ver
$service->updateShipment($packageId, [
    'trackingNumber' => 'TR123456789',
    'cargoProviderCode' => 'ARASKARGOMARKET',
]);

// Siparişi iptal et
$service->cancelOrder($packageId, $lineId, $reason);
```

### Sipariş Durumları

| Durum | Açıklama |
|-------|----------|
| Created | Yeni sipariş |
| Picking | Hazırlanıyor |
| Shipped | Kargoya verildi |
| Delivered | Teslim edildi |
| Cancelled | İptal edildi |
| Returned | İade edildi |

---

## 💰 Fiyat Yönetimi

### Komisyon Hesaplama

```php
use CastMart\Trendyol\Services\CommissionCalculator;

$calculator = new CommissionCalculator();

// Kategori bazlı komisyon
$commission = $calculator->calculate($categoryId, $price);

// Tüm maliyetler dahil
$breakdown = $calculator->getBreakdown([
    'sale_price' => 149.90,
    'category_id' => 5678,
    'cargo_cost' => 15.00,
    'purchase_cost' => 80.00,
]);

// Sonuç:
// [
//     'sale_price' => 149.90,
//     'commission_rate' => 12.5,
//     'commission' => 18.74,
//     'cargo_cost' => 15.00,
//     'net_income' => 116.16,
//     'profit' => 36.16,
//     'profit_margin' => 24.12,
// ]
```

---

## 🎯 Buybox Takibi

### Buybox Kontrolü

```php
use CastMart\Trendyol\Services\TrendyolScraperService;

$scraper = new TrendyolScraperService();

// Tek ürün kontrolü
$result = $scraper->checkBuybox($productUrl);

// Toplu kontrol (artisan komutu)
php artisan trendyol:check-buybox
```

### Buybox Tracking Modeli

```php
use CastMart\Trendyol\Models\BuyboxTracking;

// Son buybox durumunu al
$tracking = BuyboxTracking::where('barcode', '1234567890123')
    ->latest()
    ->first();

// Buybox geçmişi
$history = BuyboxTracking::where('barcode', '1234567890123')
    ->where('created_at', '>=', now()->subDays(7))
    ->get();
```

---

## 🤖 Akıllı Fiyat Kuralları

### Kural Oluşturma

```php
use CastMart\Trendyol\Models\PriceRule;

$rule = PriceRule::create([
    'marketplace_account_id' => $accountId,
    'name' => 'Rakibe Otomatik Uyum',
    'trigger' => 'competitor_cheaper',
    'action' => 'match_minus',
    'action_value' => 1.00, // 1 TL altına
    'scope' => 'all',
    'min_price' => 50.00, // Minimum fiyat
    'max_price' => null,
    'priority' => 10,
    'is_active' => true,
]);
```

### Tetikleyiciler

| Tetikleyici | Açıklama |
|-------------|----------|
| `competitor_cheaper` | Rakip daha ucuz |
| `buybox_lost` | Buybox kaybedildi |
| `stock_low` | Stok düşük |
| `competitor_stock_zero` | Rakip stoksuz |
| `time_based` | Belirli saatlerde |

### Aksiyonlar

| Aksiyon | Açıklama |
|---------|----------|
| `match_minus` | Rakibe eşitle - X TL |
| `decrease_percent` | Yüzde düşür |
| `increase_percent` | Yüzde artır |
| `set_price` | Sabit fiyat |

---

## 📊 Raporlama

### Günlük Rapor Komutu

```bash
php artisan trendyol:daily-report
```

### Chart.js Verileri

```php
use CastMart\Trendyol\Services\ChartDataService;

$chartService = new ChartDataService($account);

// Tüm grafik verileri
$data = $chartService->getAllDashboardData();

// Belirli grafik
$salesData = $chartService->getSalesChartData(30); // Son 30 gün
$buyboxData = $chartService->getBuyboxChartData(14); // Son 14 gün
```

### API Endpoints

| Endpoint | Açıklama |
|----------|----------|
| `GET /api/charts/sales` | Satış grafiği |
| `GET /api/charts/categories` | Kategori dağılımı |
| `GET /api/charts/buybox` | Buybox performansı |
| `GET /api/charts/stock` | Stok durumu |
| `GET /api/charts/commission` | Komisyon analizi |

---

## ⏰ Zamanlanmış Görevler

### Scheduler Ayarları

`app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Sipariş senkronizasyonu (her 15 dk)
    $schedule->command('trendyol:sync-orders')
        ->everyFifteenMinutes();
    
    // Buybox kontrolü (her saat)
    $schedule->command('trendyol:check-buybox')
        ->hourly();
    
    // Fiyat kurallarını uygula (her 30 dk)
    $schedule->command('trendyol:apply-price-rules')
        ->everyThirtyMinutes();
    
    // Günlük rapor (her gün 08:00)
    $schedule->command('trendyol:daily-report')
        ->dailyAt('08:00');
    
    // Müşteri soruları senkronizasyonu
    $schedule->command('trendyol:sync-questions')
        ->everyFifteenMinutes();
}
```

### Manuel Çalıştırma

```bash
# Siparişleri senkronize et
php artisan trendyol:sync-orders

# Buybox kontrol
php artisan trendyol:check-buybox

# Fiyat kurallarını uygula
php artisan trendyol:apply-price-rules

# Tüm scheduler görevleri
php artisan schedule:run
```

---

## 🔒 Güvenlik Notları

1. **API Bilgileri**: Asla commit etmeyin, .env kullanın
2. **Rate Limiting**: Trendyol API limitlerine uyun
3. **Logging**: Tüm API çağrılarını loglamak backend'de yapılmaktadır
4. **Hata Yönetimi**: Başarısız işlemler otomatik retry edilir

---

## 🐛 Sorun Giderme

### API Bağlantı Hatası

```bash
# Bağlantıyı test et
php artisan trendyol:test-connection
```

### Sipariş Senkronizasyon Sorunu

1. API kimlik bilgilerini kontrol edin
2. Scheduler'ın çalıştığından emin olun: `php artisan schedule:work`
3. Queue worker'ın aktif olduğunu kontrol edin
4. Log'ları inceleyin: `storage/logs/laravel.log`

### Ürün Gönderim Hatası

- Kategori ID'nin doğru olduğundan emin olun
- Zorunlu attribute'ların doldurulduğunu kontrol edin
- Görsel URL'lerinin erişilebilir olduğunu doğrulayın
