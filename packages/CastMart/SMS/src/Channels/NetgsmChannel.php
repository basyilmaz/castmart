<?php

namespace CastMart\SMS\Channels;

use CastMart\SMS\Contracts\SmsDriverInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetgsmChannel implements SmsDriverInterface
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('castmart-sms.netgsm');
    }

    /**
     * Driver adını getir
     */
    public function getName(): string
    {
        return 'netgsm';
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
            Log::info('SMS Debug Mode', ['to' => $to, 'message' => $message]);
            return ['success' => true, 'message_id' => 'debug_' . uniqid(), 'debug' => true];
        }

        try {
            $phone = $this->formatPhone($to);
            
            $params = [
                'usercode' => $this->config['usercode'],
                'password' => $this->config['password'],
                'gsmno' => $phone,
                'message' => $message,
                'msgheader' => $options['header'] ?? $this->config['msgheader'],
                'dil' => 'TR',
            ];

            // Zamanlı gönderim varsa
            if (!empty($options['send_date'])) {
                $params['startdate'] = $options['send_date'];
            }

            $response = Http::get($this->config['api_url']['send'], $params);
            $result = trim($response->body());

            // Netgsm response codes
            // 00: Başarılı
            // 20: Mesaj metninde hata
            // 30: Geçersiz kullanıcı adı/şifre
            // 40: Gönderici başlığı sorunu
            // 50: IYS bilgisi sorunu
            // 60: Veritabanı sorunu
            // 70: Referans no hatası

            if (str_starts_with($result, '00')) {
                // Başarılı: "00 123456789" formatında döner
                $parts = explode(' ', $result);
                $messageId = $parts[1] ?? uniqid();

                // Log kaydı
                $this->logSms($phone, $message, $messageId, 'sent');

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'provider' => 'netgsm',
                ];
            }

            Log::error('Netgsm SMS error', ['response' => $result, 'phone' => $phone]);

            return [
                'success' => false,
                'error_code' => $result,
                'message' => $this->getErrorMessage($result),
            ];
        } catch (\Exception $e) {
            Log::error('Netgsm exception', ['message' => $e->getMessage()]);

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
            Log::info('SMS Bulk Debug Mode', ['count' => count($recipients)]);
            return ['success' => true, 'message_id' => 'debug_bulk_' . uniqid(), 'debug' => true];
        }

        try {
            $phones = array_map(fn($r) => $this->formatPhone($r), $recipients);
            $phoneList = implode(',', $phones);

            // XML API kullan (toplu gönderim için daha uygun)
            $xml = $this->buildBulkXml($phones, $message, $options);

            $response = Http::withBody($xml, 'text/xml')
                ->post($this->config['api_url']['send_xml']);

            $result = trim($response->body());

            if (str_starts_with($result, '00')) {
                $parts = explode(' ', $result);
                $messageId = $parts[1] ?? uniqid();

                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'count' => count($phones),
                    'provider' => 'netgsm',
                ];
            }

            return [
                'success' => false,
                'error_code' => $result,
                'message' => $this->getErrorMessage($result),
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

        return $this->send($to, $message, ['header' => $this->config['msgheader']]);
    }

    /**
     * SMS durumu sorgula
     */
    public function getStatus(string $messageId): array
    {
        try {
            $params = [
                'usercode' => $this->config['usercode'],
                'password' => $this->config['password'],
                'bulkid' => $messageId,
                'type' => 0, // 0: Özet, 1: Detay
            ];

            $response = Http::get($this->config['api_url']['report'], $params);
            $result = trim($response->body());

            // Parse XML response
            $xml = simplexml_load_string($result);

            if ($xml && isset($xml->main)) {
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'total' => (string) $xml->main->total ?? 0,
                    'delivered' => (string) $xml->main->delivered ?? 0,
                    'pending' => (string) $xml->main->pending ?? 0,
                    'failed' => (string) $xml->main->undelivered ?? 0,
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
            $params = [
                'usercode' => $this->config['usercode'],
                'password' => $this->config['password'],
                'tip' => 1,
            ];

            $response = Http::get($this->config['api_url']['balance'], $params);
            $result = trim($response->body());

            // Format: "KREDI_MIKTARI PARA_MIKTARI" veya hata kodu
            if (is_numeric($result) || str_contains($result, ' ')) {
                $parts = explode(' ', $result);
                
                return [
                    'success' => true,
                    'credits' => (float) ($parts[0] ?? 0),
                    'balance' => (float) ($parts[1] ?? 0),
                    'provider' => 'netgsm',
                ];
            }

            return [
                'success' => false,
                'error_code' => $result,
                'message' => $this->getErrorMessage($result),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Telefon numarasını formatla
     */
    protected function formatPhone(string $phone): string
    {
        // Sadece rakamları al
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // +90 veya 0090 ile başlıyorsa kaldır
        if (str_starts_with($phone, '90') && strlen($phone) === 12) {
            $phone = substr($phone, 2);
        }

        // 0 ile başlıyorsa kaldır
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            $phone = substr($phone, 1);
        }

        // 90 prefix ekle (Netgsm için gerekli)
        if (strlen($phone) === 10) {
            $phone = '90' . $phone;
        }

        return $phone;
    }

    /**
     * Toplu SMS için XML oluştur
     */
    protected function buildBulkXml(array $phones, string $message, array $options): string
    {
        $header = $options['header'] ?? $this->config['msgheader'];
        $phoneNumbers = '';
        
        foreach ($phones as $phone) {
            $phoneNumbers .= "<no>{$phone}</no>\n";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mainbody>
    <header>
        <company dession="1"/>
        <usercode>{$this->config['usercode']}</usercode>
        <password>{$this->config['password']}</password>
        <startdate></startdate>
        <stopdate></stopdate>
        <type>1:n</type>
        <msgheader>{$header}</msgheader>
    </header>
    <body>
        <msg><![CDATA[{$message}]]></msg>
        {$phoneNumbers}
    </body>
</mainbody>
XML;
    }

    /**
     * Hata mesajını getir
     */
    protected function getErrorMessage(string $code): string
    {
        $errors = [
            '20' => 'Mesaj metninde hata var veya mesaj metni boş',
            '30' => 'Geçersiz kullanıcı adı veya şifre',
            '40' => 'Gönderici başlığı sisteme tanımlı değil',
            '50' => 'Abone değilsiniz',
            '51' => 'Gönderici başlığı izni yok',
            '60' => 'Veritabanı sorunu',
            '70' => 'Referans numarası hatalı',
            '80' => 'Gönderim sınırı aşıldı',
            '85' => 'Mükerrer gönderim engeli',
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
                'provider' => 'netgsm',
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::warning('SMS log kaydedilemedi', ['error' => $e->getMessage()]);
        }
    }
}
