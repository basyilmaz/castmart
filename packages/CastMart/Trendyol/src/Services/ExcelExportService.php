<?php

namespace CastMart\Trendyol\Services;

use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\MarketplaceAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExcelExportService
{
    protected MarketplaceAccount $account;

    public function __construct(MarketplaceAccount $account)
    {
        $this->account = $account;
    }

    /**
     * Ürünleri Excel formatında export et
     */
    public function exportProducts(array $filters = []): string
    {
        $listings = MarketplaceListing::where('account_id', $this->account->id)
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['category_id']), fn($q) => $q->where('category_id', $filters['category_id']))
            ->get();

        $rows = [];
        
        // Header
        $rows[] = [
            'Barkod',
            'Ürün Adı',
            'Marka',
            'Kategori ID',
            'Stok',
            'Satış Fiyatı',
            'Liste Fiyatı',
            'KDV Oranı',
            'Desi',
            'Durum',
            'Trendyol ID',
            'Son Güncelleme',
        ];

        foreach ($listings as $listing) {
            $rows[] = [
                $listing->barcode,
                $listing->title,
                $listing->brand ?? '',
                $listing->category_id ?? '',
                $listing->stock ?? 0,
                $listing->sale_price ?? 0,
                $listing->list_price ?? 0,
                $listing->vat_rate ?? 18,
                $listing->desi ?? 1,
                $listing->status,
                $listing->external_id ?? '',
                $listing->updated_at?->format('Y-m-d H:i') ?? '',
            ];
        }

        return $this->generateCsv($rows, 'products');
    }

    /**
     * Siparişleri Excel formatında export et
     */
    public function exportOrders(array $filters = []): string
    {
        $orders = MarketplaceOrder::where('account_id', $this->account->id)
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['from']), fn($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->orderBy('created_at', 'desc')
            ->get();

        $rows = [];
        
        // Header
        $rows[] = [
            'Sipariş No',
            'Paket ID',
            'Tarih',
            'Durum',
            'Müşteri Adı',
            'Şehir',
            'Tutar',
            'Kargo Firması',
            'Kargo Takip No',
            'Ürün Sayısı',
        ];

        foreach ($orders as $order) {
            $customerData = $order->customer_data ?? [];
            $itemsData = $order->items_data ?? [];
            
            $rows[] = [
                $order->external_order_number ?? $order->external_order_id,
                $order->package_id ?? '',
                $order->created_at?->format('Y-m-d H:i') ?? '',
                $this->translateStatus($order->status),
                ($customerData['firstName'] ?? '') . ' ' . ($customerData['lastName'] ?? ''),
                $customerData['city'] ?? '',
                $order->total_amount ?? 0,
                $order->cargo_provider ?? '',
                $order->tracking_number ?? '',
                is_array($itemsData) ? count($itemsData) : 0,
            ];
        }

        return $this->generateCsv($rows, 'orders');
    }

    /**
     * Fiyat güncelleme şablonu oluştur
     */
    public function exportPriceTemplate(): string
    {
        $listings = MarketplaceListing::where('account_id', $this->account->id)
            ->whereNotNull('barcode')
            ->get(['barcode', 'title', 'stock', 'sale_price', 'list_price']);

        $rows = [];
        
        // Header
        $rows[] = [
            'Barkod',
            'Ürün Adı (Bilgi)',
            'Mevcut Stok',
            'Yeni Stok',
            'Mevcut Satış Fiyatı',
            'Yeni Satış Fiyatı',
            'Mevcut Liste Fiyatı',
            'Yeni Liste Fiyatı',
        ];

        foreach ($listings as $listing) {
            $rows[] = [
                $listing->barcode,
                $listing->title,
                $listing->stock ?? 0,
                '', // Yeni stok (kullanıcı dolduracak)
                $listing->sale_price ?? 0,
                '', // Yeni satış fiyatı
                $listing->list_price ?? 0,
                '', // Yeni liste fiyatı
            ];
        }

        return $this->generateCsv($rows, 'price_template');
    }

    /**
     * Komisyon raporu export et
     */
    public function exportCommissionReport(array $filters = []): string
    {
        $orders = MarketplaceOrder::where('account_id', $this->account->id)
            ->where('status', 'delivered')
            ->when(isset($filters['from']), fn($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when(isset($filters['to']), fn($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->get();

        $rows = [];
        
        // Header
        $rows[] = [
            'Sipariş No',
            'Tarih',
            'Ürün Tutarı',
            'Komisyon Oranı (%)',
            'Komisyon Tutarı',
            'Kargo Geliri',
            'Net Kazanç',
        ];

        $totalAmount = 0;
        $totalCommission = 0;
        $totalNet = 0;

        foreach ($orders as $order) {
            $orderData = $order->order_data ?? [];
            $amount = $order->total_amount ?? 0;
            $commissionRate = $orderData['commissionRate'] ?? 12; // Varsayılan %12
            $commission = $amount * ($commissionRate / 100);
            $cargoIncome = $orderData['cargoProviderAmount'] ?? 0;
            $net = $amount - $commission + $cargoIncome;

            $totalAmount += $amount;
            $totalCommission += $commission;
            $totalNet += $net;

            $rows[] = [
                $order->external_order_number ?? $order->external_order_id,
                $order->created_at?->format('Y-m-d') ?? '',
                number_format($amount, 2, ',', '.'),
                $commissionRate,
                number_format($commission, 2, ',', '.'),
                number_format($cargoIncome, 2, ',', '.'),
                number_format($net, 2, ',', '.'),
            ];
        }

        // Toplam satırı
        $rows[] = [];
        $rows[] = [
            'TOPLAM',
            '',
            number_format($totalAmount, 2, ',', '.'),
            '',
            number_format($totalCommission, 2, ',', '.'),
            '',
            number_format($totalNet, 2, ',', '.'),
        ];

        return $this->generateCsv($rows, 'commission_report');
    }

    /**
     * CSV dosyası oluştur
     */
    protected function generateCsv(array $rows, string $prefix): string
    {
        $filename = $prefix . '_' . $this->account->id . '_' . date('Y-m-d_His') . '.csv';
        $path = 'exports/trendyol/' . $filename;

        $content = '';
        foreach ($rows as $row) {
            // UTF-8 BOM ekle (Excel için)
            if ($content === '') {
                $content = "\xEF\xBB\xBF";
            }
            $content .= implode(';', array_map(function ($cell) {
                // Noktalı virgül ve tırnak karakterlerini escape et
                $cell = str_replace('"', '""', (string) $cell);
                return '"' . $cell . '"';
            }, $row)) . "\n";
        }

        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Durum çevirisi
     */
    protected function translateStatus(string $status): string
    {
        return match ($status) {
            'new', 'pending' => 'Yeni',
            'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal',
            'returned' => 'İade',
            default => $status,
        };
    }
}
