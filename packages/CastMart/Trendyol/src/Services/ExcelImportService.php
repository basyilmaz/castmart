<?php

namespace CastMart\Trendyol\Services;

use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\MarketplaceAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExcelImportService
{
    protected MarketplaceAccount $account;
    protected array $errors = [];
    protected array $warnings = [];
    protected int $successCount = 0;
    protected int $errorCount = 0;

    public function __construct(MarketplaceAccount $account)
    {
        $this->account = $account;
    }

    /**
     * Fiyat/stok güncelleme dosyası import et
     */
    public function importPriceUpdate(UploadedFile $file): array
    {
        $this->resetCounters();

        $rows = $this->parseCsv($file);
        
        if (empty($rows)) {
            return $this->getResult('Dosya okunamadı veya boş');
        }

        // Header satırını atla
        $header = array_shift($rows);

        $updates = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2; // Header + 0-index

            if (count($row) < 8) {
                $this->addError($lineNumber, 'Yetersiz sütun sayısı');
                continue;
            }

            $barcode = trim($row[0] ?? '');
            $newStock = trim($row[3] ?? '');
            $newSalePrice = trim($row[5] ?? '');
            $newListPrice = trim($row[7] ?? '');

            if (empty($barcode)) {
                $this->addError($lineNumber, 'Barkod boş');
                continue;
            }

            // En az bir güncelleme olmalı
            if ($newStock === '' && $newSalePrice === '' && $newListPrice === '') {
                $this->addWarning($lineNumber, 'Güncelleme yapılacak değer yok, atlandı');
                continue;
            }

            // Listing'i bul
            $listing = MarketplaceListing::where('account_id', $this->account->id)
                ->where('barcode', $barcode)
                ->first();

            if (!$listing) {
                $this->addError($lineNumber, "Barkod bulunamadı: {$barcode}");
                continue;
            }

            // Güncelleme hazırla
            $update = ['barcode' => $barcode];

            if ($newStock !== '') {
                if (!is_numeric($newStock) || $newStock < 0) {
                    $this->addError($lineNumber, 'Geçersiz stok değeri');
                    continue;
                }
                $update['quantity'] = (int) $newStock;
                $listing->stock = (int) $newStock;
            }

            if ($newSalePrice !== '') {
                $newSalePrice = $this->parsePrice($newSalePrice);
                if ($newSalePrice === null || $newSalePrice <= 0) {
                    $this->addError($lineNumber, 'Geçersiz satış fiyatı');
                    continue;
                }
                $update['salePrice'] = $newSalePrice;
                $listing->sale_price = $newSalePrice;
            }

            if ($newListPrice !== '') {
                $newListPrice = $this->parsePrice($newListPrice);
                if ($newListPrice === null || $newListPrice <= 0) {
                    $this->addError($lineNumber, 'Geçersiz liste fiyatı');
                    continue;
                }
                $update['listPrice'] = $newListPrice;
                $listing->list_price = $newListPrice;
            }

            // Liste fiyatı satış fiyatından düşük olamaz
            if (isset($update['salePrice']) && isset($update['listPrice'])) {
                if ($update['listPrice'] < $update['salePrice']) {
                    $this->addError($lineNumber, 'Liste fiyatı satış fiyatından düşük olamaz');
                    continue;
                }
            }

            $updates[] = $update;
            $listing->save();
            $this->successCount++;
        }

        // Trendyol API'ye toplu güncelleme gönder
        if (!empty($updates)) {
            try {
                $service = new TrendyolService($this->account);
                $result = $service->updateInventory($updates);
                
                if (!$result) {
                    $this->addWarning(0, 'Trendyol API güncelleme hatası, yerel veritabanı güncellendi');
                }
            } catch (\Exception $e) {
                $this->addWarning(0, 'Trendyol API bağlantı hatası: ' . $e->getMessage());
            }
        }

        return $this->getResult();
    }

    /**
     * Ürün import et (yeni ürün ekleme)
     */
    public function importProducts(UploadedFile $file): array
    {
        $this->resetCounters();

        $rows = $this->parseCsv($file);
        
        if (empty($rows)) {
            return $this->getResult('Dosya okunamadı veya boş');
        }

        // Header satırını atla
        $header = array_shift($rows);

        $products = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;

            if (count($row) < 10) {
                $this->addError($lineNumber, 'Yetersiz sütun sayısı');
                continue;
            }

            $barcode = trim($row[0] ?? '');
            $title = trim($row[1] ?? '');
            $brand = trim($row[2] ?? '');
            $categoryId = trim($row[3] ?? '');
            $stock = trim($row[4] ?? '0');
            $salePrice = $this->parsePrice($row[5] ?? '0');
            $listPrice = $this->parsePrice($row[6] ?? '0');
            $vatRate = trim($row[7] ?? '18');
            $desi = trim($row[8] ?? '1');
            $description = trim($row[9] ?? '');

            // Validasyon
            $validator = Validator::make([
                'barcode' => $barcode,
                'title' => $title,
                'brand' => $brand,
                'category_id' => $categoryId,
                'stock' => $stock,
                'sale_price' => $salePrice,
            ], [
                'barcode' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'brand' => 'required|string|max:100',
                'category_id' => 'required|numeric',
                'stock' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                $this->addError($lineNumber, implode(', ', $validator->errors()->all()));
                continue;
            }

            // Mevcut ürün kontrolü
            $existing = MarketplaceListing::where('account_id', $this->account->id)
                ->where('barcode', $barcode)
                ->first();

            if ($existing) {
                $this->addWarning($lineNumber, "Barkod zaten mevcut: {$barcode}, güncelleme yapılacak");
                
                $existing->update([
                    'title' => $title,
                    'brand' => $brand,
                    'category_id' => $categoryId,
                    'stock' => (int) $stock,
                    'sale_price' => $salePrice,
                    'list_price' => $listPrice ?: $salePrice,
                    'vat_rate' => (int) $vatRate,
                    'desi' => (float) $desi,
                    'description' => $description,
                ]);
                
                $this->successCount++;
                continue;
            }

            // Yeni ürün oluştur
            MarketplaceListing::create([
                'account_id' => $this->account->id,
                'barcode' => $barcode,
                'title' => $title,
                'brand' => $brand,
                'category_id' => $categoryId,
                'stock' => (int) $stock,
                'sale_price' => $salePrice,
                'list_price' => $listPrice ?: $salePrice,
                'vat_rate' => (int) $vatRate,
                'desi' => (float) $desi,
                'description' => $description,
                'status' => 'draft',
            ]);

            $this->successCount++;
        }

        return $this->getResult();
    }

    /**
     * CSV dosyasını parse et
     */
    protected function parseCsv(UploadedFile $file): array
    {
        $rows = [];
        
        try {
            $content = file_get_contents($file->getRealPath());
            
            // BOM karakterini kaldır
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            
            // Satırlara böl
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // CSV parse (noktalı virgül ayracı)
                $row = str_getcsv($line, ';', '"');
                $rows[] = $row;
            }
        } catch (\Exception $e) {
            Log::error('CSV parse error', ['error' => $e->getMessage()]);
        }

        return $rows;
    }

    /**
     * Fiyat değerini parse et
     */
    protected function parsePrice(string $value): ?float
    {
        // Boşlukları ve TL/₺ sembollerini kaldır
        $value = preg_replace('/[^\d,.\-]/', '', $value);
        
        // Türkçe format (1.234,56) -> (1234.56)
        if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $value)) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            // Standart format
            $value = str_replace(',', '.', $value);
        }

        $float = (float) $value;
        return $float > 0 ? $float : null;
    }

    /**
     * Hata ekle
     */
    protected function addError(int $line, string $message): void
    {
        $this->errors[] = "Satır {$line}: {$message}";
        $this->errorCount++;
    }

    /**
     * Uyarı ekle
     */
    protected function addWarning(int $line, string $message): void
    {
        $prefix = $line > 0 ? "Satır {$line}: " : '';
        $this->warnings[] = $prefix . $message;
    }

    /**
     * Sayaçları sıfırla
     */
    protected function resetCounters(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    /**
     * Sonuç döndür
     */
    protected function getResult(?string $globalError = null): array
    {
        return [
            'success' => empty($globalError) && $this->errorCount === 0,
            'message' => $globalError ?? ($this->successCount > 0 
                ? "{$this->successCount} kayıt başarıyla işlendi" 
                : 'Hiçbir kayıt işlenemedi'),
            'processed' => $this->successCount,
            'errors' => $this->errors,
            'error_count' => $this->errorCount,
            'warnings' => $this->warnings,
        ];
    }
}
