<x-admin::layouts>
    <x-slot:title>
        Satış & Stok Tahmini
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">🔮</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Tahmin Motoru</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">AI destekli satış ve stok tahmini</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="runPrediction()" class="flex items-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                ⚡ Tahmin Çalıştır
            </button>
        </div>
    </div>

    <!-- Özet Kartları -->
    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="box-shadow rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Tahmini Haftalık Satış</p>
                    <p class="text-3xl font-bold">47</p>
                </div>
                <span class="text-4xl opacity-80">📈</span>
            </div>
            <p class="text-sm mt-2 opacity-80">+12% geçen haftaya göre</p>
        </div>
        
        <div class="box-shadow rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Tahmini Gelir</p>
                    <p class="text-3xl font-bold">14.5K ₺</p>
                </div>
                <span class="text-4xl opacity-80">💰</span>
            </div>
            <p class="text-sm mt-2 opacity-80">Bu hafta beklenen</p>
        </div>
        
        <div class="box-shadow rounded-lg bg-gradient-to-br from-red-500 to-red-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Stok Kritik</p>
                    <p class="text-3xl font-bold">5</p>
                </div>
                <span class="text-4xl opacity-80">⚠️</span>
            </div>
            <p class="text-sm mt-2 opacity-80">7 gün içinde tükenecek</p>
        </div>
        
        <div class="box-shadow rounded-lg bg-gradient-to-br from-green-500 to-green-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Tahmin Doğruluğu</p>
                    <p class="text-3xl font-bold">87%</p>
                </div>
                <span class="text-4xl opacity-80">✓</span>
            </div>
            <p class="text-sm mt-2 opacity-80">Son 30 günlük</p>
        </div>
    </div>

    <!-- Tahmin Grafikleri -->
    <div class="grid gap-6 mb-6 lg:grid-cols-2">
        <!-- Satış Tahmini Grafiği -->
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📊 Satış Tahmini (7 Gün)</h3>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <svg viewBox="0 0 400 150" class="w-full h-40">
                    <!-- Grid -->
                    <line x1="40" y1="20" x2="380" y2="20" stroke="#e5e7eb" stroke-dasharray="4"/>
                    <line x1="40" y1="50" x2="380" y2="50" stroke="#e5e7eb" stroke-dasharray="4"/>
                    <line x1="40" y1="80" x2="380" y2="80" stroke="#e5e7eb" stroke-dasharray="4"/>
                    <line x1="40" y1="110" x2="380" y2="110" stroke="#e5e7eb" stroke-dasharray="4"/>
                    
                    <!-- Geçmiş (mavi) -->
                    <polyline 
                        points="60,80 100,70 140,85 180,65" 
                        fill="none" 
                        stroke="#3b82f6" 
                        stroke-width="3"
                        stroke-linecap="round"/>
                    
                    <!-- Tahmin (mor, kesikli) -->
                    <polyline 
                        points="180,65 220,55 260,50 300,45 340,40" 
                        fill="none" 
                        stroke="#8b5cf6" 
                        stroke-width="3"
                        stroke-dasharray="8,4"
                        stroke-linecap="round"/>
                    
                    <!-- Noktalar -->
                    <circle cx="60" cy="80" r="4" fill="#3b82f6"/>
                    <circle cx="100" cy="70" r="4" fill="#3b82f6"/>
                    <circle cx="140" cy="85" r="4" fill="#3b82f6"/>
                    <circle cx="180" cy="65" r="5" fill="#8b5cf6" stroke="white" stroke-width="2"/>
                    <circle cx="220" cy="55" r="4" fill="#8b5cf6"/>
                    <circle cx="260" cy="50" r="4" fill="#8b5cf6"/>
                    <circle cx="300" cy="45" r="4" fill="#8b5cf6"/>
                    <circle cx="340" cy="40" r="4" fill="#8b5cf6"/>
                    
                    <!-- X ekseni -->
                    <text x="60" y="135" text-anchor="middle" class="text-xs fill-gray-500">-3g</text>
                    <text x="100" y="135" text-anchor="middle" class="text-xs fill-gray-500">-2g</text>
                    <text x="140" y="135" text-anchor="middle" class="text-xs fill-gray-500">Dün</text>
                    <text x="180" y="135" text-anchor="middle" class="text-xs fill-gray-500 font-bold">Bugün</text>
                    <text x="220" y="135" text-anchor="middle" class="text-xs fill-gray-400">+1g</text>
                    <text x="260" y="135" text-anchor="middle" class="text-xs fill-gray-400">+2g</text>
                    <text x="300" y="135" text-anchor="middle" class="text-xs fill-gray-400">+3g</text>
                    <text x="340" y="135" text-anchor="middle" class="text-xs fill-gray-400">+4g</text>
                </svg>
            </div>
            
            <div class="flex items-center gap-4 mt-4 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Gerçek Satış</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                    <span class="text-gray-600 dark:text-gray-400">Tahmin</span>
                </div>
            </div>
        </div>

        <!-- Stok Durumu -->
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📦 Stok Tükenme Tahmini</h3>
            
            <div class="space-y-4">
                <div class="flex items-center gap-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-200">Mavi Kazak - Erkek</p>
                        <p class="text-xs text-gray-500">Stok: 5 | Günlük satış: 2</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600">2-3 gün</p>
                        <p class="text-xs text-gray-500">tükenecek</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-200">Siyah Pantolon - Erkek</p>
                        <p class="text-xs text-gray-500">Stok: 12 | Günlük satış: 1.5</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-yellow-600">8 gün</p>
                        <p class="text-xs text-gray-500">tükenecek</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-200">Beyaz Gömlek - Kadın</p>
                        <p class="text-xs text-gray-500">Stok: 45 | Günlük satış: 1</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-green-600">45 gün</p>
                        <p class="text-xs text-gray-500">yeterli</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500">
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-200">Spor Ayakkabı - Unisex</p>
                        <p class="text-xs text-gray-500">Stok: 28 | Günlük satış: 0.8</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-green-600">35 gün</p>
                        <p class="text-xs text-gray-500">yeterli</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sezonluk Analiz -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">🌡️ Sezonluk Tahmin</h3>
        
        <div class="grid gap-4 md:grid-cols-4">
            <div class="text-center p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                <span class="text-3xl">❄️</span>
                <p class="font-bold text-blue-600 mt-2">Kış Koleksiyonu</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Satış trendi: <span class="text-green-600 font-medium">↑ +35%</span></p>
                <p class="text-xs text-gray-500 mt-1">Montlar, kazaklar yüksek talep</p>
            </div>
            
            <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <span class="text-3xl">👕</span>
                <p class="font-bold text-gray-600 mt-2">Basic Ürünler</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Satış trendi: <span class="font-medium">● Sabit</span></p>
                <p class="text-xs text-gray-500 mt-1">T-shirt, pantolon stabil</p>
            </div>
            
            <div class="text-center p-4 rounded-lg bg-orange-50 dark:bg-orange-900/20">
                <span class="text-3xl">🎄</span>
                <p class="font-bold text-orange-600 mt-2">Yılbaşı Dönemi</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Beklenen: <span class="text-green-600 font-medium">↑ +50%</span></p>
                <p class="text-xs text-gray-500 mt-1">2 hafta sonra pik</p>
            </div>
            
            <div class="text-center p-4 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                <span class="text-3xl">📦</span>
                <p class="font-bold text-purple-600 mt-2">Stok Önerisi</p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Sipariş verin: <span class="text-red-600 font-medium">3 ürün</span></p>
                <p class="text-xs text-gray-500 mt-1">Tükenmeden önce hazırlık</p>
            </div>
        </div>
    </div>

    <!-- Detaylı Tahmin Tablosu -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📋 Ürün Bazlı Tahminler</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Mevcut Stok</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Günlük Ort.</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">7 Gün Tahmin</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Stok Süresi</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Önerilen Sipariş</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Aksiyon</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    <tr class="bg-red-50 dark:bg-red-900/10">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-red-600">⚠️</span>
                                <span class="font-medium">Mavi Kazak - Erkek</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center font-medium text-red-600">5</td>
                        <td class="px-4 py-3 text-center">2.1</td>
                        <td class="px-4 py-3 text-center font-medium">15</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-medium">2 gün</span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-red-600">+50 adet</td>
                        <td class="px-4 py-3 text-center">
                            <button class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                Acil Sipariş
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span class="font-medium">Siyah Pantolon - Erkek</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">28</td>
                        <td class="px-4 py-3 text-center">1.5</td>
                        <td class="px-4 py-3 text-center font-medium">11</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">18 gün</span>
                        </td>
                        <td class="px-4 py-3 text-center">+20 adet</td>
                        <td class="px-4 py-3 text-center">
                            <button class="bg-yellow-600 text-white px-3 py-1 rounded text-xs hover:bg-yellow-700">
                                Planla
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span class="font-medium">Beyaz Gömlek - Kadın</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">45</td>
                        <td class="px-4 py-3 text-center">1.0</td>
                        <td class="px-4 py-3 text-center font-medium">7</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">45 gün</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400">-</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-green-600 text-xs">Yeterli</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span class="font-medium">Spor Ayakkabı - Unisex</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">18</td>
                        <td class="px-4 py-3 text-center">0.8</td>
                        <td class="px-4 py-3 text-center font-medium">6</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium">22 gün</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-400">-</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-green-600 text-xs">Yeterli</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            window.runPrediction = function() {
                alert('🔮 Tahmin modeli çalıştırılıyor...\n\nBu özellik gerçek verilerle entegre edildiğinde çalışacaktır.');
            }
        </script>
    @endPushOnce
</x-admin::layouts>
