<?php

namespace CastMart\Trendyol\Services;

use CastMart\Marketplace\Contracts\MarketplaceInterface;
use CastMart\Marketplace\Models\MarketplaceAccount;
use CastMart\Marketplace\Models\MarketplaceListing;
use CastMart\Marketplace\Models\MarketplaceOrder;
use CastMart\Marketplace\Models\CustomerQuestion;

class TrendyolService implements MarketplaceInterface
{
    private TrendyolApiClient $client;
    private MarketplaceAccount $account;

    public function __construct(MarketplaceAccount $account)
    {
        $this->account = $account;
        $this->client = TrendyolApiClient::fromAccount($account);
    }

    public function testConnection(): bool|array
    {
        return $this->client->testConnection();
    }

    public function getProducts(array $params = []): array
    {
        return $this->client->getProducts($params);
    }

    public function createProduct(array $productData): array
    {
        return $this->client->createProduct($productData);
    }

    public function updateProduct(string $externalId, array $productData): array
    {
        // Trendyol ürün güncelleme barcode üzerinden yapılır
        return $this->client->createProduct($productData);
    }

    public function updateInventory(array|string $items, int $stock = 0, float $price = 0): array|bool
    {
        // Tek ürün için eski format desteği
        if (is_string($items)) {
            $items = [[
                'barcode' => $items,
                'quantity' => $stock,
                'salePrice' => $price,
                'listPrice' => $price,
            ]];
        }

        $result = $this->client->updatePriceAndInventory($items);
        return is_string($items) ? isset($result['batchRequestId']) : $result;
    }

    public function getOrders(array $filters = []): array
    {
        $response = $this->client->getShipmentPackages($filters);
        return $response['content'] ?? [];
    }

    public function updateOrderStatus(string $orderId, string $status, array $data = []): bool
    {
        // Trendyol'da sipariş durumu güncelleme kargo üzerinden yapılır
        return true;
    }

    public function sendTrackingNumber(string $orderId, string $trackingNumber, string $cargoProvider): bool
    {
        $result = $this->client->updateTrackingNumber((int)$orderId, $trackingNumber);
        return !isset($result['errors']);
    }

    public function getQuestions(array $filters = []): array
    {
        $response = $this->client->getQuestions($filters);
        return $response['content'] ?? [];
    }

    public function answerQuestion(string $questionId, string $answer): bool
    {
        $result = $this->client->answerQuestion((int)$questionId, $answer);
        return !isset($result['errors']);
    }

    public function getCategories(): array
    {
        $response = $this->client->getCategories();
        return $response['categories'] ?? [];
    }

    public function getBrands(): array
    {
        $response = $this->client->getBrands();
        return $response['brands'] ?? [];
    }

    public function getShipmentProviders(): array
    {
        return $this->client->getShipmentProviders();
    }

    public function syncOrders(): int
    {
        $orders = $this->getOrders([
            'status' => 'Created',
            'size' => 100,
        ]);

        $syncedCount = 0;

        foreach ($orders as $orderData) {
            $existing = MarketplaceOrder::where('account_id', $this->account->id)
                ->where('external_order_id', $orderData['orderNumber'] ?? $orderData['id'])
                ->first();

            if (!$existing) {
                MarketplaceOrder::create([
                    'account_id' => $this->account->id,
                    'external_order_id' => $orderData['orderNumber'] ?? $orderData['id'],
                    'external_order_number' => $orderData['orderNumber'] ?? null,
                    'package_id' => $orderData['id'] ?? null,
                    'status' => $this->mapOrderStatus($orderData['status'] ?? 'Created'),
                    'cargo_provider' => $orderData['cargoProviderName'] ?? null,
                    'tracking_number' => $orderData['cargoTrackingNumber'] ?? null,
                    'order_data' => $orderData,
                    'customer_data' => $orderData['customerAddress'] ?? null,
                    'items_data' => $orderData['lines'] ?? null,
                ]);
                $syncedCount++;
            }
        }

        $this->account->update(['last_sync_at' => now()]);

        return $syncedCount;
    }

