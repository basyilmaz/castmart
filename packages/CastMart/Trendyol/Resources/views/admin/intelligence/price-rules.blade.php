<x-admin::layouts>
    <x-slot:title>
        Otomatik Fiyat Kuralları
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">⚙️</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Fiyat Kuralları</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">BuyBox kazanmak için otomatik fiyat ayarlama</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="showRuleModal()" class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                ➕ Yeni Kural Ekle
            </button>
        </div>
    </div>

    <!-- Global Ayarlar -->
    <div class="box-shadow rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">🎚️ Global Ayarlar</h3>
        
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Minimum Kar Marjı (%)</label>
                <input type="number" id="min-margin" value="10" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <p class="text-xs text-gray-500 mt-1">Bu marjın altına fiyat düşmez</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maksimum Fiyat Düşürme (%)</label>
                <input type="number" id="max-decrease" value="15" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                <p class="text-xs text-gray-500 mt-1">Tek seferde max düşürme</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kontrol Sıklığı</label>
                <select id="check-frequency" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="15">15 dakika</option>
                    <option value="30" selected>30 dakika</option>
                    <option value="60">1 saat</option>
                    <option value="120">2 saat</option>
                </select>
            </div>
        </div>
        
        <div class="mt-4 flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" id="auto-mode" checked class="rounded border-gray-300">
                Otomatik fiyat güncelleme aktif
            </label>
            <button onclick="saveGlobalSettings()" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">
                💾 Kaydet
            </button>
        </div>
    </div>

    <!-- Mevcut Kurallar -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📋 Mevcut Kurallar</h3>
        
        <div class="space-y-4" id="rules-container">
            <!-- Kural 1 -->
            <div class="border rounded-lg p-4 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🏆</span>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">BuyBox Kaybetme Kuralı</p>
                            <p class="text-xs text-gray-500">Rakip bizden ucuzsa otomatik eşleş</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">Aktif</span>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-4 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Tetikleyici</p>
                        <p class="font-medium">Rakip fiyat &lt; Benim fiyat</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Eylem</p>
                        <p class="font-medium">Fiyatı rakibe eşitle (- 0.01₺)</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Kapsam</p>
                        <p class="font-medium">Tüm ürünler</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Son Tetiklenme</p>
                        <p class="font-medium">2 saat önce (3 ürün)</p>
                    </div>
                </div>
            </div>

            <!-- Kural 2 -->
            <div class="border rounded-lg p-4 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📈</span>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">Fiyat Artırma Kuralı</p>
                            <p class="text-xs text-gray-500">Rakip yoksa fiyatı artır</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">Aktif</span>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-4 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Tetikleyici</p>
                        <p class="font-medium">Rakip stok = 0</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Eylem</p>
                        <p class="font-medium">Fiyatı %5 artır</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Kapsam</p>
                        <p class="font-medium">Moda kategorisi</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Son Tetiklenme</p>
                        <p class="font-medium">Dün (1 ürün)</p>
                    </div>
                </div>
            </div>

            <!-- Kural 3 -->
            <div class="border rounded-lg p-4 dark:border-gray-700 opacity-60">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">Stok Azalma Kuralı</p>
                            <p class="text-xs text-gray-500">Stok azaldığında fiyat artır</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-medium">Pasif</span>
                        <button class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="grid gap-3 md:grid-cols-4 text-sm">
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Tetikleyici</p>
                        <p class="font-medium">Stok &lt; 5 adet</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Eylem</p>
                        <p class="font-medium">Fiyatı %10 artır</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Kapsam</p>
                        <p class="font-medium">Tüm ürünler</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 rounded p-2">
                        <p class="text-xs text-gray-500">Son Tetiklenme</p>
                        <p class="font-medium">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fiyat Geçmişi -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📊 Fiyat Değişim Geçmişi</h3>
        
        <div class="mb-4 flex gap-2">
            <button class="px-4 py-2 bg-indigo-600 text-white rounded text-sm">Son 7 gün</button>
            <button class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded text-sm">Son 30 gün</button>
            <button class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded text-sm">Tüm zamanlar</button>
        </div>
        
        <!-- Grafik alanı (SVG ile basit çizgi grafik) -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
            <svg viewBox="0 0 400 150" class="w-full h-48">
                <!-- Arka plan çizgileri -->
                <line x1="40" y1="20" x2="380" y2="20" stroke="#e5e7eb" stroke-width="1"/>
                <line x1="40" y1="50" x2="380" y2="50" stroke="#e5e7eb" stroke-width="1"/>
                <line x1="40" y1="80" x2="380" y2="80" stroke="#e5e7eb" stroke-width="1"/>
                <line x1="40" y1="110" x2="380" y2="110" stroke="#e5e7eb" stroke-width="1"/>
                
                <!-- Y ekseni etiketleri -->
                <text x="35" y="25" text-anchor="end" class="text-xs fill-gray-500">80%</text>
                <text x="35" y="55" text-anchor="end" class="text-xs fill-gray-500">60%</text>
                <text x="35" y="85" text-anchor="end" class="text-xs fill-gray-500">40%</text>
                <text x="35" y="115" text-anchor="end" class="text-xs fill-gray-500">20%</text>
                
                <!-- BuyBox oranı çizgisi -->
                <polyline 
                    points="60,90 100,85 140,70 180,75 220,60 260,55 300,50 340,45" 
                    fill="none" 
                    stroke="#22c55e" 
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"/>
                
                <!-- Noktalar -->
                <circle cx="60" cy="90" r="4" fill="#22c55e"/>
                <circle cx="100" cy="85" r="4" fill="#22c55e"/>
                <circle cx="140" cy="70" r="4" fill="#22c55e"/>
                <circle cx="180" cy="75" r="4" fill="#22c55e"/>
                <circle cx="220" cy="60" r="4" fill="#22c55e"/>
                <circle cx="260" cy="55" r="4" fill="#22c55e"/>
                <circle cx="300" cy="50" r="4" fill="#22c55e"/>
                <circle cx="340" cy="45" r="4" fill="#22c55e"/>
                
                <!-- X ekseni etiketleri -->
                <text x="60" y="135" text-anchor="middle" class="text-xs fill-gray-500">Pzt</text>
                <text x="100" y="135" text-anchor="middle" class="text-xs fill-gray-500">Sal</text>
                <text x="140" y="135" text-anchor="middle" class="text-xs fill-gray-500">Çar</text>
                <text x="180" y="135" text-anchor="middle" class="text-xs fill-gray-500">Per</text>
                <text x="220" y="135" text-anchor="middle" class="text-xs fill-gray-500">Cum</text>
                <text x="260" y="135" text-anchor="middle" class="text-xs fill-gray-500">Cts</text>
                <text x="300" y="135" text-anchor="middle" class="text-xs fill-gray-500">Paz</text>
                <text x="340" y="135" text-anchor="middle" class="text-xs fill-gray-500">Bugün</text>
            </svg>
        </div>
        
        <div class="flex items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-600 dark:text-gray-400">BuyBox Oranı</span>
            </div>
            <div class="ml-auto text-gray-600 dark:text-gray-400">
                Bu hafta: <span class="font-bold text-green-600">+15%</span> artış
            </div>
        </div>
        
        <!-- Son değişiklikler tablosu -->
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Son Fiyat Değişiklikleri</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Tarih</th>
                            <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">Ürün</th>
                            <th class="px-4 py-2 text-right text-gray-600 dark:text-gray-300">Eski Fiyat</th>
                            <th class="px-4 py-2 text-right text-gray-600 dark:text-gray-300">Yeni Fiyat</th>
                            <th class="px-4 py-2 text-center text-gray-600 dark:text-gray-300">Sebep</th>
                            <th class="px-4 py-2 text-center text-gray-600 dark:text-gray-300">Sonuç</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        <tr>
                            <td class="px-4 py-2 text-gray-500">Bugün 14:30</td>
                            <td class="px-4 py-2">Mavi Kazak - Erkek</td>
                            <td class="px-4 py-2 text-right">159.90 ₺</td>
                            <td class="px-4 py-2 text-right font-medium text-green-600">149.90 ₺</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">Oto Kural</span></td>
                            <td class="px-4 py-2 text-center"><span class="text-green-600">✓ BuyBox</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-500">Bugün 11:15</td>
                            <td class="px-4 py-2">Spor Ayakkabı - Unisex</td>
                            <td class="px-4 py-2 text-right">329.90 ₺</td>
                            <td class="px-4 py-2 text-right font-medium text-red-600">349.90 ₺</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs">Manuel</span></td>
                            <td class="px-4 py-2 text-center"><span class="text-green-600">✓ BuyBox</span></td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2 text-gray-500">Dün 18:45</td>
                            <td class="px-4 py-2">Kış Montu - Erkek</td>
                            <td class="px-4 py-2 text-right">549.90 ₺</td>
                            <td class="px-4 py-2 text-right font-medium">549.90 ₺</td>
                            <td class="px-4 py-2 text-center"><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">Min Marj</span></td>
                            <td class="px-4 py-2 text-center"><span class="text-red-600">✗ Kaybedildi</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kural Ekleme Modal -->
    <div id="rule-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 w-full max-w-lg mx-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">➕ Yeni Kural Ekle</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kural Adı</label>
                    <input type="text" id="rule-name" placeholder="Örn: BuyBox Koruma Kuralı" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tetikleyici</label>
                    <select id="rule-trigger" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="competitor_cheaper">Rakip fiyat &lt; Benim fiyat</option>
                        <option value="buybox_lost">BuyBox kaybedildiğinde</option>
                        <option value="stock_low">Stok azaldığında</option>
                        <option value="competitor_stock_zero">Rakip stok bittiğinde</option>
                        <option value="time_based">Belirli zamanda</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Eylem</label>
                    <select id="rule-action" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="match_minus">Rakibe eşitle (-0.01₺)</option>
                        <option value="decrease_percent">Yüzde düşür</option>
                        <option value="increase_percent">Yüzde artır</option>
                        <option value="set_price">Sabit fiyat</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kapsam</label>
                    <select id="rule-scope" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="all">Tüm ürünler</option>
                        <option value="category">Belirli kategori</option>
                        <option value="selected">Seçili ürünler</option>
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="rule-active" checked class="rounded border-gray-300">
                    <label for="rule-active" class="text-sm text-gray-700 dark:text-gray-300">Kuralı aktif olarak başlat</label>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button onclick="closeRuleModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    İptal
                </button>
                <button onclick="saveRule()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Kuralı Kaydet
                </button>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            // Kural modalı göster
            window.showRuleModal = function() {
                document.getElementById('rule-modal').classList.remove('hidden');
                document.getElementById('rule-modal').classList.add('flex');
            }

            // Kural modalı kapat
            window.closeRuleModal = function() {
                document.getElementById('rule-modal').classList.add('hidden');
                document.getElementById('rule-modal').classList.remove('flex');
            }

            // Kural kaydet
            window.saveRule = function() {
                const name = document.getElementById('rule-name').value;
                if (!name) {
                    alert('Lütfen kural adı girin.');
                    return;
                }
                alert('Kural kaydedildi: ' + name);
                closeRuleModal();
            }

            // Global ayarları kaydet
            window.saveGlobalSettings = function() {
                const minMargin = document.getElementById('min-margin').value;
                const maxDecrease = document.getElementById('max-decrease').value;
                const frequency = document.getElementById('check-frequency').value;
                const autoMode = document.getElementById('auto-mode').checked;
                
                alert('Ayarlar kaydedildi!\nMin Marj: %' + minMargin + '\nMax Düşürme: %' + maxDecrease + '\nSıklık: ' + frequency + ' dk\nOtomatik: ' + (autoMode ? 'Açık' : 'Kapalı'));
            }
        </script>
    @endPushOnce
</x-admin::layouts>
