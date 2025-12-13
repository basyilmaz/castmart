<x-admin::layouts>
    <x-slot:title>
        Fiyat Analizi
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <span class="text-2xl">📊</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Fiyat Analizi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rakip fiyatlarını takip edin ve otomatik alarm kurun</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="refreshAnalysis()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600">
                🔄 Analizi Yenile
            </button>
            <button onclick="addPriceRule()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white;">
                ➕ Fiyat Kuralı Ekle
            </button>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="grid gap-4 md:grid-cols-4 mb-4">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Toplam Ürün</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['total_products'] ?? 22 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Buybox Kazanan</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['buybox_winning'] ?? 18 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Buybox Kaybeden</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['buybox_losing'] ?? 4 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Aktif Alarm</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['active_alarms'] ?? 3 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <!-- Fiyat Kuralları -->
        <div class="lg:col-span-2 box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Fiyat Güncelleme Kuralları</h3>
            
            <div class="space-y-3" id="price-rules">
                @php
                $rules = [
                    [
                        'name' => 'Otomatik İndirim',
                        'condition' => 'Stok > 50 ise',
                        'action' => '%5 indirim uygula',
                        'active' => true
                    ],
                    [
                        'name' => 'Düşük Stok Fiyatı',
                        'condition' => 'Stok < 10 ise',
                        'action' => '%10 zam uygula',
                        'active' => true
                    ],
                    [
                        'name' => 'Minimum Kar Marjı',
                        'condition' => 'Kar < %15 ise',
                        'action' => 'Fiyatı düzeltme',
                        'active' => false
                    ],
                ];
                @endphp
                @foreach($rules as $rule)
                <div class="flex items-center justify-between rounded border p-3 dark:border-gray-700 {{ $rule['active'] ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : '' }}">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">{{ $rule['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $rule['condition'] }} → {{ $rule['action'] }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" {{ $rule['active'] ? 'checked' : '' }} class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all peer-checked:bg-blue-600 peer-checked:after:translate-x-full dark:bg-gray-700"></div>
                        </label>
                        <button onclick="editRule(this)" class="text-blue-600 hover:text-blue-800" title="Düzenle">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="deleteRule(this)" class="text-red-600 hover:text-red-800" title="Sil">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <button onclick="addPriceRule()" class="mt-3 w-full rounded border-2 border-dashed border-gray-300 py-3 text-gray-500 hover:border-blue-500 hover:text-blue-500 dark:border-gray-700">
                + Yeni Kural Ekle
            </button>
        </div>

        <!-- Alarmlar -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Aktif Alarmlar</h3>
            
            <div class="space-y-3">
                <div class="rounded border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="flex h-2 w-2 rounded-full bg-red-500"></span>
                        <span class="text-sm font-medium text-red-700 dark:text-red-300">Buybox Kaybı</span>
                    </div>
                    <p class="text-xs text-red-600 dark:text-red-400">Gebelik Testi - Rakip fiyatı: 89.90₺</p>
                    <p class="text-xs text-gray-500 mt-1">2 saat önce</p>
                </div>

                <div class="rounded border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="flex h-2 w-2 rounded-full bg-yellow-500"></span>
                        <span class="text-sm font-medium text-yellow-700 dark:text-yellow-300">Düşük Stok</span>
                    </div>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400">Digital Test Kit - Stok: 5 adet</p>
                    <p class="text-xs text-gray-500 mt-1">5 saat önce</p>
                </div>

                <div class="rounded border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="flex h-2 w-2 rounded-full bg-blue-500"></span>
                        <span class="text-sm font-medium text-blue-700 dark:text-blue-300">Fiyat Değişikliği</span>
                    </div>
                    <p class="text-xs text-blue-600 dark:text-blue-400">Ovülasyon Testi - Rakip %10 indirim yaptı</p>
                    <p class="text-xs text-gray-500 mt-1">1 gün önce</p>
                </div>
            </div>

            <a href="#" class="mt-3 block text-center text-sm text-blue-600 hover:underline">
                Tüm alarmları görüntüle →
            </a>
        </div>
    </div>

    <!-- Ürün Fiyat Analizi Tablosu -->
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Ürün Fiyat Analizi</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Maliyet</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Satış Fiyatı</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Kar Marjı</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Buybox</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Rakip Min</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Öneri</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @php
                    $products = [
                        ['name' => 'Dijital Gebelik Testi', 'cost' => 45, 'price' => 99.90, 'margin' => 55, 'buybox' => true, 'competitor' => 104.90],
                        ['name' => 'Ovülasyon Test Kiti', 'cost' => 35, 'price' => 79.90, 'margin' => 45, 'buybox' => true, 'competitor' => 84.90],
                        ['name' => 'Gebelik Testi 2li', 'cost' => 25, 'price' => 59.90, 'margin' => 35, 'buybox' => false, 'competitor' => 54.90],
                        ['name' => 'Erken Gebelik Testi', 'cost' => 40, 'price' => 89.90, 'margin' => 50, 'buybox' => true, 'competitor' => 94.90],
                    ];
                    @endphp
                    @foreach($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ $product['name'] }}</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ number_format($product['cost'], 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($product['price'], 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3 text-right">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $product['margin'] >= 40 ? 'bg-green-100 text-green-700' : ($product['margin'] >= 25 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                %{{ $product['margin'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($product['buybox'])
                                <span class="text-green-600">✓ Kazanıyor</span>
                            @else
                                <span class="text-red-600">✗ Kaybediyor</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right {{ $product['competitor'] < $product['price'] ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                            {{ number_format($product['competitor'], 2, ',', '.') }} ₺
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!$product['buybox'])
                                <button onclick="updatePrice('{{ $product['name'] }}', {{ $product['competitor'] - 1 }})" 
                                        class="text-xs text-blue-600 hover:underline">
                                    {{ number_format($product['competitor'] - 1, 2, ',', '.') }} ₺ yap
                                </button>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Kar Marjı Hesaplama -->
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Kar Marjı Hesaplama</h3>
        
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maliyet (₺)</label>
                <input type="number" id="calc-cost" value="50" step="0.01" 
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                       oninput="calculateMargin()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Satış Fiyatı (₺)</label>
                <input type="number" id="calc-price" value="100" step="0.01" 
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                       oninput="calculateMargin()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Komisyon (%)</label>
                <input type="number" id="calc-commission" value="18" step="0.1" 
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                       oninput="calculateMargin()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Net Kar</label>
                <div class="rounded bg-gray-100 px-3 py-2 dark:bg-gray-800">
                    <span id="calc-result" class="text-lg font-bold text-green-600">32.00 ₺</span>
                    <span id="calc-margin" class="text-sm text-gray-500 ml-2">(%32)</span>
                </div>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            window.refreshAnalysis = function() {
                location.reload();
            }

            window.addPriceRule = function() {
                alert('Fiyat kuralı ekleme özelliği geliştirme aşamasında.');
            }

            window.editRule = function(btn) {
                alert('Kural düzenleme özelliği geliştirme aşamasında.');
            }

            window.deleteRule = function(btn) {
                if (confirm('Bu kuralı silmek istediğinize emin misiniz?')) {
                    btn.closest('.flex').parentElement.remove();
                }
            }

            window.updatePrice = function(productName, newPrice) {
                if (confirm(`${productName} için fiyatı ${newPrice.toFixed(2)} ₺ yapmak istiyor musunuz?`)) {
                    alert('Fiyat güncelleme özelliği geliştirme aşamasında.');
                }
            }

            window.calculateMargin = function() {
                const cost = parseFloat(document.getElementById('calc-cost').value) || 0;
                const price = parseFloat(document.getElementById('calc-price').value) || 0;
                const commission = parseFloat(document.getElementById('calc-commission').value) || 0;
                
                const commissionAmount = price * (commission / 100);
                const netProfit = price - cost - commissionAmount;
                const marginPercent = price > 0 ? (netProfit / price) * 100 : 0;
                
                document.getElementById('calc-result').textContent = netProfit.toFixed(2) + ' ₺';
                document.getElementById('calc-margin').textContent = `(%${marginPercent.toFixed(1)})`;
                
                const resultEl = document.getElementById('calc-result');
                resultEl.className = netProfit >= 0 ? 'text-lg font-bold text-green-600' : 'text-lg font-bold text-red-600';
            }

            // Initial calculation
            calculateMargin();
        </script>
    @endPushOnce
</x-admin::layouts>
