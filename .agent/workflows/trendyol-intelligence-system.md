---
description: Trendyol Akıllı Satıcı Sistemi - 7. His & Gelişmiş Komisyon Hesaplayıcı
---

# 🧠 CastMart Trendyol Intelligence System

## VİZYON
Trendyol'da mağazası olan bir firmaya "7. his" gibi çalışan, yapay zeka destekli, eşsiz bir karar destek sistemi.

---

## 🎯 İKİ CAN ALICI ÖZELLİK

### 1️⃣ TRENDYOL "7. HİS" - AI DESTEKLI SATIŞ ZEKASI

**Konsept:** Satıcının henüz fark etmediği fırsatları, riskleri ve optimizasyon noktalarını proaktif olarak tespit eden akıllı asistan.

#### 📊 Dashboard - "Bugün Dikkat Et!"
```
┌─────────────────────────────────────────────────────────────────┐
│ 🔴 ACİL (3)  🟡 ÖNEMLİ (7)  🟢 FIRSAT (5)  📈 TREND (12)        │
├─────────────────────────────────────────────────────────────────┤
│ 🔴 Ürün X BuyBox kaybetti! Rakip 2₺ düşürdü → [Fiyat Güncelle] │
│ 🔴 Stok Z 3 günde tükenecek! → [Stok Güncelle]                  │
│ 🟡 Ürün Y satışları %40 düştü → [Analiz Et]                     │
│ 🟢 Kategori K'de trend yükselişi → [Fırsat Analizi]             │
│ 📈 Rakip mağaza C fiyat değiştirdi → [Karşılaştır]              │
└─────────────────────────────────────────────────────────────────┘
```

#### 🤖 AI Modülleri

##### A) BuyBox Tahmin Motoru
- **Gerçek Zamanlı İzleme:** Her ürün için BuyBox durumu
- **Tahmin Algoritması:** 
  - Fiyat değişikliği etkisi tahmini
  - Rakip davranış analizi
  - Puan/Yorum etkisi hesaplama
- **Otomatik Öneriler:**
  - "Fiyatı 5₺ düşürürsen %85 BuyBox kazanma şansın"
  - "Bu rakiple rekabet etme, stok bitiyor"

##### B) Kar Marjı Risk Analizi
- Ürün bazlı karlılık puanı (0-100)
- Kampanya dönemi kar simülasyonu
- "Bu fiyatla 30 gün satarsan toplam kârın: X₺"
- Break-even analizi

##### C) Talep Tahmin Motoru
- Sezonluk trend analizi
- Kategori bazlı büyüme tahmini
- "Önümüzdeki 7 gün tahmini satış: X adet"
- Stok optimizasyonu önerileri

##### D) Rekabet İstihbaratı
- Rakip fiyat hareketleri
- Yeni rakip girişi uyarısı
- Fiyat savaşı riski analizi
- Pazar payı tahmini

##### E) Performans Skorları
```
Mağaza Sağlık Skoru: 78/100
├── BuyBox Oranı: 65% (↑5%)
├── Kar Marjı: 18% (●)
├── Stok Sağlığı: 85% (↑)
├── Müşteri Puanı: 4.7/5 (●)
├── Kargo Performansı: 92% (↑)
└── Ürün Çeşitliliği: 67% (↓)
```

#### 🎯 Hedef KPI'lar
1. **BuyBox Win Rate** - %70+ hedef
2. **Net Kar Marjı** - %15-20 hedef
3. **Stok Devir Hızı** - 30 gün altı
4. **Müşteri Memnuniyeti** - 4.5+ puan
5. **İade Oranı** - %5 altı

---

### 2️⃣ GELİŞMİŞ KOMİSYON HESAPLAYICI 2.0

**Mevcut:** Temel kar hesaplama
**Hedef:** Piyasadaki en kapsamlı Trendyol kar hesaplama aracı

#### 🧮 Hesaplama Modülleri

