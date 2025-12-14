# Changelog

Tüm önemli değişiklikler bu dosyada belgelenecektir.

Bu proje [Semantic Versioning](https://semver.org/lang/tr/) standardını takip eder.

## [Unreleased]

### Eklenenler
- Master görev listesi oluşturuldu (`.agent/CASTMART_MASTER_TASK_LIST.md`)
- VERSION dosyası eklendi
- CHANGELOG.md oluşturuldu
- MarketplaceReview modeli eklendi (müşteri yorumları için)
- DailyReportCommand eklendi (günlük performans raporları)
- TrendyolController'a fiyat kuralları API endpoint'leri eklendi
- Scheduler cron job'ları aktif edildi (bootstrap/app.php)
- **iyzico Ödeme Entegrasyonu** (packages/CastMart/Iyzico)
  - 3D Secure desteği
  - Taksitli ödeme (2-12 ay)
  - Checkout form entegrasyonu
  - İade ve iptal işlemleri
  - BIN sorgulama (taksit seçenekleri)
- **PayTR Ödeme Entegrasyonu** (packages/CastMart/PayTR)
  - iFrame checkout desteği
  - Taksitli ödeme (1-12 ay)
  - Callback (webhook) entegrasyonu
  - İade işlemleri
  - BIN sorgulama (taksit oranları)
- **Kargo Entegrasyonu** (packages/CastMart/Shipping)
  - Aras Kargo API entegrasyonu (SOAP)
  - MNG Kargo API entegrasyonu (REST)
  - Yurtiçi Kargo API entegrasyonu (SOAP)
  - Gönderi oluşturma, takip, etiket
  - Desi/ağırlık hesaplama
  - Fiyat karşılaştırma
  - Shipment veritabanı modeli
- **SMS Bildirim Sistemi** (packages/CastMart/SMS)
  - Netgsm API entegrasyonu
  - İletimerkezi API entegrasyonu
  - OTP doğrulama sistemi
  - Sipariş bildirimleri (oluşturuldu, kargoya verildi, teslim edildi)
  - SMS şablonları ve log sistemi
  - Günlük limit ve bakiye kontrolü
  - Admin panel SMS yönetimi
- **Multi-Tenant Altyapısı** (packages/CastMart/Tenant)
  - Tenant modeli ve migration
  - TenantUser ve TenantSubscription modelleri
  - IdentifyTenant middleware (subdomain/domain/path/header)
  - BelongsToTenant trait ve TenantScope
  - TenantManager service (oluşturma, abonelik, cache)
  - Tenant kayıt ve yönetim controller
  - Plan ve abonelik sistemi (Starter, Professional, Enterprise)
  - Admin panel tenant yönetimi
  - Modern kayıt formu (responsive)
  - Hata sayfaları (not-found, suspended)
  - Limit kontrol middleware
  - Abonelik süre kontrolü command
  - BillingService (iyzico entegreli abonelik ödemesi)
  - BillingController (checkout, callback, fatura)
  - Billing admin paneli
- **Pazarlama Sistemi** (packages/CastMart/Marketing)
  - Kupon sistemi (yüzde, sabit, ücretsiz kargo, X al Y öde)
  - Sadakat puan sistemi (tier'lar: Bronz, Gümüş, Altın, Platin)
  - Referral (arkadaş davet) sistemi
  - LoyaltyAccount ve LoyaltyTransaction modelleri
  - MarketingService (kupon doğrulama, puan işlemleri)
  - CouponController (CRUD, API doğrulama)
  - EmailMarketingService (hoş geldin, terk sepet, doğum günü, win-back)
  - NotificationService (push, fiyat düşüşü, stok bildirimi)
  - Artisan commands (abandoned-carts, birthday-emails, win-back)
  - Push subscription ve alert tablolar
- **Railway Deployment Hazırlığı**
  - railway.json yapılandırması
  - Procfile (web, worker, scheduler)
  - nixpacks.toml (PHP 8.2, Node 18)
  - Health check endpoint (/api/health)
  - GitHub Actions CI/CD workflow
  - Production .env.example
- **AI Chatbot Sistemi**
  - ChatbotService (OpenAI entegrasyonu)
  - Intent analizi (NLP-based)
  - Sipariş takip, iade, kargo, ödeme bilgisi
  - Canlı destek aktarımı
  - Widget.js (embed edilebilir chat UI)
- **Performans Optimizasyonu**
  - CacheService (ürün, kategori, sepet cache)
  - CacheResponse middleware
  - CompressResponse middleware (gzip)
  - Cache warm command
  - Query optimize command
- **Image Optimizasyonu** (app/Services)
  - WebP otomatik dönüşüm
  - Thumbnail oluşturma (small, medium, large, product)
  - Lazy loading desteği
  - ServeWebP middleware
  - Batch optimizasyon komutu (images:optimize)
- **Two-Factor Authentication (2FA)** (app/Services)
  - Google Authenticator desteği
  - Email OTP desteği
  - SMS OTP desteği
  - Recovery kodları (8 adet)
  - Müşteri ve Admin desteği
  - Session timeout (30 dakika)
  - Rate limiting koruması

### Değiştirildi
- IntelligenceService: Tüm hardcoded metrikler gerçek veritabanı sorgularıyla değiştirildi
  - getAverageProfitMargin(): Gerçek sipariş ve komisyon verilerinden hesaplama
  - getStockHealth(): Gerçek listing ve envanter verilerinden hesaplama
  - getCustomerRating(): marketplace_reviews tablosundan ortalama puan
  - getCargoPerformance(): Kargo zamanlaması verilerinden hesaplama
  - getWeeklyStats(): Yeni haftalık istatistik metodu eklendi
- TrendyolService: getSellerInfo() ve getProductSalesStats() metodları eklendi
- TrendyolServiceProvider: DailyReportCommand kaydı eklendi

### Değiştirilecekler
- Webkul namespace'leri CastMart olarak değiştirilecek
- Tüm marka referansları güncellenecek

---

## [1.0.0-alpha] - 2025-12-13

### Eklenenler
- Trendyol Intelligence System ("7. His")
  - Mağaza Sağlık Skoru
  - BuyBox Takip Sistemi
  - Otomatik Fiyat Kuralları
  - Akıllı Uyarı Sistemi
- Gelişmiş Komisyon Hesaplayıcı 2.0
  - İade maliyeti hesaplama
  - Gelir vergisi stopajı
  - Birim ambalaj maliyeti
  - Akıllı öneriler
- Marketplace altyapısı
  - MarketplaceAccount model
  - MarketplaceListing model
  - MarketplaceOrder model
  - CustomerQuestion model
- E-Fatura entegrasyonu (BizimHesap)
- TrendyolScraperService (Web scraping)
- IntelligenceService (Zeka servisi)

### Altyapı
- Laravel 11 tabanlı
- PHP 8.2+ gereksinimi
- Multi-channel desteği
- 20+ dil desteği
- AI entegrasyonu (OpenAI, Gemini, Ollama, GroqAI)

---

## [0.0.0] - Başlangıç

Bagisto 2.3.9 fork'u olarak başlatıldı.

---

<!-- Link tanımları -->
[Unreleased]: https://github.com/castintech/castmart/compare/v1.0.0-alpha...HEAD
[1.0.0-alpha]: https://github.com/castintech/castmart/releases/tag/v1.0.0-alpha
