<?php

namespace CastMart\Param\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Param extends Payment
{
    protected $code = 'param';

    /**
     * Param API Base URL
     */
    protected function getApiUrl(): string
    {
        return config('param.test_mode')
            ? 'https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_prod.asmx'
            : 'https://dmz.param.com.tr/turkpos.ws/service_turkpos_prod.asmx';
    }

    /**
     * Payment title
     */
    public function getTitle(): string
    {
        return 'Param ile Öde';
    }

    /**
     * Payment description
     */
    public function getDescription(): string
    {
        return 'Kredi kartı ile güvenli ödeme';
    }

    /**
     * Ödeme formunu al
     */
    public function getRedirectUrl()
    {
        return route('param.redirect');
    }

    /**
     * 3D Secure ödeme başlat
     */
    public function initiate3DPayment(array $data): array
    {
        $cart = $this->getCart();
        
        $params = [
            'CLIENT_CODE' => config('param.client_code'),
            'CLIENT_USERNAME' => config('param.client_username'),
            'CLIENT_PASSWORD' => config('param.client_password'),
            'GUID' => config('param.guid'),
            
            'KK_Sahibi' => $data['card_holder'],
            'KK_No' => str_replace(' ', '', $data['card_number']),
            'KK_SK_Ay' => $data['expiry_month'],
            'KK_SK_Yil' => $data['expiry_year'],
            'KK_CVC' => $data['cvv'],
            
            'Tutar' => number_format($cart->grand_total, 2, ',', ''),
            'Taksit' => $data['installment'] ?? 1,
            'Siparis_ID' => $cart->id,
            'Islem_Guvenlik_Tip' => '3D',
            'Islem_ID' => uniqid('PARAM_'),
            'IPAdr' => request()->ip(),
            
            'Hata_URL' => route('param.callback.fail'),
            'Basarili_URL' => route('param.callback.success'),
        ];

        try {
            $response = $this->sendSoapRequest('TP_WMD_Pay', $params);
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'redirect_url' => $response['data']['UCD_URL'] ?? null,
                    'html' => $response['data']['UCD_HTML'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? 'Ödeme başlatılamadı',
            ];

        } catch (\Exception $e) {
            Log::error('Param 3D payment init error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Ödeme işlemi sırasında bir hata oluştu',
            ];
        }
    }

    /**
     * Callback doğrulama
     */
    public function verifyCallback(array $postData): array
    {
        $requiredFields = ['TURKPOS_RETVAL_Sonuc', 'TURKPOS_RETVAL_Sonuc_Str', 'Siparis_ID'];
        
        foreach ($requiredFields as $field) {
            if (!isset($postData[$field])) {
                return [
                    'success' => false,
                    'message' => 'Eksik callback parametresi',
                ];
            }
        }

        $result = $postData['TURKPOS_RETVAL_Sonuc'] ?? '';
        $resultStr = $postData['TURKPOS_RETVAL_Sonuc_Str'] ?? '';
        $orderId = $postData['Siparis_ID'] ?? '';
        $islemId = $postData['Islem_ID'] ?? '';
        $dekontId = $postData['Dekont_ID'] ?? '';

        // Başarılı işlem
        if ($result === '1' || $result === 1) {
            return [
                'success' => true,
                'order_id' => $orderId,
                'transaction_id' => $islemId,
                'receipt_id' => $dekontId,
                'message' => 'Ödeme başarılı',
            ];
        }

        return [
            'success' => false,
            'order_id' => $orderId,
            'message' => $resultStr ?: 'Ödeme başarısız',
            'error_code' => $result,
        ];
    }

    /**
     * İade işlemi
     */
    public function refund(string $transactionId, float $amount): array
    {
        $params = [
            'CLIENT_CODE' => config('param.client_code'),
            'CLIENT_USERNAME' => config('param.client_username'),
            'CLIENT_PASSWORD' => config('param.client_password'),
            'GUID' => config('param.guid'),
            
            'Durum' => 'IADE',
            'Dekont_ID' => $transactionId,
            'Tutar' => number_format($amount, 2, ',', ''),
        ];

        try {
            $response = $this->sendSoapRequest('TP_Islem_Iptal_Iade', $params);
            
            return [
                'success' => $response['success'],
                'message' => $response['message'] ?? ($response['success'] ? 'İade başarılı' : 'İade başarısız'),
            ];
        } catch (\Exception $e) {
            Log::error('Param refund error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'İade işlemi sırasında bir hata oluştu',
            ];
        }
    }

