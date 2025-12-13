<?php

namespace CastMart\Shipping\Carriers;

use CastMart\Shipping\Contracts\CarrierInterface;
use Illuminate\Support\Facades\Log;

class YurticiKargo implements CarrierInterface
{
    protected array $config;
    protected string $apiUrl;

    public function __construct()
    {
        $this->config = config('castmart-shipping.yurtici');
        $mode = $this->config['mode'] ?? 'test';
        $this->apiUrl = $this->config['api_url'][$mode] ?? $this->config['api_url']['test'];
    }

    /**
     * Kargo firması kodunu getir
     */
    public function getCode(): string
    {
        return 'yurtici';
    }

    /**
     * Kargo firması adını getir
     */
    public function getName(): string
    {
        return 'Yurtiçi Kargo';
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
            $soapClient = $this->getSoapClient('ShippingOrderService');

            $params = [
                'wsUserName' => $this->config['username'],
                'wsPassword' => $this->config['password'],
                'userLanguage' => 'TR',
                'ShippingOrderVO' => [
                    'cargoKey' => $data['order_number'] ?? uniqid('YK'),
                    'invoiceKey' => $data['invoice_number'] ?? '',
                    'receiverCustName' => $data['receiver_name'],
                    'receiverAddress' => $data['receiver_address'],
                    'receiverPhone1' => $this->formatPhone($data['receiver_phone']),
                    'cityName' => $data['receiver_city'],
                    'townName' => $data['receiver_district'] ?? '',
                    'waybillNo' => '',
                    'ttDocumentId' => '',
                    'dcSelectedCredit' => 0,
                    'dcCreditRule' => 0,
                    'description' => $data['description'] ?? 'E-ticaret ürünü',
                    'orgGeoCode' => '',
                    'privilegeOrder' => 0,
                    'custProdId' => 0,
                    'orgReceiverCustId' => 0,
                    
                    // Kargo detayları
                    'ShippingOrderDetailVOList' => [
                        [
                            'desi' => $data['desi'] ?? $this->calculateDesi($data),
                            'kg' => $data['weight'] ?? 1,
                            'cargoCount' => $data['piece_count'] ?? 1,
                        ]
                    ],
                ],
            ];

            // Kapıda ödeme
            if ($data['is_cod'] ?? false) {
                $params['ShippingOrderVO']['codAmount'] = $data['cod_amount'] ?? 0;
                $params['ShippingOrderVO']['ttCollectionType'] = 2; // Kapıda ödeme
            }

            $response = $soapClient->createShipment($params);

            if (isset($response->ShippingOrderResultVO)) {
                $result = $response->ShippingOrderResultVO;
                
                if ($result->outFlag === '0') {
                    return [
                        'success' => true,
                        'tracking_number' => $result->cargoKey ?? '',
                        'cargo_key' => $result->jobId ?? '',
                        'message' => 'Gönderi başarıyla oluşturuldu',
                    ];
                }

                return [
                    'success' => false,
                    'error_code' => $result->outFlag,
                    'message' => $result->errMessage ?? 'Gönderi oluşturulamadı',
                ];
            }

            return [
                'success' => false,
                'message' => 'Beklenmeyen API yanıtı',
            ];
        } catch (\Exception $e) {
            Log::error('Yurtiçi Kargo shipment creation error', [
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
            $soapClient = $this->getSoapClient('ShippingQueryService');

            $params = [
                'wsUserName' => $this->config['username'],
                'wsPassword' => $this->config['password'],
                'userLanguage' => 'TR',
                'keys' => $trackingNumber,
                'keyType' => 0, // 0: Takip No, 1: Referans No
                'addHistoricalData' => true,
                'onlyTracking' => false,
            ];

            $response = $soapClient->queryShipment($params);

            if (isset($response->ShippingDeliveryVO)) {
                $shipment = $response->ShippingDeliveryVO;
                
                if (is_array($shipment)) {
                    $shipment = $shipment[0];
                }

                $movements = [];
                if (isset($shipment->ShippingDeliveryDetailVO)) {
                    $details = is_array($shipment->ShippingDeliveryDetailVO) 
                        ? $shipment->ShippingDeliveryDetailVO 
                        : [$shipment->ShippingDeliveryDetailVO];
                    
                    foreach ($details as $detail) {
                        $movements[] = [
                            'date' => $detail->operationDate ?? '',
                            'status' => $detail->operationMessage ?? '',
                            'location' => $detail->unitName ?? '',
                        ];
                    }
                }

                return [
                    'success' => true,
                    'tracking_number' => $trackingNumber,
                    'status' => $this->mapStatus($shipment->operationCode ?? ''),
                    'status_text' => $shipment->operationMessage ?? '',
                    'last_update' => $shipment->operationDate ?? '',
                    'last_location' => $shipment->unitName ?? '',
                    'delivery_date' => $shipment->deliveryDate ?? null,
                    'movements' => $movements,
                ];
            }

            return [
                'success' => false,
                'message' => 'Kargo bulunamadı',
            ];
        } catch (\Exception $e) {
            Log::error('Yurtiçi Kargo tracking error', [
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
     * Kargo etiketi oluştur
     */
    public function getLabel(string $trackingNumber): ?string
    {
        try {
            $soapClient = $this->getSoapClient('ShippingOrderService');

            $params = [
                'wsUserName' => $this->config['username'],
                'wsPassword' => $this->config['password'],
                'userLanguage' => 'TR',
                'cargoKeys' => $trackingNumber,
            ];

            $response = $soapClient->createCargoBarcode($params);

            if (isset($response->barcodeBytes)) {
                return base64_decode($response->barcodeBytes);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Yurtiçi Kargo label generation error', [
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
            $soapClient = $this->getSoapClient('ShippingOrderService');

            $params = [
                'wsUserName' => $this->config['username'],
                'wsPassword' => $this->config['password'],
                'userLanguage' => 'TR',
                'cargoKeys' => $trackingNumber,
            ];

            $response = $soapClient->cancelShipment($params);

            if (isset($response->outFlag)) {
                return $response->outFlag === '0';
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Yurtiçi Kargo cancellation error', [
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
        
        // Yurtiçi Kargo tarifesi (yaklaşık değerler)
        $baseCost = 38.00; // TL
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

        if (isset($data['width'], $data['height'], $data['length'])) {
            $volume = $data['width'] * $data['height'] * $data['length'];
            $desiCoefficient = config('castmart-shipping.desi_coefficient', 3000);
            return round($volume / $desiCoefficient, 2);
        }

        return 1;
    }

    /**
     * SOAP client oluştur
     */
    protected function getSoapClient(string $service): \SoapClient
    {
        $wsdlUrls = [
            'ShippingOrderService' => $this->apiUrl,
            'ShippingQueryService' => str_replace('ShippingOrdersServiceV2', 'ShippingQueryServiceV2', $this->apiUrl),
        ];

        $options = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 30,
            'soap_version' => SOAP_1_1,
        ];

        return new \SoapClient($wsdlUrls[$service] . '?wsdl', $options);
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
     * Durum kodunu map'le
     */
    protected function mapStatus(string $operationCode): string
    {
        $statusMap = [
            '0' => 'pending',
            '1' => 'picked_up',
            '2' => 'in_transit',
            '3' => 'out_for_delivery',
            '4' => 'delivered',
            '5' => 'returned',
            '6' => 'cancelled',
        ];

        return $statusMap[$operationCode] ?? 'unknown';
    }
}
