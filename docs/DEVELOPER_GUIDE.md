# 🛠️ CastMart Geliştirici Dokümantasyonu

Bu döküman, CastMart projesinde geliştirme yapmak isteyen geliştiriciler için hazırlanmıştır.

## 📋 İçindekiler

1. [Kurulum](#kurulum)
2. [Proje Yapısı](#proje-yapısı)
3. [Modüler Mimari](#modüler-mimari)
4. [Kodlama Standartları](#kodlama-standartları)
5. [Veritabanı](#veritabanı)
6. [API Geliştirme](#api-geliştirme)
7. [Test Yazımı](#test-yazımı)
8. [Git İş Akışı](#git-iş-akışı)

---

## 🚀 Kurulum

### Gereksinimler

- PHP 8.2+
- MySQL 8.0+ veya PostgreSQL 15+
- Redis 7+
- Node.js 18+
- Composer 2.x

### Ortam Kurulumu

```bash
# Repo'yu klonla
git clone https://github.com/basyilmaz/castmart.git
cd castmart

# Dependencies
composer install
npm install

# Ortam dosyası
cp .env.example .env
php artisan key:generate

# Veritabanı
php artisan migrate
php artisan db:seed

# Storage link
php artisan storage:link

# Frontend build
npm run dev
```

### Docker ile Kurulum

```bash
docker-compose up -d
docker-compose exec app php artisan migrate
```

---

## 📁 Proje Yapısı

```
castmart/
├── app/
│   ├── Console/Commands/     # Artisan komutları
│   ├── Http/
│   │   ├── Controllers/      # App controller'ları
│   │   ├── Middleware/       # Custom middleware'ler
│   │   └── Requests/         # Form request'ler
│   ├── Jobs/                 # Queue job'ları
│   ├── Models/               # Eloquent modelleri
│   ├── Providers/            # Service provider'lar
│   ├── Services/             # Business logic servisleri
│   └── View/Components/      # Blade component'ler
├── config/                   # Yapılandırma dosyaları
├── database/
│   ├── migrations/           # Veritabanı migration'ları
│   ├── seeders/              # Seeder'lar
│   └── factories/            # Model factory'ler
├── packages/
│   ├── CastMart/             # CastMart modülleri
│   │   ├── Iyzico/           # iyzico ödeme
│   │   ├── Marketplace/      # Marketplace altyapısı
│   │   ├── Marketing/        # Kupon ve kampanyalar
│   │   ├── PayTR/            # PayTR ödeme
│   │   ├── Shipping/         # Kargo entegrasyonları
│   │   ├── SMS/              # SMS servisleri
│   │   ├── Tenant/           # Multi-tenant desteği
│   │   └── Trendyol/         # Trendyol entegrasyonu
│   └── Webkul/               # Bagisto core modülleri
├── routes/                   # Route tanımları
├── resources/
│   ├── themes/               # Tema dosyaları
│   └── lang/                 # Çeviriler
└── tests/                    # Test dosyaları
```

---

## 🧩 Modüler Mimari

### Yeni Modül Oluşturma

```bash
# Modül dizin yapısı
packages/CastMart/YeniModul/
├── src/
│   ├── Config/
│   ├── Console/Commands/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Providers/YeniModulServiceProvider.php
│   ├── Routes/admin.php
│   └── Services/
├── Resources/views/
└── composer.json
```

### Service Provider Örneği

```php
namespace CastMart\YeniModul\Providers;

use Illuminate\Support\ServiceProvider;

class YeniModulServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'yenimodul');
        $this->mergeConfigFrom(__DIR__ . '/../Config/yenimodul.php', 'yenimodul');
    }
    
    public function register(): void
    {
        $this->app->singleton(YeniModulService::class);
    }
}
```

### Modülü Kaydetme

`bootstrap/providers.php` dosyasına ekleyin:

```php
CastMart\YeniModul\Providers\YeniModulServiceProvider::class,
```

---

## 📐 Kodlama Standartları

### PHP

- PSR-12 standardına uyun
- Type hint'leri kullanın
- DocBlock'lar ekleyin
- Early return pattern tercih edin

```php
// ✅ Doğru
public function getUser(int $id): ?User
{
    if ($id <= 0) {
        return null;
    }
    
    return User::find($id);
}

// ❌ Yanlış
public function getUser($id)
{
    if ($id > 0) {
        return User::find($id);
    } else {
        return null;
    }
}
```

### Blade

- Component'leri tercih edin
- Nested ternary kullanmayın
- CSS class'larını düzenli tutun

```blade
{{-- ✅ Doğru --}}
<x-shop::product-card :product="$product" />

{{-- ❌ Yanlış --}}
<div class="product {{ $product->is_featured ? 'featured' : '' }} {{ $product->stock > 0 ? 'in-stock' : 'out-of-stock' }}">
```

### Naming Conventions

| Tip | Format | Örnek |
|-----|--------|-------|
| Controller | PascalCase + Controller | `ProductController` |
| Model | PascalCase, tekil | `Product` |
| Migration | snake_case | `create_products_table` |
| Tablo | snake_case, çoğul | `products` |
| Method | camelCase | `getActiveProducts()` |
| Route | kebab-case | `/admin/products` |
| Config key | snake_case | `cache_ttl` |

---

## 🗄️ Veritabanı

### Migration Oluşturma

```bash
php artisan make:migration create_example_table
```

### Migration Örneği

```php
public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('price', 10, 2);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        $table->softDeletes();
        
        $table->index('is_active');
    });
}
```

### Seeder Kullanımı

```bash
# Tüm seeder'ları çalıştır
php artisan db:seed

# Belirli seeder
php artisan db:seed --class=ProductSeeder
```

---

## 🔌 API Geliştirme

### Controller Örneği

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::active()
            ->paginate(config('api.response.default_per_page'));
            
        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
    
    public function show(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}
```

### API Resource Kullanımı

```php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->formatted_price,
            'in_stock' => $this->stock > 0,
        ];
    }
}
```

---

## 🧪 Test Yazımı

### Unit Test

```php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PriceCalculator;

class PriceCalculatorTest extends TestCase
{
    public function test_calculates_discount_correctly(): void
    {
        $calculator = new PriceCalculator();
        
        $result = $calculator->applyDiscount(100, 10);
        
        $this->assertEquals(90, $result);
    }
}
```

### Feature Test

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;

class ProductApiTest extends TestCase
{
    public function test_can_list_products(): void
    {
        Product::factory()->count(5)->create();
        
        $response = $this->getJson('/api/v1/products');
        
        $response->assertStatus(200)
                 ->assertJsonCount(5, 'data');
    }
}
```

### Test Çalıştırma

```bash
# Tüm testler
php artisan test

# Belirli test
php artisan test --filter=ProductApiTest

# Coverage raporu
php artisan test --coverage
```

---

## 🌿 Git İş Akışı

### Branch Yapısı

- `main` - Production
- `develop` - Development
- `feature/*` - Yeni özellikler
- `bugfix/*` - Bug düzeltmeleri
- `hotfix/*` - Acil düzeltmeler

### Commit Mesajları

```
<type>(<scope>): <description>

Örnekler:
feat(trendyol): add buybox tracking
fix(checkout): resolve payment redirect issue
docs(api): update authentication guide
refactor(cart): improve performance
test(product): add unit tests
```

### Pull Request Süreci

1. Feature branch oluştur
2. Değişiklikleri yap
3. Testleri çalıştır
4. PR aç
5. Code review bekle
6. Merge

---

## 📞 Yardım

Sorularınız için:
- GitHub Issues
- Slack: #castmart-dev
- E-posta: dev@castmart.com