##### A) Detaylı Maliyet Girdileri
```
ÜRÜN MALİYETLERİ
├── Alış Fiyatı (KDV Hariç)
├── Alış KDV Oranı (%0, %1, %10, %20)
├── Tedarik Kargo
├── Gümrük/İthalat (varsa)
└── Birim Ambalaj Maliyeti

TRENDYOL KESİNTİLERİ
├── Kategori Komisyonu (%)
├── Kampanya Komisyon Eklentisi (%)
├── Hizmet Bedeli
├── İndirim Katılım Bedeli
└── Reklam/Sponsorlu Ürün (%)

KARGO MALİYETLERİ
├── Kargo Firması Seçimi (TEX, PTT, Aras...)
├── Desi/Ağırlık Hesabı
├── KDV Dahil/Hariç Seçimi
├── Ücretsiz Kargo Kampanyası Etkisi
└── İade Kargo Maliyeti (ortalama iade oranı)

VERGİLER
├── Satış KDV
├── Alış KDV (indirim)
├── Kargo KDV (indirim)
├── Komisyon KDV (indirim)
├── Gelir Vergisi Stopajı (%2)
└── Net Ödenecek KDV
```

##### B) Çoklu Senaryo Analizi
```
┌─────────────────────────────────────────────────────────────┐
│ SENARYO KARŞILAŞTIRMA                                       │
├──────────────────┬──────────┬──────────┬──────────┬─────────┤
│                  │ Normal   │ Kampanya │ BuyBox   │ Premium │
├──────────────────┼──────────┼──────────┼──────────┼─────────┤
│ Satış Fiyatı     │ 119.90₺  │ 99.90₺   │ 109.90₺  │ 139.90₺ │
│ Komisyon         │ 21.58₺   │ 23.98₺   │ 19.78₺   │ 25.18₺  │
│ Kargo            │ 61.04₺   │ 61.04₺   │ 61.04₺   │ 0₺      │
│ Net Kâr          │ 18.42₺   │ -5.26₺   │ 10.22₺   │ 45.86₺  │
│ Kar Marjı        │ 15.4%    │ -5.3%    │ 9.3%     │ 32.8%   │
│ ROI              │ 30.7%    │ -8.8%    │ 17.0%    │ 76.4%   │
└──────────────────┴──────────┴──────────┴──────────┴─────────┘
```

##### C) Tersine Hesaplama
"X₺ kar istiyorum" → Minimum satış fiyatı hesaplama
"Minimum %20 kar marjı istiyorum" → Fiyat önerisi

##### D) Toplu Ürün Analizi
- Excel/CSV import
- Tüm ürünlerin karlılık analizi
- En karlı / En riskli ürün sıralaması
- Portföy kar simülasyonu

##### E) Kampanya Simülatörü
```
KAMPANYA ETKİ ANALİZİ
├── Kampanya Tipi: %20 İndirim
├── Tahmini Satış Artışı: +150%
├── Normal Dönem Kârı: 500₺/hafta
├── Kampanya Dönemi Kârı: 375₺/hafta
├── Hacım Artışı Etkisi: +750₺
└── NET ETKİ: +625₺ (Kampanya FAYDALI ✓)
```

##### F) İade Maliyet Hesabı
- Ortalama iade oranı (%5-15)
- İade kargo maliyeti
- Değer kaybı (hasarlı iade)
- Net kar etkisi

#### 📊 Görsel Çıktılar

##### Pasta Grafik - Maliyet Dağılımı
```
Satış Fiyatı: 119.90₺
├── 🟢 Net Kâr: 18.42₺ (15.4%)
├── 🔴 Ürün Maliyeti: 50.00₺ (41.7%)
├── 🟠 Kargo: 61.04₺ (25.9%)
├── 🟡 Komisyon: 21.58₺ (18.0%)
└── 🟣 Diğer: 0.00₺ (0%)
```

##### Kar Trend Grafiği
- Son 30 gün kar trendi
- Kampanya dönemleri işaretli
- Tahmin çizgisi

#### 🎯 Akıllı Öneriler Motoru

```javascript
// Örnek öneri mantığı
if (karMarji < 10) {
  öneri("⚠️ Düşük kar marjı! Min. %15 hedefleyin.");
  öneri("💡 Fiyatı " + (minKarFiyati - satisFiyati) + "₺ artırın");
}

if (kargoMaliyeti > satisFiyati * 0.30) {
  öneri("📦 Kargo maliyeti çok yüksek!");
  öneri("💡 Bundle satış veya daha ucuz kargo firması deneyin");
}

if (buyboxKaybi) {
  öneri("🎯 BuyBox kaybediliyor!");
  öneri("💡 " + buyboxKazanmaFiyati + "₺ ile kazanabilirsiniz");
}
```

