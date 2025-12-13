# CastMart Kurulum Kılavuzu

## Gereksinimler

- PHP 8.2+
- MySQL 8.0+
- Redis 7.0+
- Node.js 18+
- Composer 2.x
- npm 9.x

---

## 1. Yerel Kurulum

### Projeyi Klonla

```bash
git clone https://github.com/basyilmaz/castmart.git
cd castmart
```

### Bağımlılıkları Yükle

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### Environment Dosyası

```bash
cp .env.example .env
php artisan key:generate
```

### Veritabanı Ayarları

`.env` dosyasını düzenleyin:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=castmart
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Migration ve Seed

```bash
php artisan migrate
php artisan db:seed
```

### Storage Link

```bash
php artisan storage:link
```

### Vite Build

```bash
npm run build
```

### Sunucu Başlat

```bash
php artisan serve
```

Uygulama: http://localhost:8000
Admin Panel: http://localhost:8000/admin

---

## 2. Production Deployment (Railway)

### Gerekli Servisler

1. **MySQL Database**
2. **Redis Cache**
3. **PHP Application**

### Environment Variables

Railway Dashboard'dan ayarlayın:

```env
APP_NAME=CastMart
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://castmart.castintech.com

# Database (Railway otomatik sağlar)
DB_CONNECTION=mysql
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQLDATABASE}
DB_USERNAME=${MYSQLUSER}
DB_PASSWORD=${MYSQLPASSWORD}

# Redis (Railway otomatik sağlar)
CACHE_STORE=redis
REDIS_HOST=${REDISHOST}
REDIS_PASSWORD=${REDISPASSWORD}
REDIS_PORT=${REDISPORT}

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Queue
QUEUE_CONNECTION=database
```

### Custom Domain

1. Railway Settings → Domains
2. + Custom Domain: `castmart.castintech.com`
3. DNS'e CNAME ekleyin:
   - Type: CNAME
   - Host: castmart
   - Value: [railway-assigned].up.railway.app

### SSL

Railway otomatik olarak Let's Encrypt SSL sertifikası sağlar.

---

## 3. Entegrasyon Ayarları

### İyzico (Ödeme)

```env
IYZICO_API_KEY=your_api_key
IYZICO_SECRET_KEY=your_secret_key
IYZICO_MODE=live  # veya sandbox
```

### Trendyol API

```env
TRENDYOL_API_KEY=your_api_key
TRENDYOL_API_SECRET=your_api_secret
TRENDYOL_SUPPLIER_ID=your_supplier_id
TRENDYOL_ENABLED=true
```

### SMS (Netgsm)

```env
SMS_ENABLED=true
SMS_DEFAULT_DRIVER=netgsm
NETGSM_USERCODE=your_usercode
NETGSM_PASSWORD=your_password
NETGSM_HEADER=CASTMART
```

### Kargo Entegrasyonları

```env
# Aras Kargo
ARAS_USERNAME=your_username
ARAS_PASSWORD=your_password
ARAS_CUSTOMER_CODE=your_code

# MNG Kargo
MNG_API_KEY=your_api_key
MNG_SECRET_KEY=your_secret

# Yurtiçi Kargo
YURTICI_USERNAME=your_username
YURTICI_PASSWORD=your_password
```

---

## 4. Scheduled Tasks (Cron)

Scheduler için cron job ekleyin:

```bash
* * * * * cd /path/to/castmart && php artisan schedule:run >> /dev/null 2>&1
```

### Otomatik Çalışan Görevler

| Görev | Açıklama | Sıklık |
|-------|----------|--------|
| `trendyol:sync-products` | Ürün senkronizasyonu | Her 30 dakika |
| `trendyol:sync-orders` | Sipariş senkronizasyonu | Her 15 dakika |
| `marketing:abandoned-carts` | Terk sepet emaili | Saatlik |
| `marketing:birthday-emails` | Doğum günü emaili | Günlük 09:00 |
| `tenant:check-subscriptions` | Abonelik kontrolü | Günlük 00:00 |

---

## 5. Queue Worker

Production'da queue worker çalıştırın:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Supervisor ile yönetim:

```ini
[program:castmart-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/castmart/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/castmart/storage/logs/worker.log
```

---

## 6. Cache Optimizasyonu

Production'da cache'leri aktifleştirin:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:warm
```

---

## 7. Güvenlik

### HTTPS Zorunlu

Production'da HTTPS zorunludur. `.env`:

```env
FORCE_HTTPS=true
```

### Admin Güvenliği

Admin paneli için güçlü şifre kullanın ve 2FA aktifleştirin.

---

## 8. Yedekleme

Günlük veritabanı yedeği:

```bash
mysqldump -u root -p castmart > backup_$(date +%Y%m%d).sql
```

---

## Destek

Sorunlar için:
- Email: support@castintech.com
- GitHub Issues: https://github.com/basyilmaz/castmart/issues

---

*Version: 1.0.0*
*Last Updated: 2025-12-13*
