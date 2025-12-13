<?php

namespace CastMart\Marketing\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatbotService
{
    protected string $apiKey;
    protected string $model;
    protected array $systemPrompt;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');
        
        $this->systemPrompt = [
            'role' => 'system',
            'content' => $this->getSystemPrompt(),
        ];
    }

    /**
     * Mesaj gönder ve yanıt al
     */
    public function chat(string $message, array $context = [], string $sessionId = null): array
    {
        try {
            // Oturum geçmişi
            $history = $sessionId ? $this->getSessionHistory($sessionId) : [];
            
            // Mesajları hazırla
            $messages = array_merge(
                [$this->systemPrompt],
                $this->buildContextMessages($context),
                $history,
                [['role' => 'user', 'content' => $message]]
            );

            // Intent analizi
            $intent = $this->detectIntent($message);

            // Özel intent işlemleri
            if ($intent['type'] !== 'general') {
                $response = $this->handleSpecialIntent($intent, $message, $context);
                if ($response) {
                    $this->saveToHistory($sessionId, $message, $response['message']);
                    return $response;
                }
            }

            // OpenAI API çağrısı
            if (empty($this->apiKey)) {
                return $this->getFallbackResponse($message, $intent);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $botMessage = $data['choices'][0]['message']['content'] ?? 'Üzgünüm, şu anda yanıt veremiyorum.';
                
                $this->saveToHistory($sessionId, $message, $botMessage);

                return [
                    'success' => true,
                    'message' => $botMessage,
                    'intent' => $intent,
                    'suggestions' => $this->getSuggestions($intent),
                ];
            }

            Log::error('ChatBot API error', ['response' => $response->body()]);
            return $this->getFallbackResponse($message, $intent);

        } catch (\Exception $e) {
            Log::error('ChatBot error', ['error' => $e->getMessage()]);
            return $this->getFallbackResponse($message, ['type' => 'error']);
        }
    }

    /**
     * Intent tespiti
     */
    protected function detectIntent(string $message): array
    {
        $message = mb_strtolower($message);

        // Sipariş takip
        if (preg_match('/(sipariş|takip|kargo|nerede|durum)/u', $message)) {
            if (preg_match('/(\d{6,})/u', $message, $matches)) {
                return ['type' => 'order_tracking', 'order_id' => $matches[1]];
            }
            return ['type' => 'order_inquiry'];
        }

        // Ürün arama
        if (preg_match('/(ürün|ara|bul|var mı|fiyat)/u', $message)) {
            return ['type' => 'product_search', 'query' => $message];
        }

        // İade/iptal
        if (preg_match('/(iade|iptal|geri|değiştir)/u', $message)) {
            return ['type' => 'return_refund'];
        }

        // Kargo
        if (preg_match('/(kargo|teslimat|ücretsiz kargo)/u', $message)) {
            return ['type' => 'shipping'];
        }

        // Ödeme
        if (preg_match('/(ödeme|kredi kart|taksit|havale)/u', $message)) {
            return ['type' => 'payment'];
        }

        // Canlı destek
        if (preg_match('/(canlı destek|müşteri hizmet|yetkili|insan)/u', $message)) {
            return ['type' => 'live_support'];
        }

        return ['type' => 'general'];
    }

    /**
     * Özel intent işlemleri
     */
    protected function handleSpecialIntent(array $intent, string $message, array $context): ?array
    {
        switch ($intent['type']) {
            case 'order_tracking':
                return $this->handleOrderTracking($intent['order_id'] ?? null, $context);
            
            case 'order_inquiry':
                return [
                    'success' => true,
                    'message' => 'Sipariş takibi için lütfen sipariş numaranızı giriniz. Örn: "123456 nolu siparişim nerede?"',
                    'intent' => $intent,
                    'suggestions' => ['Siparişlerimi göster', 'Son siparişim nerede?'],
                ];
            
            case 'return_refund':
                return [
                    'success' => true,
                    'message' => "İade ve değişim işlemleri için:\n\n1. Ürün teslim alındıktan sonra 14 gün içinde iade edilebilir\n2. Ürün kullanılmamış ve orijinal ambalajında olmalıdır\n3. İade formu doldurulmalıdır\n\nİade başlatmak için 'Hesabım > Siparişlerim' sayfasından ilgili siparişi seçebilirsiniz.",
                    'intent' => $intent,
                    'suggestions' => ['İade formu nerede?', 'Para iadesi ne zaman yapılır?', 'Ürün değiştirmek istiyorum'],
                    'action' => ['type' => 'link', 'url' => '/customer/account/orders', 'text' => 'Siparişlerime Git'],
                ];
            
            case 'shipping':
                return [
                    'success' => true,
                    'message' => "Kargo bilgileri:\n\n🚚 **Kargo Süreleri:**\n- İstanbul: 1-2 iş günü\n- Diğer iller: 2-4 iş günü\n\n💰 **Kargo Ücreti:**\n- 200₺ ve üzeri siparişlerde ücretsiz\n- 200₺ altı siparişlerde 29.90₺\n\n📦 **Kargo Firmaları:** Aras Kargo, MNG Kargo, Yurtiçi Kargo",
                    'intent' => $intent,
                    'suggestions' => ['Kargomu takip et', 'Ücretsiz kargo şartları'],
                ];
            
            case 'payment':
                return [
                    'success' => true,
                    'message' => "Ödeme seçenekleri:\n\n💳 **Kredi Kartı:** Tüm bankalar, 12 aya kadar taksit\n🏦 **Havale/EFT:** %3 indirim\n💵 **Kapıda Ödeme:** +10₺ hizmet bedeli\n\n🔒 3D Secure ile güvenli ödeme",
                    'intent' => $intent,
                    'suggestions' => ['Taksit seçenekleri', 'Hangi bankalar var?'],
                ];
            
            case 'live_support':
                return [
                    'success' => true,
                    'message' => 'Canlı destek ekibimize bağlanmanızı sağlıyorum. Lütfen bekleyin...',
                    'intent' => $intent,
                    'action' => ['type' => 'live_support', 'transfer' => true],
                ];
        }

        return null;
    }

    /**
     * Sipariş takip
     */
    protected function handleOrderTracking(?string $orderId, array $context): array
    {
        if (!$orderId) {
            return [
                'success' => true,
                'message' => 'Sipariş numaranızı bulamadım. Lütfen sipariş numaranızı belirtir misiniz?',
                'intent' => ['type' => 'order_inquiry'],
            ];
        }

        try {
            $order = \Webkul\Sales\Models\Order::where('increment_id', $orderId)
                ->orWhere('id', $orderId)
                ->first();

            if (!$order) {
                return [
                    'success' => true,
                    'message' => "#{$orderId} numaralı siparişi bulamadım. Lütfen sipariş numaranızı kontrol edin.",
                    'intent' => ['type' => 'order_tracking'],
                ];
            }

            $statusMap = [
                'pending' => '⏳ Beklemede',
                'pending_payment' => '💳 Ödeme Bekleniyor',
                'processing' => '📦 Hazırlanıyor',
                'completed' => '✅ Tamamlandı',
                'canceled' => '❌ İptal Edildi',
            ];

            $status = $statusMap[$order->status] ?? $order->status;

            $message = "📦 **Sipariş #{$order->increment_id}**\n\n";
            $message .= "Durum: {$status}\n";
            $message .= "Tarih: " . $order->created_at->format('d.m.Y H:i') . "\n";
            $message .= "Tutar: " . number_format($order->grand_total, 2) . " ₺\n";

            if ($order->shipments->isNotEmpty()) {
                $shipment = $order->shipments->first();
                $message .= "\n🚚 Kargo: {$shipment->carrier_title}\n";
                $message .= "Takip No: {$shipment->track_number}\n";
            }

            return [
                'success' => true,
                'message' => $message,
                'intent' => ['type' => 'order_tracking', 'order_id' => $orderId],
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => $order->grand_total,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Order tracking error', ['error' => $e->getMessage()]);
            return [
                'success' => true,
                'message' => 'Sipariş bilgilerine şu anda erişemiyorum. Lütfen daha sonra tekrar deneyin.',
                'intent' => ['type' => 'error'],
            ];
        }
    }

    /**
     * Fallback yanıt
     */
    protected function getFallbackResponse(string $message, array $intent): array
    {
        $responses = [
            'general' => 'Merhaba! Size nasıl yardımcı olabilirim? Sipariş takibi, ürün arama veya iade işlemleri konusunda yardımcı olabilirim.',
            'error' => 'Üzgünüm, şu anda yanıt veremiyorum. Canlı destek hattımıza bağlanmak ister misiniz?',
        ];

        return [
            'success' => true,
            'message' => $responses[$intent['type']] ?? $responses['general'],
            'intent' => $intent,
            'suggestions' => $this->getSuggestions($intent),
        ];
    }

    /**
     * Öneri butonları
     */
    protected function getSuggestions(array $intent): array
    {
        $default = [
            'Siparişimi takip et',
            'Ürün ara',
            'İade işlemleri',
            'Canlı destek',
        ];

        return $default;
    }

    /**
     * Oturum geçmişi al
     */
    protected function getSessionHistory(string $sessionId): array
    {
        return Cache::get("chatbot:session:{$sessionId}", []);
    }

    /**
     * Oturum geçmişine kaydet
     */
    protected function saveToHistory(string $sessionId, string $userMessage, string $botMessage): void
    {
        if (!$sessionId) return;

        $history = $this->getSessionHistory($sessionId);
        
        $history[] = ['role' => 'user', 'content' => $userMessage];
        $history[] = ['role' => 'assistant', 'content' => $botMessage];
        
        // Son 10 mesajı tut
        $history = array_slice($history, -20);
        
        Cache::put("chatbot:session:{$sessionId}", $history, 3600);
    }

    /**
     * Context mesajları
     */
    protected function buildContextMessages(array $context): array
    {
        if (empty($context)) {
            return [];
        }

        $contextStr = "Müşteri bilgileri:\n";
        
        if (isset($context['customer_name'])) {
            $contextStr .= "- İsim: {$context['customer_name']}\n";
        }
        if (isset($context['current_page'])) {
            $contextStr .= "- Bulunduğu sayfa: {$context['current_page']}\n";
        }

        return [
            ['role' => 'system', 'content' => $contextStr],
        ];
    }

    /**
     * Sistem prompt'u
     */
    protected function getSystemPrompt(): string
    {
        return <<<PROMPT
Sen CastMart e-ticaret sitesinin yardımcı asistanısın. Türkçe konuşuyorsun ve müşterilere yardımcı oluyorsun.

Görevlerin:
1. Sipariş takibi yapma
2. Ürün arama ve önerme
3. İade/değişim işlemleri hakkında bilgi verme
4. Kargo ve teslimat bilgisi
5. Ödeme seçenekleri hakkında bilgi
6. Gerektiğinde canlı desteğe yönlendirme

Kurallar:
- Kısa ve net yanıtlar ver
- Emoji kullan ama abartma
- Müşteriyi memnun etmeye çalış
- Bilmediğin konularda canlı desteğe yönlendir
- Türkçe dil bilgisi kurallarına uy
PROMPT;
    }
}
