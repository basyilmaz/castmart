# Git Flow Branching Strategy

## Branch Yapısı

```
main (production)
│
├── develop (geliştirme)
│   │
│   ├── feature/xxx (yeni özellikler)
│   │
│   └── bugfix/xxx (bug düzeltmeleri)
│
├── hotfix/xxx (acil production düzeltmeleri)
│
└── release/x.x.x (sürüm hazırlığı)
```

## Branch Kuralları

### `main` (Production)
- **Koruma:** Doğrudan push yasak
- **Merge:** Sadece `release/*` ve `hotfix/*` branch'lerinden
- **Deploy:** Railway otomatik deploy eder

### `develop` (Geliştirme)
- **Amaç:** Aktif geliştirme branch'i
- **Merge:** Feature ve bugfix branch'leri buraya merge edilir
- **Test:** Staging ortamında test edilir

### `feature/*` (Yeni Özellikler)
- **Oluşturma:** `git checkout -b feature/ozellik-adi develop`
- **İsimlendirme:** `feature/iyzico-entegrasyonu`, `feature/sms-bildirimleri`
- **Merge:** PR ile `develop`'a

### `bugfix/*` (Bug Düzeltmeleri)
- **Oluşturma:** `git checkout -b bugfix/bug-adi develop`
- **İsimlendirme:** `bugfix/sepet-hesaplama-hatasi`
- **Merge:** PR ile `develop`'a

### `hotfix/*` (Acil Düzeltmeler)
- **Oluşturma:** `git checkout -b hotfix/x.x.x main`
- **Amaç:** Production'daki kritik hataları düzeltme
- **Merge:** Hem `main` hem `develop`'a

### `release/*` (Sürüm Hazırlığı)
- **Oluşturma:** `git checkout -b release/x.x.x develop`
- **Amaç:** Sürüm öncesi son hazırlıklar
- **Merge:** `main`'e merge edilir, tag oluşturulur

## Örnek İş Akışı

### Yeni Özellik Ekleme
```bash
# 1. Feature branch oluştur
git checkout develop
git pull origin develop
git checkout -b feature/yeni-ozellik

# 2. Geliştirme yap
git add .
git commit -m "feat: yeni özellik eklendi"

# 3. Push et
git push -u origin feature/yeni-ozellik

# 4. GitHub'da PR aç (develop'a)
```

### Hotfix (Acil Düzeltme)
```bash
# 1. Hotfix branch oluştur
git checkout main
git pull origin main
git checkout -b hotfix/1.0.1

# 2. Düzeltmeyi yap
git add .
git commit -m "fix: kritik hata düzeltildi"

# 3. Main'e merge et
git checkout main
git merge hotfix/1.0.1
git tag -a v1.0.1 -m "Hotfix release"
git push origin main --tags

# 4. Develop'a da merge et
git checkout develop
git merge hotfix/1.0.1
git push origin develop

# 5. Hotfix branch'i sil
git branch -d hotfix/1.0.1
```

## Commit Mesajı Formatı

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types
- `feat`: Yeni özellik
- `fix`: Bug düzeltme
- `docs`: Dokümantasyon
- `style`: Kod formatı (fonksiyon değişikliği yok)
- `refactor`: Kod refactoring
- `test`: Test ekleme/düzeltme
- `chore`: Build, config değişiklikleri

### Örnekler
```
feat(trendyol): komisyon hesaplayıcı eklendi
fix(iyzico): 3D secure callback hatası düzeltildi
docs(readme): kurulum talimatları güncellendi
```

## Versiyon Numaralandırma (SemVer)

```
MAJOR.MINOR.PATCH[-PRERELEASE]

Örnek: 1.2.3-alpha
```

- **MAJOR**: Geriye uyumsuz API değişiklikleri
- **MINOR**: Geriye uyumlu yeni özellikler
- **PATCH**: Geriye uyumlu bug düzeltmeleri
- **PRERELEASE**: alpha, beta, rc1, rc2

## Mevcut Sürümler

| Tag | Tarih | Açıklama |
|-----|-------|----------|
| v1.0.0-alpha | 2025-12-14 | İlk alpha sürümü |

---

*Son Güncelleme: 2025-12-14*
