<?php

namespace CastMart\Marketing\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use CastMart\Marketing\Services\ChatbotService;

class ChatbotController extends Controller
{
    protected ChatbotService $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Chatbot mesaj gönder
     */
    public function message(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:64',
        ]);

        $context = [];
        
        // Giriş yapmış kullanıcı için context
        if ($customer = auth('customer')->user()) {
            $context['customer_id'] = $customer->id;
            $context['customer_name'] = $customer->name;
            $context['customer_email'] = $customer->email;
        }

        // Mevcut sayfa
        if ($request->has('current_page')) {
            $context['current_page'] = $request->current_page;
        }

        $response = $this->chatbotService->chat(
            $request->message,
            $context,
            $request->session_id ?? session()->getId()
        );

        return response()->json($response);
    }

    /**
     * Chatbot widget JS
     */
    public function widget()
    {
        $config = [
            'enabled' => true,
            'position' => 'bottom-right',
            'primaryColor' => '#667eea',
            'greeting' => 'Merhaba! 👋 Size nasıl yardımcı olabilirim?',
            'placeholder' => 'Mesajınızı yazın...',
            'quickActions' => [
                ['text' => 'Siparişimi takip et', 'action' => 'track_order'],
                ['text' => 'Ürün ara', 'action' => 'search'],
                ['text' => 'İade işlemleri', 'action' => 'returns'],
                ['text' => 'Canlı destek', 'action' => 'live_support'],
            ],
        ];

        $js = $this->generateWidgetJs($config);

        return response($js)->header('Content-Type', 'application/javascript');
    }

    /**
     * Widget JS oluştur
     */
    protected function generateWidgetJs(array $config): string
    {
        $configJson = json_encode($config);

        return <<<JS
(function() {
    const config = {$configJson};
    
    // Chatbot container
    const container = document.createElement('div');
    container.id = 'castmart-chatbot';
    container.innerHTML = `
        <div class="chatbot-toggle" onclick="CastMartChat.toggle()">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
        </div>
        <div class="chatbot-window" style="display:none;">
            <div class="chatbot-header">
                <span>CastMart Asistan</span>
                <button onclick="CastMartChat.toggle()">&times;</button>
            </div>
            <div class="chatbot-messages" id="chatbot-messages"></div>
            <div class="chatbot-quick-actions" id="chatbot-actions"></div>
            <div class="chatbot-input">
                <input type="text" id="chatbot-input" placeholder="\${config.placeholder}" 
                       onkeypress="if(event.key==='Enter')CastMartChat.send()">
                <button onclick="CastMartChat.send()">Gönder</button>
            </div>
        </div>
    `;
    
    // Styles
    const style = document.createElement('style');
    style.textContent = `
        #castmart-chatbot { position: fixed; bottom: 20px; right: 20px; z-index: 9999; font-family: system-ui, sans-serif; }
        .chatbot-toggle { width: 60px; height: 60px; border-radius: 50%; background: \${config.primaryColor}; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.2s; }
        .chatbot-toggle:hover { transform: scale(1.1); }
        .chatbot-window { position: absolute; bottom: 70px; right: 0; width: 360px; height: 500px; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden; }
        .chatbot-header { background: \${config.primaryColor}; color: white; padding: 16px; display: flex; justify-content: space-between; align-items: center; font-weight: 600; }
        .chatbot-header button { background: none; border: none; color: white; font-size: 24px; cursor: pointer; }
        .chatbot-messages { flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 12px; }
        .chatbot-message { max-width: 80%; padding: 12px 16px; border-radius: 16px; line-height: 1.4; }
        .chatbot-message.bot { background: #f0f0f0; align-self: flex-start; border-bottom-left-radius: 4px; }
        .chatbot-message.user { background: \${config.primaryColor}; color: white; align-self: flex-end; border-bottom-right-radius: 4px; }
        .chatbot-quick-actions { padding: 8px; display: flex; gap: 8px; flex-wrap: wrap; border-top: 1px solid #eee; }
        .chatbot-quick-actions button { padding: 8px 12px; border: 1px solid \${config.primaryColor}; background: white; color: \${config.primaryColor}; border-radius: 20px; cursor: pointer; font-size: 13px; }
        .chatbot-quick-actions button:hover { background: \${config.primaryColor}; color: white; }
        .chatbot-input { display: flex; padding: 12px; border-top: 1px solid #eee; gap: 8px; }
        .chatbot-input input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; }
        .chatbot-input input:focus { border-color: \${config.primaryColor}; }
        .chatbot-input button { padding: 12px 20px; background: \${config.primaryColor}; color: white; border: none; border-radius: 8px; cursor: pointer; }
    `;
    
    document.head.appendChild(style);
    document.body.appendChild(container);
    
    // CastMart Chat object
    window.CastMartChat = {
        sessionId: 'chat_' + Date.now(),
        isOpen: false,
        
        toggle: function() {
            const window = document.querySelector('.chatbot-window');
            this.isOpen = !this.isOpen;
            window.style.display = this.isOpen ? 'flex' : 'none';
            
            if (this.isOpen && document.getElementById('chatbot-messages').children.length === 0) {
                this.addMessage(config.greeting, 'bot');
                this.showQuickActions();
            }
        },
        
        send: function() {
            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();
            if (!message) return;
            
            this.addMessage(message, 'user');
            input.value = '';
            
            fetch('/api/marketing/chatbot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message, session_id: this.sessionId })
            })
            .then(r => r.json())
            .then(data => {
                this.addMessage(data.message, 'bot');
                if (data.suggestions) this.showSuggestions(data.suggestions);
            })
            .catch(() => this.addMessage('Bağlantı hatası. Lütfen tekrar deneyin.', 'bot'));
        },
        
        addMessage: function(text, type) {
            const messages = document.getElementById('chatbot-messages');
            const div = document.createElement('div');
            div.className = 'chatbot-message ' + type;
            div.innerHTML = text.replace(/\\n/g, '<br>').replace(/\\*\\*(.+?)\\*\\*/g, '<strong>\$1</strong>');
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        },
        
        showQuickActions: function() {
            const container = document.getElementById('chatbot-actions');
            container.innerHTML = config.quickActions.map(a => 
                '<button onclick="CastMartChat.quickAction(\\'' + a.text + '\\')">' + a.text + '</button>'
            ).join('');
        },
        
        showSuggestions: function(suggestions) {
            const container = document.getElementById('chatbot-actions');
            container.innerHTML = suggestions.map(s => 
                '<button onclick="CastMartChat.quickAction(\\'' + s + '\\')">' + s + '</button>'
            ).join('');
        },
        
        quickAction: function(text) {
            document.getElementById('chatbot-input').value = text;
            this.send();
        }
    };
})();
JS;
    }
}
