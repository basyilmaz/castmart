<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayTR ile Ödeme - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .payment-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            margin: 20px;
        }
        .payment-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        .payment-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .payment-header p {
            font-size: 0.875rem;
            opacity: 0.8;
        }
        .payment-body {
            padding: 0;
        }
        .payment-iframe {
            width: 100%;
            min-height: 500px;
            border: none;
        }
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            font-size: 0.75rem;
            color: #666;
        }
        .security-badge svg {
            width: 16px;
            height: 16px;
            fill: #28a745;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>💳 Güvenli Ödeme</h1>
            <p>PayTR altyapısı ile güvenli ödeme</p>
        </div>
        
        <div class="payment-body">
            <div class="loading" id="loading">
                <div class="loading-spinner"></div>
                <p>Ödeme formu yükleniyor...</p>
            </div>
            
            <iframe 
                src="{{ $iframe_url }}" 
                class="payment-iframe" 
                id="paytriframe"
                frameborder="0"
                scrolling="yes"
                onload="document.getElementById('loading').style.display='none';">
            </iframe>
        </div>
        
        <div class="security-badge">
            <svg viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
            </svg>
            <span>256-bit SSL şifreleme ile güvende</span>
        </div>
    </div>

    <script>
        // İframe yüksekliğini otomatik ayarla
        window.addEventListener('message', function(e) {
            if (e.data && e.data.height) {
                document.getElementById('paytriframe').style.height = e.data.height + 'px';
            }
        });
    </script>
</body>
</html>
