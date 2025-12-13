---
description: CastMart Master Görev Listesi - Proje yönetimi ve görev takibi
---

# 🚀 CastMart Master Görev Listesi Workflow

Bu workflow, CastMart projesinin tüm görevlerini yönetmek için kullanılır.

## Dosya Konumu
`.agent/CASTMART_MASTER_TASK_LIST.md`

## Hızlı Erişim Komutları

### 1. Görev Listesini Görüntüle
```bash
cat .agent/CASTMART_MASTER_TASK_LIST.md
```

### 2. Belirli Bir Fazı Görüntüle
Dosyayı açıp ilgili faz başlığına git:
- FAZ 0: Versiyonlama ve Rebranding
- FAZ 1: Kritik Eksiklikler
- FAZ 2: Trendyol Entegrasyonu
- FAZ 3: Multi-Tenant Altyapısı
- FAZ 4: Pazarlama ve Müşteri Deneyimi
- FAZ 5: Performans ve Güvenlik
- FAZ 6: Dokümantasyon ve Test
- FAZ 7: Genişleme ve Yeni Özellikler

### 3. Görev Durumu Güncelleme
Görev tamamlandığında `[ ]` işaretini `[x]` olarak değiştir.

### 4. Versiyon Kontrolü
```bash
cat VERSION
```

### 5. Değişiklik Günlüğü
```bash
cat CHANGELOG.md
```

## Öncelik Seviyeleri
- **P0**: KRİTİK - Hemen yapılmalı
- **P1**: YÜKSEK - Bu sprint içinde
- **P2**: ORTA - Sonraki sprint
- **P3**: DÜŞÜK - Backlog

## İlerleme Takibi
Toplam: 137 görev
- FAZ 0: 25 görev (Versiyonlama)
- FAZ 1: 20 görev (Kritik)
- FAZ 2: 15 görev (Trendyol) - %87 tamamlandı
- FAZ 3: 12 görev (Multi-Tenant)
- FAZ 4: 18 görev (Pazarlama)
- FAZ 5: 15 görev (Güvenlik)
- FAZ 6: 12 görev (Test)
- FAZ 7: 20 görev (Genişleme)

## Güncel Öncelikler (Sırasıyla)

// turbo-all
1. FAZ 0.2 - Webkul/Bagisto izlerini temizle
2. FAZ 0.1 - Versiyon kontrol sistemi kur
3. FAZ 1.1 - iyzico ödeme entegrasyonu
4. FAZ 1.2 - Kargo API entegrasyonları
5. FAZ 2.2 - Trendyol hardcoded değerleri düzelt