---

## 🛠 TEKNİK MİMARİ

### Backend Servisler
```
packages/CastMart/Trendyol/src/Services/
├── TrendyolIntelligenceService.php   # Ana zeka servisi
├── BuyBoxAnalyzerService.php          # BuyBox analizi
├── ProfitCalculatorService.php        # Gelişmiş kar hesaplama
├── CompetitorTrackerService.php       # Rakip takibi
├── DemandForecastService.php          # Talep tahmini
├── CampaignSimulatorService.php       # Kampanya simülasyonu
└── AlertManagerService.php            # Uyarı yönetimi
```

### Veritabanı Tabloları
```sql
-- Ürün karlılık geçmişi
trendyol_product_profitability
├── product_id
├── date
├── sale_price
├── cost_price
├── commission
├── cargo_cost
├── net_profit
└── margin_percentage

-- Rakip takibi
trendyol_competitor_prices
├── product_id
├── competitor_id
├── price
├── has_buybox
└── tracked_at

-- Uyarılar
trendyol_alerts
├── type (buybox_lost, low_stock, price_change, margin_low)
├── severity (critical, warning, info, opportunity)
├── product_id
├── message
├── action_taken
└── created_at
```

### Frontend Sayfaları
```
Resources/views/admin/
├── intelligence/
│   ├── dashboard.blade.php     # Ana "7. His" dashboard
│   ├── buybox-tracker.blade.php
│   ├── profit-analyzer.blade.php
│   └── competitor-monitor.blade.php
├── commission-calculator/
│   └── index.blade.php          # Gelişmiş hesaplayıcı (v2)
└── components/
    ├── profit-chart.blade.php
    ├── alert-card.blade.php
    └── scenario-table.blade.php
```

---

## 📅 UYGULAMA PLANI

### FAZ 1: GELİŞMİŞ KOMİSYON HESAPLAYICI (1-2 Hafta)
- [ ] Detaylı maliyet girdileri
- [ ] Çoklu senaryo karşılaştırma
- [ ] Tersine hesaplama
- [ ] İade maliyeti hesabı
- [ ] Pasta grafik görselleştirme
- [ ] Excel export
- [ ] Akıllı öneri motoru

### FAZ 2: BUYBOX TAKİP SİSTEMİ (1-2 Hafta)
- [ ] Ürün bazlı BuyBox durumu
- [ ] Rakip fiyat takibi
- [ ] Fiyat değişikliği uyarıları
- [ ] BuyBox kazanma tahmini
- [ ] Otomatik fiyat önerisi

### FAZ 3: ZEKA DASHBOARD (2-3 Hafta)
- [ ] Acil/Önemli/Fırsat kategorileme
- [ ] Mağaza sağlık skoru
- [ ] Trend analizi
- [ ] Stok uyarıları
- [ ] Performans grafiği

### FAZ 4: TAHMİN MOTORLERİ (2-4 Hafta)
- [ ] Talep tahmini (basit istatistik)
- [ ] Sezonluk analiz
- [ ] Kampanya ROI tahmini
- [ ] Kar simülasyonu

---

## 💎 REKABET AVANTAJLARI

1. **Türkiye'de İlk:** Trendyol'a özel AI destekli satıcı zekası
2. **Gerçek Zamanlı:** Anlık uyarılar ve öneriler
3. **Proaktif:** Sorun olmadan önce tespit
4. **Bütünleşik:** Tek panelden tüm analizler
5. **Aksiyon Odaklı:** Her uyarının yanında çözüm
6. **Öğrenen Sistem:** Kullanıldıkça daha akıllı öneriler

---

## 🎯 BAŞARI METRİKLERİ

| Metrik | Mevcut | Hedef |
|--------|--------|-------|
| BuyBox Win Rate | %50 | %75 |
| Net Kar Marjı | %12 | %20 |
| Stok Tükenme Süprizi | 5/ay | 0/ay |
| Rakip Fiyat Tepki Süresi | 24 saat | 1 saat |
| Kampanya ROI Tahmini | Yok | %90 doğruluk |

---

*Created: 2025-12-11*
*Version: 1.0*
*Author: CastMart AI Development Team*
