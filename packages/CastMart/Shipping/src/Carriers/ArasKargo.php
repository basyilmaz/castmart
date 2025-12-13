<?php

namespace CastMart\Shipping\Carriers;

use CastMart\Shipping\Contracts\CarrierInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ArasKargo implements CarrierInterface
{
    protected array $config;
    protected string $apiUrl;

    public function __construct()
    {
        $this->config = config('castmart-shipping.aras');
        $mode = $this->config['mode'] ?? 'test';
        $this->apiUrl = $this->config['api_url'][$mode] ?? $this->config['api_url']['test'];
    }

    /**
     * Kargo firması kodunu getir
     */
    public function getCode(): string
    {
        return 'aras';
    }

    /**
     * Kargo firması adını getir
     */
    public function getName(): string
    {
        return 'Aras Kargo';
    }

    /**
     * Kargo firması aktif mi?
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Gönderi oluştur
     */
    public function createShipment(array $data): array
    {
        try {
            $soapClient = $this->getSoapClient();

            $params = [
                'UserName' => $this->config['username'],
                'Password' => $this->config['password'],
                'CustomerCode' => $this->config['customer_code'],
                
                // Alıcı bilgileri
                'ReceiverName' => $data['receiver_name'],
                'ReceiverPhone' => $this->formatPhone($data['receiver_phone']),
                'ReceiverAddress' => $data['receiver_address'],
                'ReceiverCity' => $this->getCityCode($data['receiver_city']),
                'ReceiverTown' => $data['receiver_district'] ?? '',
                
                // Gönderi bilgileri
                'PieceCount' => $data['piece_count'] ?? 1,
                'PayorTypeCode' => $this->config['payment_type'] ?? 1,
                'DeliveryTypeCode' => $this->config['delivery_type'] ?? 1,
                
                // Opsiyonel alanlar
                'Weight' => $data['weight'] ?? 1,
                'Volume' => $data['desi'] ?? $this->calculateDesi($data),
                'InvoiceNumber' => $data['invoice_number'] ?? '',
                'OrderNumber' => $data['order_number'] ?? '',
                'Description' => $data['description'] ?? 'E-ticaret siparişi',
                
                // COD (Kapıda ödeme)
                'IsCOD' => $data['is_cod'] ?? false,
                'CODAmount' => $data['cod_amount'] ?? 0,
            ];

            // SOAP isteği gönder
            $response = $soapClient->SetOrder($params);

            if (isset($response->SetOrderResult)) {
                $result = $response->SetOrderResult;
                
                if ($result->ResultCode === '0') {
                    return [
                        'success' => true,
                        'tracking_number' => $result->BarcodeNumber ?? '',
                        'cargo_key' => $result->CargoKey ?? '',
                        'message' => 'Gönderi başarıyla oluşturuldu',
                    ];
                }

                return [
                    'success' => false,
                    'error_code' => $result->ResultCode,
                    'message' => $result->ResultMessage ?? 'Gönderi oluşturulamadı',
                ];
            }

            return [
                'success' => false,
                'message' => 'Beklenmeyen API yanıtı',
            ];
        } catch (\Exception $e) {
            Log::error('Aras Kargo shipment creation error', [
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
            $soapClient = $this->getSoapClient();

            $params = [
                'UserName' => $this->config['username'],
                'Password' => $this->config['password'],
                'CustomerCode' => $this->config['customer_code'],
                'QueryType' => 1, // Barkod ile sorgulama
                'IntegrationCode' => $trackingNumber,
            ];

            $response = $soapClient->GetQueryJSON($params);

            if (isset($response->GetQueryJSONResult)) {
                $result = json_decode($response->GetQueryJSONResult, true);
                
                if ($result && isset($result['RESULT_CODE']) && $result['RESULT_CODE'] === '0') {
                    $movements = $result['SHIPPMENT_MOVEMENTS'] ?? [];
                    $lastMovement = end($movements);
                    
                    return [
                        'success' => true,
                        'tracking_number' => $trackingNumber,
                        'status' => $this->mapStatus($lastMovement['REASON_ID'] ?? ''),
                        'status_text' => $lastMovement['REASON'] ?? '',
                        'last_update' => $lastMovement['OPS_DATE'] ?? '',
                        'last_location' => $lastMovement['UNIT_NAME'] ?? '',
                        'delivery_date' => $result['DELIVERY_DATE'] ?? null,
                        'movements' => array_map(function ($m) {
                            return [
                                'date' => $m['OPS_DATE'] ?? '',
                                'status' => $m['REASON'] ?? '',
                                'location' => $m['UNIT_NAME'] ?? '',
                            ];
                        }, $movements),
                    ];
                }

                return [
                    'success' => false,
                    'message' => $result['RESULT_MESSAGE'] ?? 'Kargo bulunamadı',
                ];
            }

            return [
                'success' => false,
                'message' => 'Beklenmeyen API yanıtı',
            ];
        } catch (\Exception $e) {
            Log::error('Aras Kargo tracking error', [
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
     * Kargo etiketi oluştur (PDF/ZPL)
     */
    public function getLabel(string $trackingNumber): ?string
    {
        try {
            $soapClient = $this->getSoapClient();

            $params = [
                'UserName' => $this->config['username'],
                'Password' => $this->config['password'],
                'CustomerCode' => $this->config['customer_code'],
                'IntegrationCode' => $trackingNumber,
                'LabelType' => 1, // 1: PDF, 2: ZPL
            ];

            $response = $soapClient->GetBarcodePDF($params);

            if (isset($response->GetBarcodePDFResult)) {
                $result = $response->GetBarcodePDFResult;
                
                if ($result->ResultCode === '0' && !empty($result->BarcodeContent)) {
                    return base64_decode($result->BarcodeContent);
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Aras Kargo label generation error', [
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
            $soapClient = $this->getSoapClient();

            $params = [
                'UserName' => $this->config['username'],
                'Password' => $this->config['password'],
                'CustomerCode' => $this->config['customer_code'],
                'IntegrationCode' => $trackingNumber,
            ];

            $response = $soapClient->CancelOrder($params);

            if (isset($response->CancelOrderResult)) {
                return $response->CancelOrderResult->ResultCode === '0';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Aras Kargo cancellation error', [
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
        
        // Aras Kargo'nun bölgesel fiyatlandırması için API çağrısı yapılabilir
        // Şimdilik basit bir hesaplama
        $baseCost = 35.00; // TL
        $desiCost = 5.00; // TL/desi
        
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

        // Hacimsel ağırlık hesaplama: En x Boy x Yükseklik / 3000
        if (isset($data['width'], $data['height'], $data['length'])) {
            $volume = $data['width'] * $data['height'] * $data['length'];
            $desiCoefficient = config('castmart-shipping.desi_coefficient', 3000);
            return round($volume / $desiCoefficient, 2);
        }

        // Varsayılan
        return 1;
    }

    /**
     * SOAP client oluştur
     */
    protected function getSoapClient(): \SoapClient
    {
        $options = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 30,
            'soap_version' => SOAP_1_1,
        ];

        return new \SoapClient($this->apiUrl . '?wsdl', $options);
    }

    /**
     * Telefon numarasını formatla
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) === 10) {
            return '0' . $phone;
        }
        
        return $phone;
    }

    /**
     * Şehir kodunu getir
     */
    protected function getCityCode(string $city): string
    {
        $cities = $this->getCityList();
        $cityNormalized = $this->normalizeTurkish(mb_strtoupper($city));
        
        foreach ($cities as $code => $name) {
            if ($this->normalizeTurkish($name) === $cityNormalized) {
                return (string) $code;
            }
        }
        
        return $city; // Bulunamazsa direkt gönder
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
    protected function mapStatus(string $reasonId): string
    {
        $statusMap = [
            '0' => 'pending',
            '1' => 'in_transit',
            '2' => 'in_transit',
            '3' => 'out_for_delivery',
            '4' => 'delivered',
            '5' => 'returned',
            '6' => 'cancelled',
        ];

        return $statusMap[$reasonId] ?? 'unknown';
    }

    /**
     * Türkiye illeri listesi
     */
    protected function getCityList(): array
    {
        return [
            1 => 'ADANA', 2 => 'ADIYAMAN', 3 => 'AFYONKARAHİSAR', 4 => 'AĞRI',
            5 => 'AMASYA', 6 => 'ANKARA', 7 => 'ANTALYA', 8 => 'ARTVİN',
            9 => 'AYDIN', 10 => 'BALIKESİR', 11 => 'BİLECİK', 12 => 'BİNGÖL',
            13 => 'BİTLİS', 14 => 'BOLU', 15 => 'BURDUR', 16 => 'BURSA',
            17 => 'ÇANAKKALE', 18 => 'ÇANKIRI', 19 => 'ÇORUM', 20 => 'DENİZLİ',
            21 => 'DİYARBAKIR', 22 => 'EDİRNE', 23 => 'ELAZIĞ', 24 => 'ERZİNCAN',
            25 => 'ERZURUM', 26 => 'ESKİŞEHİR', 27 => 'GAZİANTEP', 28 => 'GİRESUN',
            29 => 'GÜMÜŞHANE', 30 => 'HAKKARİ', 31 => 'HATAY', 32 => 'ISPARTA',
            33 => 'MERSİN', 34 => 'İSTANBUL', 35 => 'İZMİR', 36 => 'KARS',
            37 => 'KASTAMONU', 38 => 'KAYSERİ', 39 => 'KIRKLARELİ', 40 => 'KIRŞEHİR',
            41 => 'KOCAELİ', 42 => 'KONYA', 43 => 'KÜTAHYA', 44 => 'MALATYA',
            45 => 'MANİSA', 46 => 'KAHRAMANMARAŞ', 47 => 'MARDİN', 48 => 'MUĞLA',
            49 => 'MUŞ', 50 => 'NEVŞEHİR', 51 => 'NİĞDE', 52 => 'ORDU',
            53 => 'RİZE', 54 => 'SAKARYA', 55 => 'SAMSUN', 56 => 'SİİRT',
            57 => 'SİNOP', 58 => 'SİVAS', 59 => 'TEKİRDAĞ', 60 => 'TOKAT',
            61 => 'TRABZON', 62 => 'TUNCELİ', 63 => 'ŞANLIURFA', 64 => 'UŞAK',
            65 => 'VAN', 66 => 'YOZGAT', 67 => 'ZONGULDAK', 68 => 'AKSARAY',
            69 => 'BAYBURT', 70 => 'KARAMAN', 71 => 'KIRIKKALE', 72 => 'BATMAN',
            73 => 'ŞIRNAK', 74 => 'BARTIN', 75 => 'ARDAHAN', 76 => 'IĞDIR',
            77 => 'YALOVA', 78 => 'KARABÜK', 79 => 'KİLİS', 80 => 'OSMANİYE',
            81 => 'DÜZCE',
        ];
    }
}
