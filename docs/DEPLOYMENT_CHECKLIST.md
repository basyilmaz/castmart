# 🚀 CastMart Production Deployment Checklist

Bu döküman, CastMart'ı production ortamına deploy etmeden önce kontrol edilmesi gereken tüm adımları içerir.

---

## 📋 Pre-Deployment Checklist

### 1. ⚙️ Ortam Yapılandırması

- [ ] `.env.production` dosyası hazırlandı
- [ ] `APP_ENV=production` ayarlandı
- [ ] `APP_DEBUG=false` ayarlandı
- [ ] `APP_URL` doğru domain ile ayarlandı
- [ ] Veritabanı bağlantı bilgileri güncellendi
- [ ] Redis bağlantısı yapılandırıldı
- [ ] Mail sunucu ayarları yapıldı

### 2. 🔐 Güvenlik

- [ ] `APP_KEY` yeni ve güvenli
- [ ] Tüm API anahtarları production değerleriyle güncellendi
- [ ] SSL sertifikası kuruldu (HTTPS zorunlu)
- [ ] Database şifreleri güçlü
- [ ] Admin şifreleri değiştirildi
- [ ] 2FA admin kullanıcılar için aktif

### 3. 💳 Ödeme Entegrasyonları

- [ ] iyzico production modda
  - [ ] `IYZICO_API_KEY` production key
  - [ ] `IYZICO_SECRET_KEY` production secret
  - [ ] `IYZICO_BASE_URL=https://api.iyzipay.com`
  
- [ ] PayTR production modda
  - [ ] `PAYTR_MERCHANT_ID` production ID
  - [ ] `PAYTR_MERCHANT_KEY` production key
  - [ ] `PAYTR_MERCHANT_SALT` production salt
  - [ ] `PAYTR_TEST_MODE=false`

### 4. 🛍️ Trendyol Entegrasyonu

- [ ] Production API bilgileri girildi
- [ ] Webhook URL'leri yapılandırıldı
- [ ] Cron job'lar aktif
- [ ] Sipariş senkronizasyonu test edildi

### 5. 📧 E-posta Yapılandırması

- [ ] SMTP ayarları production değerleri
- [ ] E-posta şablonları kontrol edildi
- [ ] Test e-postası gönderildi
- [ ] SPF/DKIM kayıtları eklendi

### 6. 📱 SMS Yapılandırması

- [ ] SMS provider production modda
- [ ] Bakiye kontrolü yapıldı
- [ ] Test SMS gönderildi

---

## 🔄 Deployment Adımları

### Adım 1: Kodu Çek

```bash
cd /var/www/castmart
git fetch origin
git checkout main
git pull origin main
```

### Adım 2: Dependencies Güncelle

```bash
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
```

### Adım 3: Cache Temizle ve Optimize Et

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache
```

### Adım 4: Migration Çalıştır

```bash
php artisan migrate --force
```

### Adım 5: Storage Link

```bash
php artisan storage:link
```

### Adım 6: Queue Worker Yeniden Başlat

```bash
php artisan queue:restart
sudo supervisorctl restart all
```

### Adım 7: Scheduler Kontrol

```bash
# Crontab'da olduğundan emin ol
* * * * * cd /var/www/castmart && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📊 Post-Deployment Kontroller

### 1. Site Erişim Kontrolü

- [ ] Ana sayfa yükleniyor
- [ ] Ürün sayfaları açılıyor
- [ ] Sepet çalışıyor
- [ ] Checkout akışı tamamlanıyor
- [ ] Admin panel erişilebilir

### 2. Performans Kontrolü

- [ ] Sayfa yüklenme süresi < 3 saniye
- [ ] TTFB < 500ms
- [ ] İlk anlamlı boyama < 1.5s
- [ ] Lighthouse skoru > 80

### 3. Fonksiyonel Testler

- [ ] Kayıt olma çalışıyor
- [ ] Giriş yapma çalışıyor
- [ ] Ürün arama çalışıyor
- [ ] Sipariş verebilme
- [ ] Ödeme işlemi (test siparişi)
- [ ] E-posta bildirimleri geliyor

### 4. Entegrasyon Kontrolleri

- [ ] Trendyol sipariş çekme
- [ ] Kargo takip
- [ ] SMS gönderimi
- [ ] Webhook'lar çalışıyor

---

## 🐛 Rollback Planı

Eğer deployment başarısız olursa:

```bash
# Önceki versiyona dön
git checkout [previous-tag]

# Cache temizle
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Migration geri al (gerekirse)
php artisan migrate:rollback --step=1

# Queue restart
php artisan queue:restart
```

---

## 📈 Monitoring

### Uptime Monitoring
- [ ] UptimeRobot veya benzeri kuruldu
- [ ] Slack/Telegram bildirimleri aktif

### Error Tracking
- [ ] Sentry kuruldu ve yapılandırıldı
- [ ] Log rotation aktif

### Performance Monitoring
- [ ] New Relic veya Blackfire kuruldu
- [ ] Slow query logging aktif

---

## 🔗 Önemli URL'ler

| Servis | URL |
|--------|-----|
| Production Site | https://castmart.com |
| Admin Panel | https://castmart.com/admin |
| API | https://castmart.com/api |
| Health Check | https://castmart.com/health |

---

## 📞 Acil Durum İletişim

- DevOps: devops@castmart.com
- Backend Lead: backend@castmart.com
- On-Call: +90 XXX XXX XX XX

---

*Son güncelleme: 2024-12-15*
