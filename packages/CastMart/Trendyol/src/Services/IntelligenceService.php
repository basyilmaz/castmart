<?php

namespace CastMart\Trendyol\Services;

use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\MarketplaceReview;
use CastMart\Trendyol\Models\IntelligenceAlert;
use CastMart\Trendyol\Models\PriceRule;
use CastMart\Trendyol\Models\BuyboxTracking;
use CastMart\Trendyol\Models\CommissionRate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class IntelligenceService
{
    protected MarketplaceAccount $account;
    protected TrendyolService $trendyolService;

    public function __construct(MarketplaceAccount $account)
    {
        $this->account = $account;
        $this->trendyolService = new TrendyolService($account);
    }

    /**
     * Mağaza sağlık skorunu hesapla
     */
    public function calculateHealthScore(): array
    {
        $buyboxRate = $this->getBuyboxRate();
        $profitMargin = $this->getAverageProfitMargin();
        $stockHealth = $this->getStockHealth();
        $customerRating = $this->getCustomerRating();
        $cargoPerformance = $this->getCargoPerformance();

        // Ağırlıklı ortalama
        $score = (
            ($buyboxRate * 0.30) +
            ($profitMargin * 0.20) +
            ($stockHealth * 0.20) +
            ($customerRating * 20 * 0.15) + // 5 üzerinden 100'e çevir
            ($cargoPerformance * 0.15)
        );

        return [
            'total_score' => round($score),
            'buybox_rate' => $buyboxRate,
            'profit_margin' => $profitMargin,
            'stock_health' => $stockHealth,
            'customer_rating' => $customerRating,
            'cargo_performance' => $cargoPerformance,
            'label' => $this->getScoreLabel($score),
        ];
    }

    /**
     * BuyBox oranını hesapla
     */
    public function getBuyboxRate(): float
    {
        $total = BuyboxTracking::where('marketplace_account_id', $this->account->id)->count();
        if ($total == 0) return 0;

        $won = BuyboxTracking::where('marketplace_account_id', $this->account->id)
            ->where('status', 'won')
            ->count();

        return round(($won / $total) * 100, 1);
    }

    /**
     * Ortalama kar marjını hesapla
     * Son 30 günlük siparişlerin kar marjı ortalaması (gerçek veri hesaplaması)
     */
    public function getAverageProfitMargin(): float
    {
        // Son 30 günlük siparişleri al
        $orders = MarketplaceOrder::where('account_id', $this->account->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('items_data')
            ->get();

        if ($orders->isEmpty()) {
            // Veri yoksa 0 döndür (varsayılan değil, gerçek durum)
            return 0.0;
        }

        $totalRevenue = 0;
        $totalCost = 0;
        $itemCount = 0;

        foreach ($orders as $order) {
            $items = $order->items_data ?? [];
            
            foreach ($items as $item) {
                $salePrice = $item['price'] ?? $item['salePrice'] ?? 0;
                $quantity = $item['quantity'] ?? 1;
                
                // Maliyet hesaplama: Komisyon oranlarını kullan
                $commissionRate = 0.18; // Varsayılan %18
                $cargoRatio = 0.15;     // Varsayılan %15
                
                // Gerçek komisyon oranını bul
                if (!empty($item['category'])) {
                    $rate = CommissionRate::findByCategory($item['category']);
                    if ($rate) {
                        $commissionRate = $rate->commission_rate / 100;
                    }
                }
                
                $lineTotal = $salePrice * $quantity;
                $estimatedCost = $lineTotal * ($commissionRate + $cargoRatio);
                
                $totalRevenue += $lineTotal;
                $totalCost += $estimatedCost;
                $itemCount++;
            }
        }

        if ($totalRevenue == 0) {
            return 0.0;
        }

        // Kar marjı = (Gelir - Maliyet) / Gelir * 100
        $profitMargin = (($totalRevenue - $totalCost) / $totalRevenue) * 100;
        
        return round(max(0, min(100, $profitMargin)), 1);
    }

    /**
     * Stok sağlığını hesapla
     * Kritik stoklu ürün oranına göre 0-100 arası skor (gerçek veri)
     */
    public function getStockHealth(): float
    {
        // Hesaba ait aktif listing'leri al
        $listings = MarketplaceListing::where('account_id', $this->account->id)
            ->where('status', 'active')
            ->with(['product.inventories'])
            ->get();

        if ($listings->isEmpty()) {
            return 100.0; // Ürün yoksa %100 sağlıklı
        }

        $totalProducts = 0;
        $healthyProducts = 0;
        $criticalThreshold = 5; // 5 ve altı kritik stok

        foreach ($listings as $listing) {
            if (!$listing->product) continue;
            
            $totalProducts++;
            
            // Ürünün toplam stokunu hesapla
            $totalStock = 0;
            if ($listing->product->inventories) {
                $totalStock = $listing->product->inventories->sum('qty');
            }

            // Stok durumuna göre sağlık belirle
            if ($totalStock > $criticalThreshold) {
                $healthyProducts++;
            }
        }

        if ($totalProducts == 0) {
            return 100.0;
        }

        // Sağlıklı ürün oranı
        return round(($healthyProducts / $totalProducts) * 100, 1);
    }

    /**
     * Müşteri puanını getir (5 üzerinden)
     * Trendyol'dan scrape edilen veya API'den alınan yorumlardan hesaplanır (gerçek veri)
     */
    public function getCustomerRating(): float
    {
        // marketplace_reviews tablosundan ortalama puan al
        $avgRating = MarketplaceReview::where('account_id', $this->account->id)
            ->where('created_at', '>=', now()->subDays(90)) // Son 90 gün
            ->avg('rating');

        if ($avgRating === null) {
            // Veri yoksa Trendyol API'den mağaza bilgisi almayı dene
            try {
                $sellerInfo = $this->trendyolService->getSellerInfo();
                if ($sellerInfo && isset($sellerInfo['sellerScore'])) {
                    return round($sellerInfo['sellerScore'], 1);
                }
            } catch (\Exception $e) {
                // API hatası durumunda 0 döndür
            }
            
            return 0.0; // Veri yok
        }

        return round($avgRating, 1);
    }

    /**
     * Kargo performansını hesapla (0-100)
     * Zamanında gönderim ve teslimat oranlarına göre (gerçek veri)
     */
    public function getCargoPerformance(): float
    {
        // Son 30 günlük kargoya verilmiş veya teslim edilmiş siparişler
        $orders = MarketplaceOrder::where('account_id', $this->account->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereIn('status', ['shipped', 'delivered'])
            ->get();

        if ($orders->isEmpty()) {
            return 100.0; // Sipariş yoksa %100
        }

        $totalOrders = $orders->count();
        $onTimeShipped = 0;
        $onTimeDelivered = 0;
        $shippedCount = 0;
        $deliveredCount = 0;

        foreach ($orders as $order) {
            // Kargoya verilmiş siparişler
            if ($order->shipped_at) {
                $shippedCount++;
                
                // Sipariş oluşturma ve kargoya verme arasındaki süre
                // Trendyol'da genellikle 48 saat içinde kargoya verilmeli
                $hoursToShip = $order->created_at->diffInHours($order->shipped_at);
                if ($hoursToShip <= 48) {
                    $onTimeShipped++;
                }
            }

            // Teslim edilmiş siparişler
            if ($order->delivered_at && $order->shipped_at) {
                $deliveredCount++;
                
                // Kargo süresi: genellikle 3-5 gün içinde teslim beklenir
                $daysToDeliver = $order->shipped_at->diffInDays($order->delivered_at);
                if ($daysToDeliver <= 5) {
                    $onTimeDelivered++;
                }
            }
        }

        // Performans hesaplama
        $shippingScore = $shippedCount > 0 ? ($onTimeShipped / $shippedCount) * 100 : 100;
        $deliveryScore = $deliveredCount > 0 ? ($onTimeDelivered / $deliveredCount) * 100 : 100;

        // Ağırlıklı ortalama: Gönderim %60, Teslimat %40
        $performance = ($shippingScore * 0.6) + ($deliveryScore * 0.4);

        return round($performance, 1);
    }

    /**
     * Skor etiketini getir
     */
    protected function getScoreLabel(float $score): string
    {
        if ($score >= 80) return 'Mükemmel';
        if ($score >= 60) return 'İyi Durumda';
        if ($score >= 40) return 'Dikkat Gerekli';
        return 'Kritik';
    }

    /**
     * Aktif uyarıları getir
     */
    public function getActiveAlerts(): Collection
    {
        return IntelligenceAlert::where('marketplace_account_id', $this->account->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }

    /**
     * Uyarı sayılarını getir
     */
    public function getAlertCounts(): array
    {
        $alerts = IntelligenceAlert::where('marketplace_account_id', $this->account->id)
            ->active()
            ->get();

        return [
            'critical' => $alerts->where('type', 'critical')->count(),
            'warning' => $alerts->where('type', 'warning')->count(),
            'opportunity' => $alerts->where('type', 'opportunity')->count(),
            'trend' => $alerts->where('type', 'trend')->count(),
            'unread' => $alerts->where('is_read', false)->count(),
        ];
    }

    /**
     * BuyBox kaybı uyarısı oluştur
     */
    public function createBuyboxLostAlert(string $sku, float $ourPrice, float $competitorPrice): void
    {
        IntelligenceAlert::create([
            'marketplace_account_id' => $this->account->id,
            'type' => 'critical',
            'category' => 'buybox',
            'title' => 'BuyBox Kaybedildi: ' . $sku,
            'description' => sprintf(
                'Rakip satıcı fiyatı %.2f₺\'ye düşürdü. Mevcut fiyatınız: %.2f₺',
                $competitorPrice,
                $ourPrice
            ),
            'product_sku' => $sku,
            'data' => [
                'our_price' => $ourPrice,
                'competitor_price' => $competitorPrice,
                'difference' => $ourPrice - $competitorPrice,
            ],
            'action_type' => 'update_price',
        ]);
    }

    /**
     * Stok kritik uyarısı oluştur
     */
    public function createStockCriticalAlert(string $sku, int $currentStock, float $dailySales): void
    {
        $daysLeft = $dailySales > 0 ? floor($currentStock / $dailySales) : 999;

        IntelligenceAlert::create([
            'marketplace_account_id' => $this->account->id,
            'type' => 'warning',
            'category' => 'stock',
            'title' => 'Stok Kritik Seviyede: ' . $sku,
            'description' => sprintf(
                'Kalan stok: %d adet. Tahmini tükenme: %d gün içinde.',
                $currentStock,
                $daysLeft
            ),
            'product_sku' => $sku,
            'data' => [
                'current_stock' => $currentStock,
                'daily_sales' => $dailySales,
                'days_left' => $daysLeft,
            ],
            'action_type' => 'update_stock',
        ]);
    }

    /**
     * Fırsat uyarısı oluştur
     */
    public function createOpportunityAlert(string $title, string $description, ?string $sku = null): void
    {
        IntelligenceAlert::create([
            'marketplace_account_id' => $this->account->id,
            'type' => 'opportunity',
            'category' => 'buybox',
            'title' => $title,
            'description' => $description,
            'product_sku' => $sku,
        ]);
    }

    /**
     * Aktif fiyat kurallarını getir
     */
    public function getActiveRules(): Collection
    {
        return PriceRule::where('marketplace_account_id', $this->account->id)
            ->active()
            ->orderBy('priority', 'desc')
            ->get();
    }

    /**
     * Fiyat kuralını uygula
     */
    public function applyPriceRule(PriceRule $rule, string $sku, float $currentPrice, float $competitorPrice): ?float
    {
        $newPrice = null;

        switch ($rule->action) {
            case 'match_minus':
                $newPrice = $competitorPrice - 0.01;
                break;
            case 'decrease_percent':
                $newPrice = $currentPrice * (1 - ($rule->action_value / 100));
                break;
            case 'increase_percent':
                $newPrice = $currentPrice * (1 + ($rule->action_value / 100));
                break;
            case 'set_price':
                $newPrice = $rule->action_value;
                break;
        }

        if ($newPrice) {
            $rule->incrementTriggerCount();
        }

        return $newPrice ? round($newPrice, 2) : null;
    }

    /**
     * BuyBox takip verilerini güncelle
     */
    public function updateBuyboxTracking(array $products): void
    {
        foreach ($products as $product) {
            $status = 'won';
            $winChance = 100;

            if (isset($product['competitor_price']) && $product['competitor_price'] > 0) {
                $diff = $product['our_price'] - $product['competitor_price'];
                $diffPercent = ($diff / $product['competitor_price']) * 100;

                if ($diffPercent > 0) {
                    $status = 'lost';
                    $winChance = max(0, 50 - ($diffPercent * 5));
                } elseif ($diffPercent > -5) {
                    $status = 'risk';
                    $winChance = 50 + (abs($diffPercent) * 10);
                } else {
                    $status = 'won';
                    $winChance = min(100, 80 + (abs($diffPercent) * 2));
                }
            }

            BuyboxTracking::updateOrCreate(
                [
                    'marketplace_account_id' => $this->account->id,
                    'product_sku' => $product['sku'],
                ],
                [
                    'barcode' => $product['barcode'] ?? null,
                    'our_price' => $product['our_price'],
                    'competitor_price' => $product['competitor_price'] ?? null,
                    'competitor_seller' => $product['competitor_seller'] ?? null,
                    'status' => $status,
                    'win_chance' => round($winChance),
                    'last_checked_at' => now(),
                ]
            );
        }
    }

    /**
     * Haftalık performans istatistiklerini getir
     */
    public function getWeeklyStats(): array
    {
        $now = now();
        $weekAgo = now()->subDays(7);
        $twoWeeksAgo = now()->subDays(14);

        // Bu hafta
        $thisWeekOrders = MarketplaceOrder::where('account_id', $this->account->id)
            ->where('created_at', '>=', $weekAgo)
            ->count();

        // Geçen hafta
        $lastWeekOrders = MarketplaceOrder::where('account_id', $this->account->id)
            ->where('created_at', '>=', $twoWeeksAgo)
            ->where('created_at', '<', $weekAgo)
            ->count();

        // Değişim yüzdesi
        $ordersChange = $lastWeekOrders > 0 
            ? round((($thisWeekOrders - $lastWeekOrders) / $lastWeekOrders) * 100, 1)
            : ($thisWeekOrders > 0 ? 100 : 0);

        // Bu hafta BuyBox
        $thisWeekBuybox = BuyboxTracking::where('marketplace_account_id', $this->account->id)
            ->where('status', 'won')
            ->count();

        $totalBuybox = BuyboxTracking::where('marketplace_account_id', $this->account->id)->count();
        $buyboxRate = $totalBuybox > 0 ? round(($thisWeekBuybox / $totalBuybox) * 100, 1) : 0;

        return [
            'this_week_orders' => $thisWeekOrders,
            'last_week_orders' => $lastWeekOrders,
            'orders_change' => $ordersChange,
            'buybox_rate' => $buyboxRate,
            'buybox_won' => $thisWeekBuybox,
            'buybox_total' => $totalBuybox,
        ];
    }
}
