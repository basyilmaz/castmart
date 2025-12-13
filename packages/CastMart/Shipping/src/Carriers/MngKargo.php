<?php

namespace CastMart\Shipping\Carriers;

use CastMart\Shipping\Contracts\CarrierInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MngKargo implements CarrierInterface
{
    protected array $config;
    protected string $apiUrl;
    protected ?string $token = null;

    public function __construct()
    {
        $this->config = config('castmart-shipping.mng');
        $mode = $this->config['mode'] ?? 'test';
        $this->apiUrl = $this->config['api_url'][$mode] ?? $this->config['api_url']['test'];
    }

    /**
     * Kargo firması kodunu getir
     */
    public function getCode(): string
    {
        return 'mng';
    }

    /**
     * Kargo firması adını getir
     */
    public function getName(): string
    {
        return 'MNG Kargo';
    }

    /**
     * Kargo firması aktif mi?
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * API token al
     */
    protected function getToken(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/token', [
                'customerNumber' => $this->config['customer_number'],
                'password' => $this->config['api_secret'],
                'identityType' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->token = $data['token'] ?? null;
                return $this->token;
            }

            Log::error('MNG Kargo token error', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('MNG Kargo token exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Gönderi oluştur
     */
    public function createShipment(array $data): array
    {
        try {
            $token = $this->getToken();
            
            if (!$token) {
                return ['success' => false, 'message' => 'API token alınamadı'];
            }

            $payload = [
                'order' => [
                    'referenceId' => $data['order_number'] ?? uniqid('MNG'),
                    'barcode' => '',
                    'billOfLandingId' => $data['invoice_number'] ?? '',
                    'isCOD' => $data['is_cod'] ?? false,
                    'codAmount' => $data['cod_amount'] ?? 0,
                    'shipmentServiceType' => 1, // 1: Normal, 2: Ekspres
                    'packagingType' => 1, // 1: Koli, 2: Dosya
                    'content' => $data['description'] ?? 'E-ticaret ürünü',
                    'smsNotification1' => true,
                    'smsNotification2' => true,
                    'paymentType' => ($data['is_cod'] ?? false) ? 2 : 1, // 1: Peşin, 2: Kapıda
                    'deliveryType' => 1, // 1: Adrese, 2: Şubeye
                    'description' => $data['description'] ?? '',
                    'marketPlaceShortCode' => '',
                    'marketPlaceSaleCode' => $data['order_number'] ?? '',
                ],
                'orderPieceList' => [
                    [
                        'barcode' => '',
                        'desi' => $data['desi'] ?? $this->calculateDesi($data),
                        'kg' => $data['weight'] ?? 1,
                        'content' => $data['description'] ?? 'Ürün',
                    ]
                ],
                'recipient' => [
                    'customerId' => '',
                    'refCustomerId' => '',
                    'cityCode' => $this->getCityCode($data['receiver_city']),
                    'cityName' => $data['receiver_city'],
                    'districtName' => $data['receiver_district'] ?? '',
                    'address' => $data['receiver_address'],
                    'bussinessPhoneNumber' => '',
                    'email' => $data['receiver_email'] ?? '',
                    'taxNumber' => '',
                    'taxOffice' => '',
                    'fullName' => $data['receiver_name'],
                    'homePhoneNumber' => $this->formatPhone($data['receiver_phone']),
                    'mobilePhoneNumber' => $this->formatPhone($data['receiver_phone']),
                ],
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($this->apiUrl . '/standardcmdapi/createOrder', $payload);

            $result = $response->json();

            if ($response->successful() && isset($result['orderResult'])) {
                $orderResult = $result['orderResult'];
                
                if ($orderResult['resultCode'] === '0') {
                    return [
                        'success' => true,
                        'tracking_number' => $orderResult['barcode'] ?? '',
                        'cargo_key' => $orderResult['orderId'] ?? '',
                        'reference_id' => $orderResult['referenceId'] ?? '',
                        'message' => 'Gönderi başarıyla oluşturuldu',
                    ];
                }

                return [
                    'success' => false,
                    'error_code' => $orderResult['resultCode'],
                    'message' => $orderResult['resultMessage'] ?? 'Gönderi oluşturulamadı',
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Beklenmeyen API yanıtı',
            ];
        } catch (\Exception $e) {
            Log::error('MNG Kargo shipment creation error', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => 'Sistem hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Takip numarası sorgula
     */
    public function trackShipment(string $trackingNumber): array
    {
        try {
            $token = $this->getToken();
            
            if (!$token) {
                return ['success' => false, 'message' => 'API token alınamadı'];
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get($this->apiUrl . '/standardqueryapi/getShipmentInfo', [
                'barcode' => $trackingNumber,
            ]);

            $result = $response->json();

            if ($response->successful() && !empty($result)) {
                $shipment = $result[0] ?? $result;
                
                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'status' => $this->mapStatus($shipment['status'] ?? ''),
                    'status_text' => $shipment['statusName'] ?? '',
                    'last_update' => $shipment['lastTransactionDate'] ?? '',
                    'last_location' => $shipment['lastTransactionBranch'] ?? '',
                    'delivery_date' => $shipment['deliveryDate'] ?? null,
                    'recipient_name' => $shipment['recipientName'] ?? '',
                    'movements' => $this->parseMovements($shipment['movements'] ?? []),
                ];
            }

            return [
                'success' => false,
                'message' => 'Kargo bulunamadı',
            ];
        } catch (\Exception $e) {
            Log::error('MNG Kargo tracking error', [
                'message' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
            ]);

            return [
                'success' => false,
                'message' => 'Takip sorgulama hatası: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Kargo etiketi oluştur (PDF)
     */
    public function getLabel(string $trackingNumber): ?string
    {
        try {
            $token = $this->getToken();
            
            if (!$token) {
                return null;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->get($this->apiUrl . '/standardcmdapi/getBarcodePdf', [
                'barcode' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (!empty($result['pdfContent'])) {
                    return base64_decode($result['pdfContent']);
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('MNG Kargo label generation error', [
                'message' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
            ]);

            return null;
        }
    }

    /**
     * Gönderiyi iptal et
     */
    public function cancelShipment(string $trackingNumber): bool
    {
        try {
            $token = $this->getToken();
            
            if (!$token) {
                return false;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($this->apiUrl . '/standardcmdapi/cancelOrder', [
                'barcode' => $trackingNumber,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return ($result['resultCode'] ?? '') === '0';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('MNG Kargo cancellation error', [
                'message' => $e->getMessage(),
                'tracking_number' => $trackingNumber,
            ]);

            return false;
        }
    }

    /**
     * Kargo ücreti hesapla
     */
    public function calculateRate(array $data): float
    {
        $desi = $this->calculateDesi($data);
        
        // MNG Kargo tarifesi (yaklaşık değerler)
        $baseCost = 32.00; // TL
        $desiCost = 4.50; // TL/desi
        
        return $baseCost + ($desi * $desiCost);
    }

    /**
     * Desi hesapla
     */
    protected function calculateDesi(array $data): float
    {
        if (isset($data['desi'])) {
            return (float) $data['desi'];
        }

        if (isset($data['width'], $data['height'], $data['length'])) {
            $volume = $data['width'] * $data['height'] * $data['length'];
            $desiCoefficient = config('castmart-shipping.desi_coefficient', 3000);
            return round($volume / $desiCoefficient, 2);
        }

        return 1;
    }

    /**
     * Telefon numarasını formatla
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10 && !str_starts_with($phone, '0')) {
            return '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * Şehir kodunu getir
     */
    protected function getCityCode(string $city): int
    {
        $cities = [
            'ADANA' => 1, 'ADIYAMAN' => 2, 'AFYONKARAHİSAR' => 3, 'AĞRI' => 4,
            'AMASYA' => 5, 'ANKARA' => 6, 'ANTALYA' => 7, 'ARTVİN' => 8,
            'AYDIN' => 9, 'BALIKESİR' => 10, 'BİLECİK' => 11, 'BİNGÖL' => 12,
            'BİTLİS' => 13, 'BOLU' => 14, 'BURDUR' => 15, 'BURSA' => 16,
            'ÇANAKKALE' => 17, 'ÇANKIRI' => 18, 'ÇORUM' => 19, 'DENİZLİ' => 20,
            'DİYARBAKIR' => 21, 'EDİRNE' => 22, 'ELAZIĞ' => 23, 'ERZİNCAN' => 24,
            'ERZURUM' => 25, 'ESKİŞEHİR' => 26, 'GAZİANTEP' => 27, 'GİRESUN' => 28,
            'GÜMÜŞHANE' => 29, 'HAKKARİ' => 30, 'HATAY' => 31, 'ISPARTA' => 32,
            'MERSİN' => 33, 'İSTANBUL' => 34, 'İZMİR' => 35, 'KARS' => 36,
            'KASTAMONU' => 37, 'KAYSERİ' => 38, 'KIRKLARELİ' => 39, 'KIRŞEHİR' => 40,
            'KOCAELİ' => 41, 'KONYA' => 42, 'KÜTAHYA' => 43, 'MALATYA' => 44,
            'MANİSA' => 45, 'KAHRAMANMARAŞ' => 46, 'MARDİN' => 47, 'MUĞLA' => 48,
            'MUŞ' => 49, 'NEVŞEHİR' => 50, 'NİĞDE' => 51, 'ORDU' => 52,
            'RİZE' => 53, 'SAKARYA' => 54, 'SAMSUN' => 55, 'SİİRT' => 56,
            'SİNOP' => 57, 'SİVAS' => 58, 'TEKİRDAĞ' => 59, 'TOKAT' => 60,
            'TRABZON' => 61, 'TUNCELİ' => 62, 'ŞANLIURFA' => 63, 'UŞAK' => 64,
            'VAN' => 65, 'YOZGAT' => 66, 'ZONGULDAK' => 67, 'AKSARAY' => 68,
            'BAYBURT' => 69, 'KARAMAN' => 70, 'KIRIKKALE' => 71, 'BATMAN' => 72,
            'ŞIRNAK' => 73, 'BARTIN' => 74, 'ARDAHAN' => 75, 'IĞDIR' => 76,
            'YALOVA' => 77, 'KARABÜK' => 78, 'KİLİS' => 79, 'OSMANİYE' => 80,
            'DÜZCE' => 81,
        ];

        $cityNormalized = $this->normalizeTurkish(mb_strtoupper(trim($city)));
        
        foreach ($cities as $name => $code) {
            if ($this->normalizeTurkish($name) === $cityNormalized) {
                return $code;
            }
        }
        
        return 34; // Varsayılan İstanbul
    }

    /**
     * Türkçe karakterleri normalize et
     */
    protected function normalizeTurkish(string $text): string
    {
        $search = ['İ', 'I', 'Ğ', 'Ü', 'Ş', 'Ö', 'Ç', 'ı', 'ğ', 'ü', 'ş', 'ö', 'ç'];
        $replace = ['I', 'I', 'G', 'U', 'S', 'O', 'C', 'i', 'g', 'u', 's', 'o', 'c'];
        return str_replace($search, $replace, $text);
    }

    /**
     * Durum kodunu map'le
     */
    protected function mapStatus(string $status): string
    {
        $statusMap = [
            'CREATED' => 'created',
            'PICKUP' => 'picked_up',
            'INTRANSIT' => 'in_transit',
            'OUTFORDELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RETURNED' => 'returned',
            'CANCELLED' => 'cancelled',
        ];

        return $statusMap[strtoupper($status)] ?? 'unknown';
    }

    /**
     * Hareketleri parse et
     */
    protected function parseMovements(array $movements): array
    {
        return array_map(function ($m) {
            return [
                'date' => $m['transactionDate'] ?? '',
                'status' => $m['transactionName'] ?? '',
                'location' => $m['branchName'] ?? '',
            ];
        }, $movements);
    }
}
