<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mağaza Kaydı - CastMart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .logo p {
            color: #666;
            margin-top: 5px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .subdomain-input {
            display: flex;
            align-items: center;
        }
        
        .subdomain-input input {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-right: none;
        }
        
        .subdomain-input span {
            background: #f8fafc;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #64748b;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .plan-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .plan-card {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .plan-card:hover {
            border-color: #667eea;
        }
        
        .plan-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .plan-card input {
            display: none;
        }
        
        .plan-card h4 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
        }
        
        .plan-card .price {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
            margin: 5px 0;
        }
        
        .plan-card .price span {
            font-size: 12px;
            font-weight: 400;
            color: #666;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .trial-info {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #f0fdf4;
            border-radius: 8px;
            color: #16a34a;
            font-size: 14px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .error-message {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }
        
        @media (max-width: 480px) {
            .plan-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>CastMart</h1>
            <p>E-Ticaret Mağazanızı Oluşturun</p>
        </div>
        
        <form action="{{ route('tenant.register.submit') }}" method="POST" id="registerForm">
            @csrf
            
            <div class="form-group">
                <label>Şirket / Mağaza Adı</label>
                <input type="text" name="company_name" value="{{ old('company_name') }}" required 
                       placeholder="Örn: ABC Ticaret">
                @error('company_name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>Mağaza Adresi (Subdomain)</label>
                <div class="subdomain-input">
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" required 
                           placeholder="magaza-adiniz" pattern="[a-z0-9\-]+" 
                           oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '')">
                    <span>{{ config('castmart-tenant.subdomain_suffix', '.castmart.com') }}</span>
                </div>
                @error('subdomain')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>Adınız Soyadınız</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                       placeholder="Örn: Ahmet Yılmaz">
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>E-posta Adresi</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                       placeholder="ornek@email.com">
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>Şifre</label>
                <input type="password" name="password" required minlength="8" 
                       placeholder="En az 8 karakter">
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label>Şifre Tekrar</label>
                <input type="password" name="password_confirmation" required 
                       placeholder="Şifrenizi tekrar girin">
            </div>
            
            <div class="form-group">
                <label>Plan Seçin</label>
                <div class="plan-cards">
                    @foreach($plans as $key => $plan)
                    <label class="plan-card {{ old('plan', 'starter') == $key ? 'selected' : '' }}">
                        <input type="radio" name="plan" value="{{ $key }}" 
                               {{ old('plan', 'starter') == $key ? 'checked' : '' }}>
                        <h4>{{ $plan['name'] }}</h4>
                        <div class="price">
                            {{ number_format($plan['price_monthly']) }}₺
                            <span>/ay</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                Mağazamı Oluştur
            </button>
            
            <div class="trial-info">
                🎉 14 gün ücretsiz deneme - Kredi kartı gerekmez!
            </div>
        </form>
        
        <div class="login-link">
            Zaten hesabınız var mı? <a href="/admin/login">Giriş Yapın</a>
        </div>
    </div>
    
    <script>
        // Plan seçimi
        document.querySelectorAll('.plan-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
        
        // Subdomain otomatik doldurma
        document.querySelector('input[name="company_name"]').addEventListener('input', function() {
            const subdomainInput = document.querySelector('input[name="subdomain"]');
            if (!subdomainInput.value) {
                subdomainInput.value = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s\-]/g, '')
                    .replace(/\s+/g, '-')
                    .substring(0, 30);
            }
        });
    </script>
</body>
</html>
