<x-admin::layouts>
    <x-slot:title>
        Komisyon Hesaplayıcı
    </x-slot>

    <style>
        .calc-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .input-group {
            transition: all 0.2s ease;
        }
        .input-group:focus-within {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .result-positive {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .result-negative {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
        .result-neutral {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
    </style>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                <span class="text-2xl">🧮</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Komisyon Hesaplayıcı</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trendyol kar-zarar analizinizi yapın</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="resetForm()" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                🔄 Sıfırla
            </button>
        </div>
    </div>

    <!-- Ana Grid -->
    <div class="grid gap-6 lg:grid-cols-5">
        
        <!-- Sol: Giriş Formu (3 kolon) -->
        <div class="lg:col-span-3 space-y-4">
            
            <!-- Maliyet Kartı -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-5 py-3">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        💰 Maliyet Bilgileri
                    </h2>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                Alış Fiyatı (KDV Hariç) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" id="purchase-price" value="50" step="0.01" min="0"
                                       class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 pr-10 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-0 text-lg font-medium"
                                       oninput="calculateAll()">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">₺</span>
                            </div>
                        </div>
                        
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                KDV Oranı (Alış)
                            </label>
                            <select id="purchase-vat" 
                                    class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-0 text-lg"
                                    onchange="calculateAll()">
                                <option value="0">%0</option>
                                <option value="1">%1</option>
                                <option value="10">%10</option>
                                <option value="20" selected>%20</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Satış Kartı -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-5 py-3">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        💵 Satış Bilgileri
                    </h2>
                </div>
                <div class="p-5 space-y-4">
                    <div class="input-group">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                            Satış Fiyatı (KDV Dahil) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" id="sale-price" value="119.90" step="0.01" min="0"
                                   class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 pr-10 dark:bg-gray-800 dark:text-white focus:border-green-500 focus:ring-0 text-xl font-bold"
                                   oninput="calculateAll()">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">₺</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Komisyon & Kargo Kartı -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-amber-500 px-5 py-3">
                    <h2 class="text-white font-semibold flex items-center gap-2">
                        📦 Komisyon & Kargo
                    </h2>
                </div>
                <div class="p-5 space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                Kategori
                            </label>
                            <select id="category-select" 
                                    class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 dark:bg-gray-800 dark:text-white focus:border-orange-500 focus:ring-0"
                                    onchange="updateCommissionFromCategory()">
                                <option value="18">Giyim & Moda (%18)</option>
                                <option value="16">Ayakkabı & Çanta (%16)</option>
                                <option value="17">Kozmetik (%17)</option>
                                <option value="15">Ev & Yaşam (%15)</option>
                                <option value="12">Elektronik (%12)</option>
                                <option value="10">Bilgisayar (%10)</option>
                                <option value="8">Telefon (%8)</option>
                                <option value="14">Spor (%14)</option>
                                <option value="0">Manuel Giriş</option>
                            </select>
                        </div>
                        
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                Komisyon Oranı
                            </label>
                            <div class="relative">
                                <input type="number" id="commission-rate" value="18" step="0.1" min="0" max="50"
                                       class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 pr-10 dark:bg-gray-800 dark:text-white focus:border-orange-500 focus:ring-0 text-lg font-medium"
                                       oninput="calculateAll()">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                Kargo Firması
                            </label>
                            <select id="cargo-company" 
                                    class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 dark:bg-gray-800 dark:text-white focus:border-orange-500 focus:ring-0"
                                    onchange="updateCargoPrice()">
                                <option value="0">Trendyol Karşılar (0₺)</option>
                                <option value="14.99">Yurtiçi Kargo (14.99₺)</option>
                                <option value="13.99">Aras Kargo (13.99₺)</option>
                                <option value="15.99">MNG Kargo (15.99₺)</option>
                                <option value="12.99">Sürat Kargo (12.99₺)</option>
                                <option value="-1">Manuel Giriş</option>
                            </select>
                        </div>
                        
                        <div class="input-group">
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                Kargo Ücreti
                            </label>
                            <div class="relative">
                                <input type="number" id="cargo-price" value="0" step="0.01" min="0"
                                       class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-700 px-4 py-3 pr-10 dark:bg-gray-800 dark:text-white focus:border-orange-500 focus:ring-0 text-lg font-medium"
                                       oninput="calculateAll()">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">₺</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sağ: Sonuçlar (2 kolon) -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Ana Sonuç Kartı -->
            <div id="result-card" class="result-positive rounded-2xl p-6 text-white shadow-xl">
                <div class="text-center">
                    <p class="text-sm opacity-80 mb-1">Net Kârınız</p>
                    <p id="net-profit" class="text-5xl font-bold mb-2">15.42 ₺</p>
                    <div class="flex justify-center gap-4 text-sm">
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            Marj: <strong id="profit-margin">12.9%</strong>
                        </span>
                        <span class="bg-white/20 px-3 py-1 rounded-full">
                            ROI: <strong id="roi-value">25.7%</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Durum Kartı -->
            <div id="status-card" class="bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl" id="status-emoji">✅</span>
                    <div>
                        <p class="font-semibold text-green-700 dark:text-green-400" id="status-title">Kârlı Satış</p>
                        <p class="text-sm text-green-600 dark:text-green-500" id="status-desc">Bu ürün size para kazandırıyor.</p>
                    </div>
                </div>
            </div>

            <!-- Detay Kartı -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <button onclick="toggleDetails()" class="w-full px-5 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">📊 Detaylı Hesaplama</span>
                    <svg id="details-arrow" class="w-5 h-5 text-gray-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="details-content" class="hidden border-t dark:border-gray-800">
                    <div class="p-5 space-y-3">
                        <div class="flex justify-between py-2 border-b dark:border-gray-800">
                            <span class="text-gray-600 dark:text-gray-400">Satış Fiyatı (KDV Dahil)</span>
                            <span class="font-medium" id="detail-sale">119.90 ₺</span>
                        </div>
                        <div class="flex justify-between py-2 border-b dark:border-gray-800">
                            <span class="text-gray-600 dark:text-gray-400">Trendyol Komisyonu</span>
                            <span class="font-medium text-red-500" id="detail-commission">-21.58 ₺</span>
                        </div>
                        <div class="flex justify-between py-2 border-b dark:border-gray-800">
                            <span class="text-gray-600 dark:text-gray-400">Kargo Ücreti</span>
                            <span class="font-medium text-red-500" id="detail-cargo">-0.00 ₺</span>
                        </div>
                        <div class="flex justify-between py-2 border-b dark:border-gray-800">
                            <span class="text-gray-600 dark:text-gray-400">Ürün Maliyeti (KDV Dahil)</span>
                            <span class="font-medium text-red-500" id="detail-cost">-60.00 ₺</span>
                        </div>
                        <div class="flex justify-between py-2 border-b dark:border-gray-800">
                            <span class="text-gray-600 dark:text-gray-400">Ödenecek KDV</span>
                            <span class="font-medium text-red-500" id="detail-vat">-3.32 ₺</span>
                        </div>
                        <div class="flex justify-between py-3 bg-gray-50 dark:bg-gray-800 -mx-5 px-5 mt-3 rounded-b-xl">
                            <span class="font-bold text-gray-800 dark:text-white">NET KÂR</span>
                            <span class="font-bold text-xl" id="detail-net">15.42 ₺</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Başabaş Kartı -->
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-2xl p-5">
                <h3 class="font-semibold text-purple-700 dark:text-purple-400 mb-3">🎯 Başabaş Analizi</h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Minimum Satış Fiyatı</span>
                        <span class="font-bold text-purple-600" id="breakeven-price">102.44 ₺</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">%15 Kâr için Fiyat</span>
                        <span class="font-bold text-green-600" id="target-price-15">117.81 ₺</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">%20 Kâr için Fiyat</span>
                        <span class="font-bold text-green-600" id="target-price-20">123.05 ₺</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @pushOnce('scripts')
        <script>
            // Sayfa yüklendiğinde hesapla
            document.addEventListener('DOMContentLoaded', calculateAll);

            // Ana hesaplama fonksiyonu
            function calculateAll() {
                // Değerleri al
                const purchasePrice = parseFloat(document.getElementById('purchase-price').value) || 0;
                const purchaseVat = parseFloat(document.getElementById('purchase-vat').value) || 0;
                const salePrice = parseFloat(document.getElementById('sale-price').value) || 0;
                const commissionRate = parseFloat(document.getElementById('commission-rate').value) || 0;
                const cargoPrice = parseFloat(document.getElementById('cargo-price').value) || 0;

                // Hesaplamalar
                const purchasePriceWithVat = purchasePrice * (1 + purchaseVat / 100);
                const saleVatRate = 20; // Satış KDV oranı
                const salePriceWithoutVat = salePrice / (1 + saleVatRate / 100);
                
                const commissionAmount = salePrice * (commissionRate / 100);
                const saleVat = salePrice - salePriceWithoutVat;
                const purchaseVatAmount = purchasePrice * (purchaseVat / 100);
                const vatPayable = saleVat - purchaseVatAmount;
                
                const netProfit = salePrice - commissionAmount - cargoPrice - purchasePriceWithVat - (vatPayable > 0 ? vatPayable : 0);
                const profitMargin = salePrice > 0 ? (netProfit / salePrice) * 100 : 0;
                const roi = purchasePriceWithVat > 0 ? (netProfit / purchasePriceWithVat) * 100 : 0;

                // Sonuçları göster
                document.getElementById('net-profit').textContent = netProfit.toFixed(2) + ' ₺';
                document.getElementById('profit-margin').textContent = profitMargin.toFixed(1) + '%';
                document.getElementById('roi-value').textContent = roi.toFixed(1) + '%';

                // Detayları güncelle
                document.getElementById('detail-sale').textContent = salePrice.toFixed(2) + ' ₺';
                document.getElementById('detail-commission').textContent = '-' + commissionAmount.toFixed(2) + ' ₺';
                document.getElementById('detail-cargo').textContent = '-' + cargoPrice.toFixed(2) + ' ₺';
                document.getElementById('detail-cost').textContent = '-' + purchasePriceWithVat.toFixed(2) + ' ₺';
                document.getElementById('detail-vat').textContent = (vatPayable > 0 ? '-' : '') + Math.abs(vatPayable).toFixed(2) + ' ₺';
                document.getElementById('detail-net').textContent = netProfit.toFixed(2) + ' ₺';
                document.getElementById('detail-net').className = 'font-bold text-xl ' + (netProfit >= 0 ? 'text-green-600' : 'text-red-600');

                // Sonuç kartını güncelle
                const resultCard = document.getElementById('result-card');
                const statusCard = document.getElementById('status-card');
                
                if (netProfit > 0) {
                    resultCard.className = 'result-positive rounded-2xl p-6 text-white shadow-xl';
                    statusCard.className = 'bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 rounded-2xl p-4';
                    document.getElementById('status-emoji').textContent = '✅';
                    document.getElementById('status-title').textContent = 'Kârlı Satış';
                    document.getElementById('status-title').className = 'font-semibold text-green-700 dark:text-green-400';
                    document.getElementById('status-desc').textContent = 'Bu ürün size para kazandırıyor.';
                    document.getElementById('status-desc').className = 'text-sm text-green-600 dark:text-green-500';
                } else if (netProfit === 0) {
                    resultCard.className = 'result-neutral rounded-2xl p-6 text-white shadow-xl';
                    statusCard.className = 'bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-200 dark:border-yellow-800 rounded-2xl p-4';
                    document.getElementById('status-emoji').textContent = '⚖️';
                    document.getElementById('status-title').textContent = 'Başabaş';
                    document.getElementById('status-title').className = 'font-semibold text-yellow-700 dark:text-yellow-400';
                    document.getElementById('status-desc').textContent = 'Ne kâr ne zarar ediyorsunuz.';
                    document.getElementById('status-desc').className = 'text-sm text-yellow-600 dark:text-yellow-500';
                } else {
                    resultCard.className = 'result-negative rounded-2xl p-6 text-white shadow-xl';
                    statusCard.className = 'bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 rounded-2xl p-4';
                    document.getElementById('status-emoji').textContent = '❌';
                    document.getElementById('status-title').textContent = 'Zararlı Satış';
                    document.getElementById('status-title').className = 'font-semibold text-red-700 dark:text-red-400';
                    document.getElementById('status-desc').textContent = 'Bu fiyatla para kaybediyorsunuz!';
                    document.getElementById('status-desc').className = 'text-sm text-red-600 dark:text-red-500';
                }

                // Başabaş hesapla
                calculateBreakeven(purchasePriceWithVat, commissionRate, cargoPrice, saleVatRate, purchaseVat);
            }

            function calculateBreakeven(cost, commissionRate, cargoPrice, saleVatRate, purchaseVatRate) {
                // Başabaş fiyatı hesapla
                // salePrice - (salePrice * commission/100) - cargo - cost - vatPayable = 0
                // Basitleştirilmiş formül
                const breakeven = (cost + cargoPrice) / (1 - commissionRate / 100 - 0.02); // %2 VAT marjı için
                const target15 = breakeven * 1.15;
                const target20 = breakeven * 1.20;

                document.getElementById('breakeven-price').textContent = breakeven.toFixed(2) + ' ₺';
                document.getElementById('target-price-15').textContent = target15.toFixed(2) + ' ₺';
                document.getElementById('target-price-20').textContent = target20.toFixed(2) + ' ₺';
            }

            function updateCommissionFromCategory() {
                const select = document.getElementById('category-select');
                const value = select.value;
                if (value !== '0') {
                    document.getElementById('commission-rate').value = value;
                    calculateAll();
                }
            }

            function updateCargoPrice() {
                const select = document.getElementById('cargo-company');
                const value = select.value;
                if (value !== '-1') {
                    document.getElementById('cargo-price').value = value;
                    calculateAll();
                }
            }

            function toggleDetails() {
                const content = document.getElementById('details-content');
                const arrow = document.getElementById('details-arrow');
                content.classList.toggle('hidden');
                arrow.classList.toggle('rotate-180');
            }

            function resetForm() {
                document.getElementById('purchase-price').value = '50';
                document.getElementById('purchase-vat').value = '20';
                document.getElementById('sale-price').value = '119.90';
                document.getElementById('commission-rate').value = '18';
                document.getElementById('cargo-price').value = '0';
                document.getElementById('category-select').value = '18';
                document.getElementById('cargo-company').value = '0';
                calculateAll();
            }
        </script>
    @endPushOnce
</x-admin::layouts>
