<x-admin::layouts>
    <x-slot:title>
        Bildirimler
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-orange-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">🔔</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Bildirimler</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tüm uyarıları ve bildirimleri yönetin</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="markAllRead()" class="flex items-center gap-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                ✓ Tümünü Okundu İşaretle
            </button>
            <button onclick="openSettings()" class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                ⚙️ Ayarlar
            </button>
        </div>
    </div>

    <!-- Bildirim İstatistikleri -->
    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Okunmamış</p>
                    <p class="text-2xl font-bold text-red-600">12</p>
                </div>
                <span class="text-3xl">📬</span>
            </div>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Bugün</p>
                    <p class="text-2xl font-bold text-blue-600">8</p>
                </div>
                <span class="text-3xl">📅</span>
            </div>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Bu Hafta</p>
                    <p class="text-2xl font-bold text-gray-600">34</p>
                </div>
                <span class="text-3xl">📊</span>
            </div>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Kritik</p>
                    <p class="text-2xl font-bold text-red-600">3</p>
                </div>
                <span class="text-3xl">🚨</span>
            </div>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button onclick="filterNotifications('all')" class="filter-btn active px-4 py-2 rounded text-sm bg-indigo-600 text-white">
                Tümü
            </button>
            <button onclick="filterNotifications('unread')" class="filter-btn px-4 py-2 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                Okunmamış
            </button>
            <button onclick="filterNotifications('buybox')" class="filter-btn px-4 py-2 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                🎯 BuyBox
            </button>
            <button onclick="filterNotifications('stock')" class="filter-btn px-4 py-2 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                📦 Stok
            </button>
            <button onclick="filterNotifications('price')" class="filter-btn px-4 py-2 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                💰 Fiyat
            </button>
            <button onclick="filterNotifications('review')" class="filter-btn px-4 py-2 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                ⭐ Yorum
            </button>
            <button onclick="filterNotifications('order')" class="filter-btn px-4 py-2 rounded text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                🛒 Sipariş
            </button>
        </div>
    </div>

    <!-- Bildirim Listesi -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 overflow-hidden">
        <div id="notifications-list" class="divide-y dark:divide-gray-700">
            <!-- Kritik Bildirim -->
            <div class="notification-item unread p-4 hover:bg-gray-50 dark:hover:bg-gray-800 bg-red-50 dark:bg-red-900/10" data-type="buybox">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-xl">
                        🔴
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-red-600 text-white rounded text-xs font-medium">KRİTİK</span>
                            <span class="text-xs text-gray-500">5 dakika önce</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">BuyBox Kaybedildi: Mavi Kazak - Erkek</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Rakip satıcı fiyatı 149.90₺'ye düşürdü. Mevcut fiyatınız: 159.90₺</p>
                        <div class="flex gap-2 mt-3">
                            <button class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                Fiyatı Eşitle
                            </button>
                            <button class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-xs hover:bg-gray-300">
                                Detay
                            </button>
                        </div>
                    </div>
                    <button onclick="dismissNotification(this)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Stok Uyarısı -->
            <div class="notification-item unread p-4 hover:bg-gray-50 dark:hover:bg-gray-800 bg-yellow-50 dark:bg-yellow-900/10" data-type="stock">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center text-xl">
                        ⚠️
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-yellow-600 text-white rounded text-xs font-medium">STOK</span>
                            <span class="text-xs text-gray-500">1 saat önce</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Stok Kritik Seviyede: Siyah Pantolon</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kalan stok: 3 adet. Tahmini tükenme: 2 gün içinde.</p>
                        <div class="flex gap-2 mt-3">
                            <button class="bg-yellow-600 text-white px-3 py-1 rounded text-xs hover:bg-yellow-700">
                                Stok Güncelle
                            </button>
                        </div>
                    </div>
                    <button onclick="dismissNotification(this)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Fiyat Değişimi -->
            <div class="notification-item p-4 hover:bg-gray-50 dark:hover:bg-gray-800" data-type="price">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-xl">
                        💰
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-blue-600 text-white rounded text-xs font-medium">FİYAT</span>
                            <span class="text-xs text-gray-500">3 saat önce</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Otomatik Fiyat Güncellendi: Spor Ayakkabı</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">349.90₺ → 339.90₺ (BuyBox kuralı uygulandı)</p>
                    </div>
                    <button onclick="dismissNotification(this)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Yorum Bildirimi -->
            <div class="notification-item unread p-4 hover:bg-gray-50 dark:hover:bg-gray-800 bg-orange-50 dark:bg-orange-900/10" data-type="review">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center text-xl">
                        ⭐
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-orange-600 text-white rounded text-xs font-medium">YORUM</span>
                            <span class="text-xs text-gray-500">5 saat önce</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Yeni 1 Yıldızlı Yorum: Beyaz Gömlek</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">"Ürün beklediğim gibi değildi, kumaş kalitesi düşük."</p>
                        <div class="flex gap-2 mt-3">
                            <button class="bg-orange-600 text-white px-3 py-1 rounded text-xs hover:bg-orange-700">
                                Yanıtla
                            </button>
                        </div>
                    </div>
                    <button onclick="dismissNotification(this)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Sipariş Bildirimi -->
            <div class="notification-item p-4 hover:bg-gray-50 dark:hover:bg-gray-800" data-type="order">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-xl">
                        🛒
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-green-600 text-white rounded text-xs font-medium">SİPARİŞ</span>
                            <span class="text-xs text-gray-500">Dün</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Yeni Sipariş: #TY-2024-1234</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">2 ürün - Toplam: 299.80₺</p>
                    </div>
                    <button onclick="dismissNotification(this)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Fırsat Bildirimi -->
            <div class="notification-item p-4 hover:bg-gray-50 dark:hover:bg-gray-800" data-type="buybox">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-xl">
                        🎉
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 bg-green-600 text-white rounded text-xs font-medium">FIRSAT</span>
                            <span class="text-xs text-gray-500">Dün</span>
                        </div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Rakip Stok Tükendi: Kış Montu</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Fiyat artırma fırsatı! Tek satıcı sizsiniz.</p>
                        <div class="flex gap-2 mt-3">
                            <button class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">
                                Fiyatı Artır
                            </button>
                        </div>
                    </div>
                    <button onclick="dismissNotification(this)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bildirim Ayarları Modal -->
    <div id="settings-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">⚙️ Bildirim Ayarları</h3>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">BuyBox Uyarıları</p>
                        <p class="text-xs text-gray-500">BuyBox kaybedildiğinde bildir</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Stok Uyarıları</p>
                        <p class="text-xs text-gray-500">Stok kritik seviyeye düştüğünde</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">Yorum Bildirimleri</p>
                        <p class="text-xs text-gray-500">Yeni yorum geldiğinde</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" checked class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                        <p class="font-medium text-gray-800 dark:text-gray-200">E-posta Bildirimleri</p>
                        <p class="text-xs text-gray-500">Kritik uyarıları e-posta ile al</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button onclick="closeSettings()" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    Kapat
                </button>
                <button onclick="saveSettings()" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Kaydet
                </button>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            // Bildirimleri filtrele
            window.filterNotifications = function(type) {
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('bg-indigo-600', 'text-white');
                    btn.classList.add('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');
                });
                event.target.classList.remove('bg-gray-100', 'dark:bg-gray-800', 'text-gray-700', 'dark:text-gray-300');
                event.target.classList.add('bg-indigo-600', 'text-white');

                const items = document.querySelectorAll('.notification-item');
                items.forEach(item => {
                    if (type === 'all') {
                        item.style.display = 'block';
                    } else if (type === 'unread') {
                        item.style.display = item.classList.contains('unread') ? 'block' : 'none';
                    } else {
                        item.style.display = item.dataset.type === type ? 'block' : 'none';
                    }
                });
            }

            // Bildirimi kapat
            window.dismissNotification = function(btn) {
                btn.closest('.notification-item').remove();
            }

            // Tümünü okundu işaretle
            window.markAllRead = function() {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread', 'bg-red-50', 'bg-yellow-50', 'bg-orange-50');
                    item.classList.remove('dark:bg-red-900/10', 'dark:bg-yellow-900/10', 'dark:bg-orange-900/10');
                });
                alert('Tüm bildirimler okundu olarak işaretlendi.');
            }

            // Ayarları aç
            window.openSettings = function() {
                document.getElementById('settings-modal').classList.remove('hidden');
                document.getElementById('settings-modal').classList.add('flex');
            }

            // Ayarları kapat
            window.closeSettings = function() {
                document.getElementById('settings-modal').classList.add('hidden');
                document.getElementById('settings-modal').classList.remove('flex');
            }

            // Ayarları kaydet
            window.saveSettings = function() {
                alert('Bildirim ayarları kaydedildi.');
                closeSettings();
            }
        </script>
    @endPushOnce
</x-admin::layouts>
