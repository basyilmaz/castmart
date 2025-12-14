<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Param ile Ödeme - CastMart</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .payment-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .payment-header .amount {
            font-size: 36px;
            font-weight: bold;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        .card-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        
        .card-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card-info img {
            height: 24px;
        }
        
        .card-info span {
            color: #666;
            font-size: 14px;
        }
        
        .btn-pay {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.3);
        }
        
        .btn-pay:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .security-badges img {
            height: 30px;
            opacity: 0.7;
        }
        
        .installment-info {
            background: #fff3cd;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #856404;
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-pay.loading .spinner {
            display: inline-block;
        }

        .btn-pay.loading .btn-text {
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>Param ile Güvenli Ödeme</h1>
            <div class="amount">{{ number_format($cart->grand_total, 2) }} ₺</div>
        </div>
        
        <div class="payment-body">
            <form id="paymentForm" action="{{ route('param.initiate') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="card_holder">Kart Üzerindeki İsim</label>
                    <input type="text" id="card_holder" name="card_holder" placeholder="AD SOYAD" required 
                           style="text-transform: uppercase;">
                </div>
                
                <div class="form-group">
                    <label for="card_number">Kart Numarası</label>
                    <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" 
                           maxlength="19" required>
                </div>
                
                <div class="card-info" id="cardInfo" style="display: none;">
                    <img id="cardLogo" src="" alt="">
                    <span id="cardBank"></span>
                </div>
                
                <div class="card-row">
                    <div class="form-group">
                        <label for="expiry_month">Ay</label>
                        <select id="expiry_month" name="expiry_month" required>
                            <option value="">Ay</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">
                                    {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_year">Yıl</label>
                        <select id="expiry_year" name="expiry_year" required>
                            <option value="">Yıl</option>
                            @for($i = date('y'); $i <= date('y') + 15; $i++)
                                <option value="{{ $i }}">20{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="***" maxlength="4" required>
                    </div>
                </div>
                
                @if($installments && $cart->grand_total >= config('param.installments.min_amount', 100))
                <div class="form-group">
                    <label for="installment">Taksit Seçimi</label>
                    <select id="installment" name="installment">
                        <option value="1">Tek Çekim</option>
                    </select>
                </div>
                
                <div class="installment-info" id="installmentInfo" style="display: none;">
                    Aylık ödeme: <strong id="monthlyPayment"></strong>
                </div>
                @endif
                
                <button type="submit" class="btn-pay" id="payBtn">
                    <span class="spinner"></span>
                    <span class="btn-text">Ödemeyi Tamamla</span>
                </button>
            </form>
            
            <div class="security-badges">
                <img src="https://www.param.com.tr/images/logo.png" alt="Param" style="height: 40px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="MasterCard">
            </div>
        </div>
    </div>
    
    <script>
        // Kart numarası formatla
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
            
            // BIN sorgusu (6 haneden sonra)
            if (value.replace(/\s/g, '').length >= 6) {
                queryBin(value.replace(/\s/g, '').substring(0, 6));
            }
        });
        
        // CVV sadece rakam
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
        
        // BIN sorgula
        function queryBin(bin) {
            fetch('{{ route("param.api.bin-query") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ bin: bin })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.bank_name) {
                    document.getElementById('cardInfo').style.display = 'flex';
                    document.getElementById('cardBank').textContent = data.bank_name;
                    
                    // Taksit seçeneklerini güncelle
                    loadInstallments(bin);
                }
            });
        }
        
        // Taksit seçenekleri
        function loadInstallments(bin) {
            fetch('{{ route("param.api.installments") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ bin: bin })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.installments.length > 0) {
                    const select = document.getElementById('installment');
                    select.innerHTML = '<option value="1">Tek Çekim - {{ number_format($cart->grand_total, 2) }} ₺</option>';
                    
                    data.installments.forEach(inst => {
                        if (inst.count > 1) {
                            select.innerHTML += `<option value="${inst.count}">${inst.count} Taksit - Aylık ${inst.monthly.toFixed(2)} ₺ (Toplam: ${inst.total.toFixed(2)} ₺)</option>`;
                        }
                    });
                }
            });
        }
        
        // Taksit seçildiğinde
        document.getElementById('installment')?.addEventListener('change', function(e) {
            const option = e.target.options[e.target.selectedIndex];
            const info = document.getElementById('installmentInfo');
            
            if (e.target.value > 1) {
                info.style.display = 'block';
                document.getElementById('monthlyPayment').textContent = option.textContent.split('Aylık ')[1]?.split(' ')[0] || '';
            } else {
                info.style.display = 'none';
            }
        });
        
        // Form gönder
        document.getElementById('paymentForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('payBtn');
            btn.classList.add('loading');
            btn.disabled = true;
        });
    </script>
</body>
</html>
