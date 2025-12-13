<?php

namespace CastMart\Trendyol\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class TrendyolApiClient
{
    private string $baseUrl = 'https://apigw.trendyol.com';
    private string $supplierId;
    private string $apiKey;
    private string $apiSecret;

    public function __construct(string $supplierId, string $apiKey, string $apiSecret)
    {
        $this->supplierId = $supplierId;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    public static function fromAccount($account): self
    {
        $credentials = $account->credentials;
        return new self(
            $credentials['supplier_id'],
            $credentials['api_key'],
            $credentials['api_secret']
        );
    }

    private function getHeaders(): array
    {
        $auth = base64_encode("{$this->apiKey}:{$this->apiSecret}");
        return [
            'Authorization' => "Basic {$auth}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => "{$this->supplierId} - SelfIntegration",
        ];
    }

    public function testConnection(): bool|array
    {
        try {
            $response = $this->get("/integration/product/sellers/{$this->supplierId}/products", ['size' => 1]);
            
            if ($response->successful()) {
                return true;
            }
            
            // Hata detaylarını döndür
            Log::error('Trendyol connection test failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'supplier_id' => $this->supplierId,
            ]);
            
            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $response->json('errors') ?? $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Trendyol connection test exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ===== ÜRÜN İŞLEMLERİ =====

    public function getProducts(array $params = []): array
    {
        $defaults = ['size' => 50, 'page' => 0];
        $params = array_merge($defaults, $params);
        
        $response = $this->get("/integration/product/sellers/{$this->supplierId}/products", $params);
        return $response->json();
    }

    public function createProduct(array $productData): array
    {
        $response = $this->post("/integration/product/sellers/{$this->supplierId}/products", ['items' => [$productData]]);
        return $response->json();
    }

    public function createProducts(array $products): array
    {
        $response = $this->post("/integration/product/sellers/{$this->supplierId}/products", ['items' => $products]);
        return $response->json();
    }

    public function updatePriceAndInventory(array $items): array
    {
        $response = $this->post("/integration/product/sellers/{$this->supplierId}/products/price-and-inventory", ['items' => $items]);
        return $response->json();
    }

    public function deleteProduct(string $barcode): array
    {
        $response = $this->delete("/integration/product/sellers/{$this->supplierId}/products", ['items' => [['barcode' => $barcode]]]);
        return $response->json();
    }

    // ===== SİPARİŞ İŞLEMLERİ =====

    public function getOrders(array $params = []): array
    {
        $defaults = [
            'size' => 50,
            'page' => 0,
            'orderByField' => 'CreatedDate',
            'orderByDirection' => 'DESC',
        ];
        $params = array_merge($defaults, $params);

        $response = $this->get("/integration/order/sellers/{$this->supplierId}/orders", $params);
        return $response->json();
    }

    public function getShipmentPackages(array $params = []): array
    {
        $response = $this->get("/integration/order/sellers/{$this->supplierId}/orders", $params);
        return $response->json();
    }

    public function updateTrackingNumber(int $shipmentPackageId, string $trackingNumber): array
    {
        $response = $this->put("/integration/order/sellers/{$this->supplierId}/shipmentPackages/{$shipmentPackageId}/updateTrackingNumber", [
            'trackingNumber' => $trackingNumber,
        ]);
        return $response->json();
    }

    public function changeCargoProvider(int $shipmentPackageId, int $cargoProviderId): array
    {
        $response = $this->put("/integration/order/sellers/{$this->supplierId}/shipmentPackages/{$shipmentPackageId}/changeCargoProvider", [
            'cargoProviderId' => $cargoProviderId,
        ]);
        return $response->json();
    }

    public function cancelOrderItem(int $shipmentPackageId, array $lines): array
    {
        $response = $this->put("/integration/order/sellers/{$this->supplierId}/shipmentPackages/{$shipmentPackageId}/cancel", [
            'lines' => $lines,
        ]);
        return $response->json();
    }

    // ===== MÜŞTERİ SORULARI =====

    public function getQuestions(array $params = []): array
    {
        $defaults = ['size' => 50, 'page' => 0];
        $params = array_merge($defaults, $params);

        $response = $this->get("/integration/qa/sellers/{$this->supplierId}/questions", $params);
        return $response->json();
    }

    public function answerQuestion(int $questionId, string $answer): array
    {
        $response = $this->post("/integration/qa/sellers/{$this->supplierId}/questions/{$questionId}/answers", [
            'text' => $answer,
        ]);
        return $response->json();
    }

    // ===== KATEGORİ VE MARKA =====

    public function getCategories(): array
    {
        $response = $this->get('/integration/product/product-categories');
        return $response->json();
    }

    public function getBrands(array $params = []): array
    {
        $response = $this->get('/integration/product/brands', $params);
        return $response->json();
    }

    public function getCategoryAttributes(int $categoryId): array
    {
        $response = $this->get("/integration/product/product-categories/{$categoryId}/attributes");
        return $response->json();
    }

    // ===== KARGO FİRMALARI =====

    public function getShipmentProviders(): array
    {
        $response = $this->get('/integration/order/shipment-providers');
        return $response->json();
    }

    // ===== BATCH İŞLEM DURUMU =====

    public function getBatchRequestResult(string $batchRequestId): array
    {
        $response = $this->get("/integration/product/sellers/{$this->supplierId}/products/batch-requests/{$batchRequestId}");
        return $response->json();
    }

    // ===== KATEGORİ ÖZELLİKLERİ =====

    public function getCategoryAttributesDetailed(int $categoryId): array
    {
        $response = $this->get("/integration/product/product-categories/{$categoryId}/attributes");
        return $response->json();
    }

    public function searchCategories(string $keyword): array
    {
        $response = $this->get("/integration/product/product-categories", ['name' => $keyword]);
        return $response->json();
    }

    // ===== BUYBOX KONTROLÜ =====

    public function checkBuybox(string $barcode): array
    {
        $response = $this->get("/integration/product/sellers/{$this->supplierId}/products", [
            'barcode' => $barcode,
            'size' => 1
        ]);
        return $response->json();
    }

    // ===== İADE SERVİSLERİ =====

    public function getClaims(array $params = []): array
    {
        $defaults = ['size' => 50, 'page' => 0];
        $params = array_merge($defaults, $params);
        
        $response = $this->get("/integration/order/sellers/{$this->supplierId}/claims", $params);
        return $response->json();
    }

    public function approveClaim(int $claimId, array $data = []): array
    {
        $response = $this->put("/integration/order/sellers/{$this->supplierId}/claims/{$claimId}/approve", $data);
        return $response->json();
    }

    // ===== ADRES BİLGİLERİ =====

    public function getSupplierAddresses(): array
    {
        $response = $this->get("/integration/product/sellers/{$this->supplierId}/addresses");
        return $response->json();
    }

    // ===== HTTP METODLARI =====

    private function get(string $endpoint, array $params = []): Response
    {
        return Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get($this->baseUrl . $endpoint, $params);
    }

    private function post(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->post($this->baseUrl . $endpoint, $data);
    }

    private function put(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->put($this->baseUrl . $endpoint, $data);
    }

    private function delete(string $endpoint, array $data = []): Response
    {
        return Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->delete($this->baseUrl . $endpoint, $data);
    }
}
