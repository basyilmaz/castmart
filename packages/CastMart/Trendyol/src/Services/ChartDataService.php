<?php

namespace CastMart\Trendyol\Services;

use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Trendyol\Models\BuyboxTracking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChartDataService
{
    protected ?MarketplaceAccount $account;

    public function __construct(?MarketplaceAccount $account = null)
    {
        $this->account = $account;
    }

    /**
     * Satış grafiği verileri (son 30 gün)
     */
    public function getSalesChartData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = MarketplaceOrder::where('status', 'delivered')
            ->where('created_at', '>=', $startDate);
        
        if ($this->account) {
            $query->where('account_id', $this->account->id);
        }

        $sales = $query->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $counts = [];
        $totals = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d M');
            $counts[] = $sales->get($date)?->count ?? 0;
            $totals[] = $sales->get($date)?->total ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Sipariş Sayısı',
                    'data' => $counts,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Ciro (₺)',
                    'data' => $totals,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y1',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Kategori bazlı satış dağılımı (Pie Chart)
     */
    public function getCategorySalesData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = MarketplaceOrder::where('status', 'delivered')
            ->where('created_at', '>=', $startDate);
        
        if ($this->account) {
            $query->where('account_id', $this->account->id);
        }

        // items_data JSON'dan kategori bilgisini çek
        $orders = $query->get();
        $categoryTotals = [];

        foreach ($orders as $order) {
            $items = $order->items_data ?? [];
            foreach ($items as $item) {
                $category = $item['categoryName'] ?? 'Diğer';
                $price = $item['price'] ?? 0;
                $categoryTotals[$category] = ($categoryTotals[$category] ?? 0) + $price;
            }
        }

        arsort($categoryTotals);
        $top5 = array_slice($categoryTotals, 0, 5, true);
        $others = array_sum(array_slice($categoryTotals, 5));
        
        if ($others > 0) {
            $top5['Diğer'] = $others;
        }

        return [
            'labels' => array_keys($top5),
            'datasets' => [[
                'data' => array_values($top5),
                'backgroundColor' => [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)',
                    'rgb(107, 114, 128)',
                ],
            ]],
        ];
    }

    /**
     * Buybox performans grafiği
     */
    public function getBuyboxChartData(int $days = 14): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = BuyboxTracking::where('created_at', '>=', $startDate);
        
        if ($this->account) {
            $query->where('marketplace_account_id', $this->account->id);
        }

        $data = $query->selectRaw('DATE(created_at) as date, 
            SUM(CASE WHEN is_winner = 1 THEN 1 ELSE 0 END) as winners,
            COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $winRates = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d M');
            
            $dayData = $data->get($date);
            if ($dayData && $dayData->total > 0) {
                $winRates[] = round(($dayData->winners / $dayData->total) * 100, 1);
            } else {
                $winRates[] = null;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Buybox Kazanma Oranı (%)',
                'data' => $winRates,
                'borderColor' => 'rgb(139, 92, 246)',
                'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                'fill' => true,
                'tension' => 0.4,
            ]],
        ];
    }

    /**
     * Stok durum grafiği (Bar Chart)
     */
    public function getStockStatusData(): array
    {
        $query = MarketplaceListing::query();
        
        if ($this->account) {
            $query->where('account_id', $this->account->id);
        }

        $outOfStock = (clone $query)->where('stock', 0)->count();
        $lowStock = (clone $query)->whereBetween('stock', [1, 5])->count();
        $normalStock = (clone $query)->whereBetween('stock', [6, 20])->count();
        $highStock = (clone $query)->where('stock', '>', 20)->count();

        return [
            'labels' => ['Stok Yok', 'Kritik (1-5)', 'Normal (6-20)', 'Yüksek (20+)'],
            'datasets' => [[
                'label' => 'Ürün Sayısı',
                'data' => [$outOfStock, $lowStock, $normalStock, $highStock],
                'backgroundColor' => [
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)',
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                ],
            ]],
        ];
    }

    /**
     * Komisyon analizi grafiği
     */
    public function getCommissionChartData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = MarketplaceOrder::where('status', 'delivered')
            ->where('created_at', '>=', $startDate);
        
        if ($this->account) {
            $query->where('account_id', $this->account->id);
        }

        $data = $query->selectRaw('DATE(created_at) as date, 
            SUM(total_amount) as revenue,
            SUM(total_amount * 0.12) as commission')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $labels = [];
        $revenues = [];
        $commissions = [];
        $profits = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d M');
            
            $dayData = $data->get($date);
            $revenue = $dayData?->revenue ?? 0;
            $commission = $dayData?->commission ?? 0;
            
            $revenues[] = $revenue;
            $commissions[] = $commission;
            $profits[] = $revenue - $commission;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Ciro',
                    'data' => $revenues,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Komisyon',
                    'data' => $commissions,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'type' => 'bar',
                ],
                [
                    'label' => 'Net Kazanç',
                    'data' => $profits,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'type' => 'line',
                    'tension' => 0.4,
                ],
            ],
        ];
    }

    /**
     * Sipariş durumları (Doughnut Chart)
     */
    public function getOrderStatusData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = MarketplaceOrder::where('created_at', '>=', $startDate);
        
        if ($this->account) {
            $query->where('account_id', $this->account->id);
        }

        $statuses = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'new' => 'Yeni',
            'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargoda',
            'delivered' => 'Teslim Edildi',
            'cancelled' => 'İptal',
            'returned' => 'İade',
        ];

        $statusColors = [
            'new' => 'rgb(59, 130, 246)',
            'processing' => 'rgb(245, 158, 11)',
            'shipped' => 'rgb(139, 92, 246)',
            'delivered' => 'rgb(16, 185, 129)',
            'cancelled' => 'rgb(239, 68, 68)',
            'returned' => 'rgb(107, 114, 128)',
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $status => $count) {
            $labels[] = $statusLabels[$status] ?? $status;
            $data[] = $count;
            $colors[] = $statusColors[$status] ?? 'rgb(107, 114, 128)';
        }

        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => $colors,
            ]],
        ];
    }

    /**
     * Saatlik satış dağılımı (Polar Area)
     */
    public function getHourlySalesData(int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $query = MarketplaceOrder::where('status', 'delivered')
            ->where('created_at', '>=', $startDate);
        
        if ($this->account) {
            $query->where('account_id', $this->account->id);
        }

        $hourlyData = $query->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();

        // Saatleri grupla: Sabah, Öğle, Akşam, Gece
        $periods = [
            'Gece (00-06)' => array_sum(array_intersect_key($hourlyData, array_flip(range(0, 5)))),
            'Sabah (06-12)' => array_sum(array_intersect_key($hourlyData, array_flip(range(6, 11)))),
            'Öğle (12-18)' => array_sum(array_intersect_key($hourlyData, array_flip(range(12, 17)))),
            'Akşam (18-24)' => array_sum(array_intersect_key($hourlyData, array_flip(range(18, 23)))),
        ];

        return [
            'labels' => array_keys($periods),
            'datasets' => [[
                'data' => array_values($periods),
                'backgroundColor' => [
                    'rgba(107, 114, 128, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                ],
            ]],
        ];
    }

    /**
     * Tüm dashboard verilerini tek seferde al
     */
    public function getAllDashboardData(): array
    {
        return [
            'salesChart' => $this->getSalesChartData(30),
            'categoryChart' => $this->getCategorySalesData(30),
            'buyboxChart' => $this->getBuyboxChartData(14),
            'stockChart' => $this->getStockStatusData(),
            'commissionChart' => $this->getCommissionChartData(30),
            'orderStatusChart' => $this->getOrderStatusData(30),
            'hourlySalesChart' => $this->getHourlySalesData(7),
        ];
    }
}
