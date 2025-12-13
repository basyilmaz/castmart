@php
    /** @var string $checkoutFormContent */
@endphp
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ödeme Yapılıyor - iyzico</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 24px;
            text-align: center;
        }
        
        .payment-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .payment-header p {
            font-size: 14px;
            opacity: 0.8;
        }
        
        .iyzico-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 20px;
            margin-top: 12px;
        }
        
        .iyzico-badge img {
            height: 20px;
        }
        
        .payment-form-wrapper {
            padding: 24px;
            min-height: 400px;
        }
        
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        
        .loading-overlay.active {
            display: flex;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e0e0e0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .loading-text {
            margin-top: 16px;
            font-size: 16px;
            color: #333;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 16px;
            padding: 16px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .security-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #6c757d;
        }
        
        .security-badge svg {
            width: 16px;
            height: 16px;
            fill: #28a745;
        }
        
        /* iyzico form container stilleri */
        #iyzipay-checkout-form {
            min-height: 350px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>🔐 Güvenli Ödeme</h1>
            <p>Kredi/Banka kartı bilgilerinizi güvenle girebilirsiniz</p>
            <div class="iyzico-badge">
                <span>iyzico güvencesiyle</span>
            </div>
        </div>
        
        <div class="payment-form-wrapper">
            <div id="iyzipay-checkout-form" class="responsive">
                {!! $checkoutFormContent !!}
            </div>
        </div>
        
        <div class="security-badges">
            <div class="security-badge">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                <span>256-bit SSL</span>
            </div>
            <div class="security-badge">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                <span>3D Secure</span>
            </div>
            <div class="security-badge">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                <span>PCI-DSS</span>
            </div>
        </div>
    </div>
    
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Ödeme işleniyor...</div>
    </div>
    
    <script>
        // Form submit olduğunda loading göster
        document.addEventListener('submit', function(e) {
            if (e.target.closest('#iyzipay-checkout-form')) {
                document.getElementById('loadingOverlay').classList.add('active');
            }
        });
    </script>
</body>
</html>