    public function syncQuestions(): int
    {
        $questions = $this->getQuestions([
            'status' => 'WAITING_FOR_ANSWER',
            'size' => 100,
        ]);

        $syncedCount = 0;

        foreach ($questions as $questionData) {
            $existing = CustomerQuestion::where('account_id', $this->account->id)
                ->where('external_question_id', $questionData['id'])
                ->first();

            if (!$existing) {
                CustomerQuestion::create([
                    'account_id' => $this->account->id,
                    'external_question_id' => $questionData['id'],
                    'external_product_id' => $questionData['productId'] ?? null,
                    'question_text' => $questionData['text'] ?? '',
                    'status' => CustomerQuestion::STATUS_PENDING,
                    'asked_at' => isset($questionData['createdDate']) 
                        ? \Carbon\Carbon::createFromTimestampMs($questionData['createdDate']) 
                        : now(),
                ]);
                $syncedCount++;
            }
        }

        return $syncedCount;
    }

    private function mapOrderStatus(string $trendyolStatus): string
    {
        return match ($trendyolStatus) {
            'Created' => MarketplaceOrder::STATUS_NEW,
            'Picking' => MarketplaceOrder::STATUS_PROCESSING,
            'Shipped' => MarketplaceOrder::STATUS_SHIPPED,
            'Delivered' => MarketplaceOrder::STATUS_DELIVERED,
            'Cancelled' => MarketplaceOrder::STATUS_CANCELLED,
            'Returned' => MarketplaceOrder::STATUS_RETURNED,
            default => MarketplaceOrder::STATUS_NEW,
        };
    }

    // ===== GELİŞMİŞ ÖZELLİKLER =====

    public function getBatchRequestResult(string $batchRequestId): array
    {
        return $this->client->getBatchRequestResult($batchRequestId);
    }

    public function getCategoryAttributes(int $categoryId): array
    {
        return $this->client->getCategoryAttributesDetailed($categoryId);
    }

    public function searchCategories(string $keyword): array
    {
        return $this->client->searchCategories($keyword);
    }

    public function checkBuybox(string $barcode): array
    {
        $result = $this->client->checkBuybox($barcode);
        if (!empty($result['content'])) {
            $product = $result['content'][0];
            return [
                'hasActiveCampaign' => $product['hasActiveCampaign'] ?? false,
                'onSale' => $product['onSale'] ?? false,
                'locked' => $product['locked'] ?? false,
                'lockReason' => $product['lockReason'] ?? null,
                'salePrice' => $product['salePrice'] ?? 0,
                'listPrice' => $product['listPrice'] ?? 0,
            ];
        }
        return ['error' => 'Ürün bulunamadı'];
    }

    public function getClaims(array $params = []): array
    {
        return $this->client->getClaims($params);
    }

    public function approveClaim(int $claimId): bool
    {
        $result = $this->client->approveClaim($claimId);
        return !isset($result['errors']);
    }

    public function getSupplierAddresses(): array
    {
        return $this->client->getSupplierAddresses();
    }

    /**
     * Satıcı/mağaza bilgilerini getir
     * Satıcı puanı, performans metrikleri vb.
     */
    public function getSellerInfo(): ?array
    {
        try {
            // Trendyol API'den supplier bilgisi al
            $addresses = $this->client->getSupplierAddresses();
            
            if (!empty($addresses)) {
                // Temel bilgileri döndür
                return [
                    'sellerId' => $this->account->id,
                    'sellerName' => $this->account->name,
                    // Trendyol seller score henüz public API'de yok
                    // Bu değer scraping ile alınabilir
                    'sellerScore' => null,
                    'addresses' => $addresses,
                ];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Ürün satış istatistiklerini getir
     */
    public function getProductSalesStats(string $barcode, int $days = 30): array
    {
        // Son X günlük siparişlerden satış istatistiği hesapla
        $orders = MarketplaceOrder::where('account_id', $this->account->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->whereNotNull('items_data')
            ->get();

        $totalQuantity = 0;
        $totalRevenue = 0;
        $orderCount = 0;

        foreach ($orders as $order) {
            $items = $order->items_data ?? [];
            foreach ($items as $item) {
                $itemBarcode = $item['barcode'] ?? $item['productBarcode'] ?? '';
                if ($itemBarcode === $barcode) {
                    $quantity = $item['quantity'] ?? 1;
                    $price = $item['price'] ?? $item['salePrice'] ?? 0;
                    
                    $totalQuantity += $quantity;
                    $totalRevenue += ($price * $quantity);
                    $orderCount++;
                }
            }
        }

        return [
            'barcode' => $barcode,
            'period_days' => $days,
            'total_quantity' => $totalQuantity,
            'total_revenue' => round($totalRevenue, 2),
            'order_count' => $orderCount,
            'daily_average' => $days > 0 ? round($totalQuantity / $days, 2) : 0,
        ];
    }
}