    /**
     * İptal işlemi
     */
    public function cancel(string $transactionId): array
    {
        $params = [
            'CLIENT_CODE' => config('param.client_code'),
            'CLIENT_USERNAME' => config('param.client_username'),
            'CLIENT_PASSWORD' => config('param.client_password'),
            'GUID' => config('param.guid'),
            
            'Durum' => 'IPTAL',
            'Dekont_ID' => $transactionId,
        ];

        try {
            $response = $this->sendSoapRequest('TP_Islem_Iptal_Iade', $params);
            
            return [
                'success' => $response['success'],
                'message' => $response['message'] ?? ($response['success'] ? 'İptal başarılı' : 'İptal başarısız'),
            ];
        } catch (\Exception $e) {
            Log::error('Param cancel error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'İptal işlemi sırasında bir hata oluştu',
            ];
        }
    }

    /**
     * Taksit seçenekleri
     */
    public function getInstallmentOptions(string $binNumber, float $amount): array
    {
        $params = [
            'CLIENT_CODE' => config('param.client_code'),
            'CLIENT_USERNAME' => config('param.client_username'),
            'CLIENT_PASSWORD' => config('param.client_password'),
            'GUID' => config('param.guid'),
            
            'BIN' => substr($binNumber, 0, 6),
            'Tutar' => number_format($amount, 2, ',', ''),
        ];

        try {
            $response = $this->sendSoapRequest('TP_Ozel_Oran_Liste', $params);
            
            if (!$response['success']) {
                return [];
            }

            $installments = [];
            $data = $response['data'] ?? [];

            for ($i = 1; $i <= 12; $i++) {
                $key = "Taksit{$i}_{$i}";
                if (isset($data[$key]) && $data[$key] > 0) {
                    $installments[] = [
                        'count' => $i,
                        'total' => $data[$key],
                        'monthly' => round($data[$key] / $i, 2),
                    ];
                }
            }

            return $installments;
        } catch (\Exception $e) {
            Log::error('Param installment error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * BIN sorgulama
     */
    public function queryBin(string $binNumber): array
    {
        $params = [
            'CLIENT_CODE' => config('param.client_code'),
            'CLIENT_USERNAME' => config('param.client_username'),
            'CLIENT_PASSWORD' => config('param.client_password'),
            'GUID' => config('param.guid'),
            
            'BIN' => substr($binNumber, 0, 6),
        ];

        try {
            $response = $this->sendSoapRequest('BIN_SanalPos', $params);
            
            return [
                'success' => $response['success'],
                'bank_name' => $response['data']['Banka_Adi'] ?? null,
                'card_type' => $response['data']['Kart_Tipi'] ?? null,
                'card_brand' => $response['data']['Kart_Marka'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * SOAP request gönder
     */
    protected function sendSoapRequest(string $method, array $params): array
    {
        $xml = $this->buildSoapEnvelope($method, $params);
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => "https://turkpos.com.tr/{$method}",
            ])->withBody($xml, 'text/xml')->post($this->getApiUrl());

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'API isteği başarısız',
                ];
            }

            return $this->parseSoapResponse($response->body(), $method);
            
        } catch (\Exception $e) {
            Log::error('Param SOAP request error', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * SOAP envelope oluştur
     */
    protected function buildSoapEnvelope(string $method, array $params): string
    {
        $paramsXml = '';
        foreach ($params as $key => $value) {
            $paramsXml .= "<{$key}>" . htmlspecialchars($value) . "</{$key}>";
        }

        return '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                           xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
                           xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                <soap:Body>
                    <' . $method . ' xmlns="https://turkpos.com.tr/">
                        <G>' . $paramsXml . '</G>
                    </' . $method . '>
                </soap:Body>
            </soap:Envelope>';
    }

    /**
     * SOAP response parse et
     */
    protected function parseSoapResponse(string $xml, string $method): array
    {
        try {
            $xml = preg_replace('/(<\/?)\w+:([^>]*>)/', '$1$2', $xml);
            $doc = simplexml_load_string($xml);
            
            if (!$doc) {
                return ['success' => false, 'message' => 'XML parse hatası'];
            }

            $result = (array) $doc->Body->{$method . 'Response'}->{$method . 'Result'};
            
            $sonuc = $result['Sonuc'] ?? $result['DT_Bilgi']['Sonuc'] ?? null;
            
            if ($sonuc === '1' || $sonuc === 1 || $sonuc === true) {
                return [
                    'success' => true,
                    'data' => $result,
                ];
            }

            return [
                'success' => false,
                'message' => $result['Sonuc_Str'] ?? 'Bilinmeyen hata',
                'data' => $result,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Response parse hatası: ' . $e->getMessage(),
            ];
        }
    }
}
