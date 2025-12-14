# 📖 CastMart Admin Panel Kullanıcı Kılavuzu

Bu kılavuz, CastMart Admin Panel'in kullanımını adım adım açıklamaktadır.

---

## 📋 İçindekiler

1. [Giriş](#giriş)
2. [Dashboard](#dashboard)
3. [Ürün Yönetimi](#ürün-yönetimi)
4. [Sipariş Yönetimi](#sipariş-yönetimi)
5. [Müşteri Yönetimi](#müşteri-yönetimi)
6. [Trendyol Entegrasyonu](#trendyol-entegrasyonu)
7. [Raporlar](#raporlar)
8. [Ayarlar](#ayarlar)

---

## 🚪 Giriş

### Admin Paneline Erişim

1. Tarayıcınızda `https://siteniz.com/admin` adresine gidin
2. E-posta ve şifrenizi girin
3. 2FA aktifse, doğrulama kodunu girin

### İlk Giriş

- Varsayılan kullanıcı: `admin@example.com`
- Güvenlik için şifrenizi hemen değiştirin!

---

## 📊 Dashboard

Dashboard, mağazanızın genel durumunu gösterir:

### Özet Kartları

| Kart | Açıklama |
|------|----------|
| Günlük Satış | Bugünkü toplam satış tutarı |
| Yeni Siparişler | Bekleyen sipariş sayısı |
| Müşteriler | Toplam kayıtlı müşteri |
| Stok Uyarısı | Düşük stoklu ürün sayısı |

### Grafikler

- **Satış Grafiği**: Son 30 günlük satış trendi
- **Sipariş Durumları**: Sipariş dağılım pasta grafiği
- **En Çok Satanlar**: Top 10 ürün listesi

---

## 📦 Ürün Yönetimi

### Ürün Listesi

`Katalog > Ürünler` menüsünden erişin.

**Filtreleme Seçenekleri:**
- Kategori
- Stok durumu
- Fiyat aralığı
- Yayın durumu

### Yeni Ürün Ekleme

1. `+ Ürün Ekle` butonuna tıklayın
2. Genel bilgileri doldurun:
   - Ürün adı
   - SKU / Barkod
   - Açıklama
3. Fiyatlandırma:
   - Satış fiyatı
   - İndirimli fiyat (opsiyonel)
   - Maliyet fiyatı
4. Stok bilgisi girin
5. Görselleri yükleyin
6. Kategori seçin
7. `Kaydet` butonuna tıklayın

### Toplu İşlemler

Birden fazla ürün seçip:
- Fiyat güncelleme
- Stok güncelleme
- Kategori değiştirme
- Yayından kaldırma

işlemlerini yapabilirsiniz.

---

## 🛒 Sipariş Yönetimi

### Sipariş Listesi

`Satışlar > Siparişler` menüsünden erişin.

**Sipariş Durumları:**

| Durum | Renk | Açıklama |
|-------|------|----------|
| Yeni | 🔵 Mavi | Ödeme alındı, hazırlanmayı bekliyor |
| Hazırlanıyor | 🟡 Sarı | Paketleme aşamasında |
| Kargoda | 🟣 Mor | Kargo firmasına teslim edildi |
| Teslim Edildi | 🟢 Yeşil | Müşteriye ulaştı |
| İptal | 🔴 Kırmızı | Sipariş iptal edildi |
| İade | ⚫ Gri | Ürün iade edildi |

### Sipariş Detayı

- Müşteri bilgileri
- Teslimat adresi
- Sipariş kalemleri
- Ödeme bilgileri
- Kargo takip

### Kargo Güncelleme

1. Siparişi açın
2. `Kargo Bilgisi` bölümüne gidin
3. Kargo firmasını seçin
4. Takip numarasını girin
5. `Kaydet` butonuna tıklayın

---

## 👥 Müşteri Yönetimi

### Müşteri Listesi

`Müşteriler` menüsünden erişin.

**Görüntülenebilen Bilgiler:**
- Ad, soyad, e-posta
- Kayıt tarihi
- Sipariş sayısı
- Toplam harcama

### Müşteri Detayı

- Sipariş geçmişi
- Adres bilgileri
- Sepet durumu
- İstek listesi

---

## 🛍️ Trendyol Entegrasyonu

### Hesap Bağlama

1. `Pazaryerleri > Trendyol` menüsüne gidin
2. `+ Hesap Ekle` butonuna tıklayın
3. API bilgilerini girin:
   - Satıcı ID
   - API Key
   - API Secret
4. `Bağlantıyı Test Et` butonuna tıklayın
5. Başarılıysa `Kaydet`

### Ürün Gönderme

1. `Ürünler` sekmesine gidin
2. Göndermek istediğiniz ürünleri seçin
3. `Trendyol'a Gönder` butonuna tıklayın
4. Kategori eşleştirmesi yapın
5. Onaylayın

### Sipariş Senkronizasyonu

Siparişler otomatik olarak her 15 dakikada senkronize edilir.

Manuel senkronizasyon için: `Siparişler > Senkronize Et`

### Buybox Takibi

- Buybox durumunu canlı takip edin
- Rakip fiyatlarını görün
- Otomatik fiyat kuralları oluşturun

### Fiyat Kuralları

1. `Fiyat Kuralları` sekmesine gidin
2. `+ Kural Ekle`
3. Tetikleyici seçin:
   - Rakip daha ucuz
   - Buybox kaybedildi
   - Stok düşük
4. Aksiyon belirleyin:
   - Rakibe eşitle - X TL
   - Yüzde indirim
   - Sabit fiyat
5. Limitler koyun (min/max fiyat)
6. Kuralı aktifleştirin

---

## 📈 Raporlar

### Satış Raporu

- Günlük/Haftalık/Aylık satış
- Kategori bazlı satış
- Ürün bazlı satış
- Bölge bazlı satış

### Stok Raporu

- Düşük stoklu ürünler
- Stoksuz ürünler
- Stok hareket geçmişi

### Müşteri Raporu

- Yeni müşteriler
- Tekrar eden müşteriler
- Müşteri yaşam boyu değeri

### Raporları Dışa Aktarma

Tüm raporlar Excel (.xlsx) veya CSV formatında indirilebilir.

---

## ⚙️ Ayarlar

### Genel Ayarlar

- Mağaza adı ve logosu
- İletişim bilgileri
- Varsayılan para birimi
- Zaman dilimi

### Ödeme Ayarları

- iyzico entegrasyonu
- PayTR entegrasyonu
- Havale/EFT bilgileri

### Kargo Ayarları

- Aras Kargo
- Yurtiçi Kargo
- MNG Kargo
- Ücretsiz kargo limiti

### E-posta Ayarları

- SMTP yapılandırması
- E-posta şablonları
- Bildirim tercihleri

### Güvenlik

- Şifre değiştirme
- 2FA etkinleştirme
- Oturum yönetimi
- API anahtarları

---

## ❓ Sık Sorulan Sorular

### Şifremi unuttum, ne yapmalıyım?

Giriş sayfasındaki "Şifremi Unuttum" linkine tıklayın ve e-posta adresinizi girin.

### Ürün görseli yüklenmiyor?

- Dosya boyutu max 5MB olmalı
- Desteklenen formatlar: JPG, PNG, WebP
- Minimum boyut: 500x500 piksel

### Trendyol siparişleri gelmiyor?

1. API bağlantısını test edin
2. Scheduler'ın çalıştığından emin olun
3. Log'ları kontrol edin

---

## 📞 Destek

Teknik destek için:
- E-posta: destek@castmart.com
- Telefon: 0850 XXX XX XX
- Canlı destek: Panel içi chat
