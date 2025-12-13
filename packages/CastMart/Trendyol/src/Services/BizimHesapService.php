<?php

namespace CastMart\Trendyol\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BizimHesapService
{
    private string $apiKey;
    private string $apiSecret;
    private string $companyId;
    private string $baseUrl = 'https://api.bizimhesap.com/api';
    
    public function __construct(array $credentials)
    {
        $this->apiKey = $credentials['api_key'] ?? '';
        $this->apiSecret = $credentials['api_secret'] ?? '';
        $this->companyId = $credentials['company_id'] ?? '';
    }

    /**
     * API bağlantısını test et
     */
    public function testConnection(): array
    {
        try {
            $response = $this->get('/company/info');
            
            if ($response['success'] ?? false) {
                return [
                    'success' => true,
                    'message' => 'BizimHesap bağlantısı başarılı!',
                    'company' => $response['data'] ?? null
                ];
            }
            
            return [
                'success' => false,
                'message' => $response['message'] ?? 'Bağlantı başarısız.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Bağlantı hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sipariş/Fatura ekle
     */
    public function createInvoice(array $data): array
    {
        $invoiceData = [
            'type' => $data['type'] ?? 'SALES', // SALES, PURCHASE, RETURN
            'invoiceType' => $data['invoice_type'] ?? 'E_ARSIV', // E_FATURA, E_ARSIV
            'date' => $data['date'] ?? now()->format('Y-m-d'),
            'dueDate' => $data['due_date'] ?? now()->addDays(30)->format('Y-m-d'),
            'currency' => $data['currency'] ?? 'TRY',
            'description' => $data['description'] ?? '',
            
            // Müşteri bilgileri
            'customer' => [
                'name' => $data['customer_name'] ?? '',
                'email' => $data['customer_email'] ?? '',
                'phone' => $data['customer_phone'] ?? '',
                'taxNumber' => $data['customer_tax_number'] ?? '',
                'taxOffice' => $data['customer_tax_office'] ?? '',
                'address' => $data['customer_address'] ?? '',
                'city' => $data['customer_city'] ?? '',
                'country' => $data['customer_country'] ?? 'Türkiye',
            ],
            
            // Fatura kalemleri
            'items' => array_map(function ($item) {
                return [
                    'productCode' => $item['sku'] ?? '',
                    'description' => $item['name'] ?? '',
                    'quantity' => $item['quantity'] ?? 1,
                    'unitPrice' => $item['unit_price'] ?? 0,
                    'vatRate' => $item['vat_rate'] ?? 20,
                    'discount' => $item['discount'] ?? 0,
                ];
            }, $data['items'] ?? []),
            
            // Ek bilgiler
            'orderNumber' => $data['order_number'] ?? '',
            'notes' => $data['notes'] ?? '',
        ];

        try {
            $response = $this->post('/invoices', $invoiceData);
            
            if ($response['success'] ?? false) {
                return [
                    'success' => true,
                    'message' => 'Fatura oluşturuldu.',
                    'invoice_id' => $response['data']['id'] ?? null,
                    'invoice_number' => $response['data']['invoiceNumber'] ?? null,
                ];
            }
            
            return [
                'success' => false,
                'message' => $response['message'] ?? 'Fatura oluşturulamadı.',
                'errors' => $response['errors'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('BizimHesap fatura oluşturma hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Fatura hatası: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Trendyol siparişinden fatura oluştur
     */
    public function createInvoiceFromTrendyolOrder(array $order): array
    {
        $items = [];
        foreach ($order['lines'] ?? [] as $line) {
            $items[] = [
                'sku' => $line['merchantSku'] ?? $line['barcode'] ?? '',
                'name' => $line['productName'] ?? '',
                'quantity' => $line['quantity'] ?? 1,
                'unit_price' => $line['price'] ?? 0,
                'vat_rate' => $line['vatBaseAmount'] > 0 ? 20 : 0,
                'discount' => $line['discount'] ?? 0,
            ];
        }

        $customerAddress = $order['shipmentAddress'] ?? $order['invoiceAddress'] ?? [];
        
        return $this->createInvoice([
            'type' => 'SALES',
            'invoice_type' => $this->isCompany($order) ? 'E_FATURA' : 'E_ARSIV',
            'date' => isset($order['orderDate']) ? date('Y-m-d', $order['orderDate'] / 1000) : now()->format('Y-m-d'),
            'customer_name' => $customerAddress['fullName'] ?? '',
            'customer_email' => $order['customerEmail'] ?? '',
            'customer_phone' => $customerAddress['phone'] ?? '',
            'customer_tax_number' => $order['taxNumber'] ?? '',
            'customer_tax_office' => $order['taxOffice'] ?? '',
            'customer_address' => ($customerAddress['address1'] ?? '') . ' ' . ($customerAddress['address2'] ?? ''),
            'customer_city' => $customerAddress['city'] ?? '',
            'items' => $items,
            'order_number' => $order['orderNumber'] ?? '',
            'notes' => 'Trendyol Sipariş: ' . ($order['orderNumber'] ?? ''),
        ]);
    }

    /**
     * Fatura listesi al
     */
    public function getInvoices(array $params = []): array
    {
        $query = [
            'page' => $params['page'] ?? 1,
            'limit' => $params['limit'] ?? 50,
            'startDate' => $params['start_date'] ?? now()->subMonth()->format('Y-m-d'),
            'endDate' => $params['end_date'] ?? now()->format('Y-m-d'),
        ];

        try {
            $response = $this->get('/invoices', $query);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('BizimHesap fatura listesi hatası: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fatura detayı al
     */
    public function getInvoice(string $invoiceId): ?array
    {
        try {
            $response = $this->get("/invoices/{$invoiceId}");
            return $response['data'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fatura PDF indir
     */
    public function downloadInvoicePdf(string $invoiceId): ?string
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/invoices/{$invoiceId}/pdf");
            
            if ($response->successful()) {
                return $response->body();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Müşteri oluştur veya güncelle
     */
    public function upsertCustomer(array $data): array
    {
        try {
            $response = $this->post('/customers', [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'taxNumber' => $data['tax_number'] ?? '',
                'taxOffice' => $data['tax_office'] ?? '',
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'country' => $data['country'] ?? 'Türkiye',
            ]);
            
            return [
                'success' => $response['success'] ?? false,
                'customer_id' => $response['data']['id'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Ürün listesi al
     */
    public function getProducts(array $params = []): array
    {
        try {
            $response = $this->get('/products', $params);
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Şirket mi kontrol et (e-fatura mı e-arşiv mi)
     */
    private function isCompany(array $order): bool
    {
        $taxNumber = $order['taxNumber'] ?? '';
        return strlen($taxNumber) === 10 || strlen($taxNumber) === 11;
    }

    /**
     * API Headers
     */
    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Api-Key' => $this->apiKey,
            'X-Api-Secret' => $this->apiSecret,
            'X-Company-Id' => $this->companyId,
        ];
    }

    /**
     * GET Request
     */
    private function get(string $endpoint, array $params = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->get($this->baseUrl . $endpoint, $params);

        return $response->json() ?? [];
    }

    /**
     * POST Request
     */
    private function post(string $endpoint, array $data = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->post($this->baseUrl . $endpoint, $data);

        return $response->json() ?? [];
    }

    /**
     * PUT Request
     */
    private function put(string $endpoint, array $data = []): array
    {
        $response = Http::withHeaders($this->getHeaders())
            ->timeout(30)
            ->put($this->baseUrl . $endpoint, $data);

        return $response->json() ?? [];
    }
}
