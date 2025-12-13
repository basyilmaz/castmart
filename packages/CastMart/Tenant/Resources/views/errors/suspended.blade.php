<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mağaza Askıda - CastMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #fef3c7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 28px;
            color: #92400e;
            margin-bottom: 10px;
        }
        p {
            color: #78716c;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .contact-info {
            background: #fef3c7;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .contact-info h3 {
            color: #92400e;
            margin-bottom: 10px;
        }
        .contact-info a {
            color: #d97706;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #f59e0b;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #d97706;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Mağaza Askıya Alındı</h1>
        <p>
            Bu mağaza şu anda askıya alınmış durumda. 
            Bu durum genellikle ödeme sorunu veya kullanım koşulları ihlali nedeniyle oluşur.
        </p>
        
        <div class="contact-info">
            <h3>İletişim</h3>
            <p>
                Sorununuzu çözmek için bizimle iletişime geçin:<br>
                <a href="mailto:destek@castmart.com">destek@castmart.com</a>
            </p>
        </div>
        
        <a href="https://castmart.com" class="btn">
            Ana Sayfaya Dön
        </a>
    </div>
</body>
</html>
