<?php

namespace CastMart\SMS\Channels;

use CastMart\SMS\Contracts\SmsDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IletimerkeziChannel implements SmsDriverInterface
{
    protected array $config;
    protected string $apiUrl;

    public function __construct()
    {
        $this->config = config('castmart-sms.iletimerkezi');
        $this->apiUrl = $this->config['api_url'] ?? 'https://api.iletimerkezi.com/v1';
    }

    /**
     * Driver adını getir
     */
    public function getName(): string
    {
        return 'iletimerkezi';
    }

    /**
     * Driver aktif mi?
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * SMS gönder
     */
    public function send(string $to, string $message, array $options = []): array
    {
        if (config('castmart-sms.debug')) {
            Log::info('SMS Debug Mode (İletimerkezi)', ['to' => $to, 'message' => $message]);
            return ['success' => true, 'message_id' => 'debug_' . uniqid(), 'debug' => true];
        }

        try {
            $phone = $this->formatPhone($to);
            $sender = $options['sender'] ?? $this->config['sender'];

            $xml = $this->buildSendXml($phone, $message, $sender);

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xml, 'text/xml')->post($this->apiUrl . '/send-sms/get/');

            $result = $this->parseXmlResponse($response->body());

            if ($result['status_code'] === '110') {
                // Başarılı
                $this->logSms($phone, $message, $result['order_id'], 'sent');

                return [
                    'success' => true,
                    'message_id' => $result['order_id'],
                    'provider' => 'iletimerkezi',
                ];
            }

            Log::error('İletimerkezi SMS error', ['response' => $result]);

            return [
                'success' => false,
                'error_code' => $result['status_code'],
                'message' => $this->getErrorMessage($result['status_code']),
            ];
        } catch (\Exception $e) {
            Log::error('İletimerkezi exception', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Toplu SMS gönder
     */
    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        if (config('castmart-sms.debug')) {
            Log::info('SMS Bulk Debug Mode (İletimerkezi)', ['count' => count($recipients)]);
            return ['success' => true, 'message_id' => 'debug_bulk_' . uniqid(), 'debug' => true];
        }

        try {
            $sender = $options['sender'] ?? $this->config['sender'];
            $phones = array_map(fn($r) => $this->formatPhone($r), $recipients);

            $xml = $this->buildBulkSendXml($phones, $message, $sender);

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xml, 'text/xml')->post($this->apiUrl . '/send-sms/get/');

            $result = $this->parseXmlResponse($response->body());

            if ($result['status_code'] === '110') {
                return [
                    'success' => true,
                    'message_id' => $result['order_id'],
                    'count' => count($phones),
                    'provider' => 'iletimerkezi',
                ];
            }

            return [
                'success' => false,
                'error_code' => $result['status_code'],
                'message' => $this->getErrorMessage($result['status_code']),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * OTP gönder
     */
    public function sendOtp(string $to, string $code): array
    {
        $template = config('castmart-sms.templates.otp');
        $message = str_replace('{code}', $code, $template);

        return $this->send($to, $message);
    }

    /**
     * SMS durumu sorgula
     */
    public function getStatus(string $messageId): array
    {
        try {
            $xml = $this->buildReportXml($messageId);

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xml, 'text/xml')->post($this->apiUrl . '/get-report/');

            $result = $this->parseReportResponse($response->body());

            if (!empty($result['orders'])) {
                $order = $result['orders'][0];
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'total' => $order['total'] ?? 0,
                    'delivered' => $order['delivered'] ?? 0,
                    'pending' => $order['undelivered'] ?? 0,
                    'failed' => $order['failed'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'message' => 'Rapor alınamadı',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Bakiye sorgula
     */
    public function getBalance(): array
    {
        try {
            $xml = $this->buildBalanceXml();

            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xml, 'text/xml')->post($this->apiUrl . '/get-balance/');

            $xml = simplexml_load_string($response->body());

            if ($xml && isset($xml->balance)) {
                return [
                    'success' => true,
                    'credits' => (float) $xml->balance->sms,
                    'balance' => (float) $xml->balance->amount,
                    'provider' => 'iletimerkezi',
                ];
            }

            return [
                'success' => false,
                'message' => 'Bakiye alınamadı',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * SMS gönderimi için XML oluştur
     */
    protected function buildSendXml(string $phone, string $message, string $sender): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<request>
    <authentication>
        <key>{$this->config['api_key']}</key>
        <hash>{$this->config['api_hash']}</hash>
    </authentication>
    <order>
        <sender>{$sender}</sender>
        <sendDateTime></sendDateTime>
        <message>
            <text><![CDATA[{$message}]]></text>
            <receipents>
                <number>{$phone}</number>
            </receipents>
        </message>
    </order>
</request>
XML;
    }

    /**
     * Toplu SMS için XML oluştur
     */
    protected function buildBulkSendXml(array $phones, string $message, string $sender): string
    {
        $phoneNumbers = '';
        foreach ($phones as $phone) {
            $phoneNumbers .= "<number>{$phone}</number>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<request>
    <authentication>
        <key>{$this->config['api_key']}</key>
        <hash>{$this->config['api_hash']}</hash>
    </authentication>
    <order>
        <sender>{$sender}</sender>
        <sendDateTime></sendDateTime>
        <message>
            <text><![CDATA[{$message}]]></text>
            <receipents>
                {$phoneNumbers}
            </receipents>
        </message>
    </order>
</request>
XML;
    }

    /**
     * Rapor sorgusu için XML oluştur
     */
    protected function buildReportXml(string $orderId): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<request>
    <authentication>
        <key>{$this->config['api_key']}</key>
        <hash>{$this->config['api_hash']}</hash>
    </authentication>
    <order>
        <id>{$orderId}</id>
        <page>1</page>
        <rowCount>1000</rowCount>
    </order>
</request>
XML;
    }

    /**
     * Bakiye sorgusu için XML oluştur
     */
    protected function buildBalanceXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<request>
    <authentication>
        <key>{$this->config['api_key']}</key>
        <hash>{$this->config['api_hash']}</hash>
    </authentication>
</request>
XML;
    }

    /**
     * XML yanıtını parse et
     */
    protected function parseXmlResponse(string $body): array
    {
        $xml = simplexml_load_string($body);

        if ($xml && isset($xml->status)) {
            return [
                'status_code' => (string) $xml->status->code,
                'status_message' => (string) $xml->status->message,
                'order_id' => (string) ($xml->order->id ?? ''),
            ];
        }

        return [
            'status_code' => '0',
            'status_message' => 'XML parse hatası',
            'order_id' => '',
        ];
    }

    /**
     * Rapor yanıtını parse et
     */
    protected function parseReportResponse(string $body): array
    {
        $xml = simplexml_load_string($body);
        $orders = [];

        if ($xml && isset($xml->order)) {
            foreach ($xml->order as $order) {
                $orders[] = [
                    'total' => (int) ($order->total ?? 0),
                    'delivered' => (int) ($order->delivered ?? 0),
                    'undelivered' => (int) ($order->undelivered ?? 0),
                    'failed' => (int) ($order->failed ?? 0),
                ];
            }
        }

        return ['orders' => $orders];
    }

    /**
     * Telefon numarasını formatla
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // +90 veya 0090 ile başlıyorsa kaldır
        if (str_starts_with($phone, '90') && strlen($phone) === 12) {
            $phone = substr($phone, 2);
        }

        // 0 ile başlıyorsa kaldır
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            $phone = substr($phone, 1);
        }

        // 90 prefix ekle
        if (strlen($phone) === 10) {
            $phone = '90' . $phone;
        }

        return $phone;
    }

    /**
     * Hata mesajını getir
     */
    protected function getErrorMessage(string $code): string
    {
        $errors = [
            '111' => 'Sipariş alındı fakat zamanında gönderilemedi',
            '112' => 'Mesaj yasaklı kelime içeriyor',
            '113' => 'Başlık hatalı',
            '114' => 'Yanlış zaman belirtildi',
            '115' => 'Geçersiz IP',
            '116' => 'Geçersiz telefon numarası',
            '117' => 'Bakiye yetersiz',
            '400' => 'İstek doğrulanamadı',
            '401' => 'API key veya hash hatalı',
            '402' => 'Hesap aktif değil',
            '403' => 'Yetkisiz erişim',
            '404' => 'Bulunamadı',
            '450' => 'Parametre eksik veya hatalı',
            '451' => 'Başlık tanımlı değil',
            '452' => 'Telefon numarası eksik',
            '453' => 'Mesaj boş',
        ];

        return $errors[$code] ?? "Bilinmeyen hata: {$code}";
    }

    /**
     * SMS log kaydı
     */
    protected function logSms(string $phone, string $message, string $messageId, string $status): void
    {
        try {
            \CastMart\SMS\Models\SmsLog::create([
                'phone' => $phone,
                'message' => $message,
                'message_id' => $messageId,
                'provider' => 'iletimerkezi',
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::warning('SMS log kaydedilemedi', ['error' => $e->getMessage()]);
        }
    }
}
