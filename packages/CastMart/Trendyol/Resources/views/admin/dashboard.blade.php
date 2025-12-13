<x-admin::layouts>
    <x-slot:title>
        Trendyol Dashboard
    </x-slot>

    <style>
        .stat-card {
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .quick-link {
            transition: all 0.2s ease;
        }
        .quick-link:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center shadow-lg">
                <span class="text-3xl">🛒</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Trendyol Dashboard</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pazaryeri yönetim merkezi</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.marketplace.trendyol.accounts.create') }}" 
               class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                <span>➕</span> Hesap Ekle
            </a>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="grid gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Aktif Hesaplar -->
        <div class="stat-card rounded-2xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #3b82f6 0%, #4f46e5 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Aktif Hesaplar</p>
                    <p class="text-4xl font-bold mt-1">{{ $stats['accounts'] }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <span class="text-3xl">👤</span>
                </div>
            </div>
            <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
                <a href="{{ route('admin.marketplace.trendyol.accounts') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1">
                    Hesapları Görüntüle →
                </a>
            </div>
        </div>

        <!-- Aktif Ürünler -->
        <div class="stat-card rounded-2xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #22c55e 0%, #10b981 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Aktif Ürünler</p>
                    <p class="text-4xl font-bold mt-1">{{ $stats['products'] }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <span class="text-3xl">📦</span>
                </div>
            </div>
            <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
                <a href="{{ route('admin.marketplace.trendyol.products') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1">
                    Ürünleri Görüntüle →
                </a>
            </div>
        </div>

        <!-- Yeni Siparişler -->
        <div class="stat-card rounded-2xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #eab308 0%, #f97316 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Yeni Siparişler</p>
                    <p class="text-4xl font-bold mt-1">{{ $stats['pending_orders'] }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <span class="text-3xl">🛍️</span>
                </div>
            </div>
            <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
                <a href="{{ route('admin.marketplace.trendyol.orders') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1">
                    Siparişleri Görüntüle →
                </a>
            </div>
        </div>

        <!-- Bekleyen Sorular -->
        <div class="stat-card rounded-2xl p-5 text-white shadow-lg" style="background: linear-gradient(135deg, #ef4444 0%, #ec4899 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Bekleyen Sorular</p>
                    <p class="text-4xl font-bold mt-1">{{ $stats['pending_questions'] }}</p>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <span class="text-3xl">❓</span>
                </div>
            </div>
            <div class="mt-3 pt-3" style="border-top: 1px solid rgba(255,255,255,0.2);">
                <a href="{{ route('admin.marketplace.trendyol.questions') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1">
                    Soruları Görüntüle →
                </a>
            </div>
        </div>
    </div>

    <!-- Hızlı Erişim Grid -->
    <div class="grid gap-4 lg:grid-cols-2 mb-6">
        <!-- Hızlı İşlemler -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                ⚡ Hızlı İşlemler
            </h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.marketplace.trendyol.intelligence') }}" class="quick-link flex items-center gap-3 p-4 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 rounded-xl">
                    <span class="text-2xl">🧠</span>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">7. His Dashboard</p>
                        <p class="text-xs text-gray-500">AI destekli analiz</p>
                    </div>
                </a>
                <a href="{{ route('admin.marketplace.trendyol.commission-calculator') }}" class="quick-link flex items-center gap-3 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl">
                    <span class="text-2xl">🧮</span>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">Komisyon Hesaplayıcı</p>
                        <p class="text-xs text-gray-500">Kâr-zarar analizi</p>
                    </div>
                </a>
                <a href="{{ route('admin.marketplace.trendyol.buybox-tracker') }}" class="quick-link flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-xl">
                    <span class="text-2xl">🎯</span>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">BuyBox Takip</p>
                        <p class="text-xs text-gray-500">Rekabet analizi</p>
                    </div>
                </a>
                <a href="{{ route('admin.marketplace.trendyol.notifications') }}" class="quick-link flex items-center gap-3 p-4 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 rounded-xl">
                    <span class="text-2xl">🔔</span>
                    <div>
                        <p class="font-medium text-gray-800 dark:text-white">Bildirimler</p>
                        <p class="text-xs text-gray-500">Uyarılar ve fırsatlar</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Son Aktiviteler -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                📊 Son Aktiviteler
            </h2>
            <div class="space-y-3">
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <span class="text-xl">🛍️</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Yeni sipariş alındı</p>
                        <p class="text-xs text-gray-500">2 dakika önce</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <span class="text-xl">📦</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Stok güncellendi</p>
                        <p class="text-xs text-gray-500">15 dakika önce</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <span class="text-xl">💰</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Fiyat değişikliği uygulandı</p>
                        <p class="text-xs text-gray-500">1 saat önce</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <span class="text-xl">✅</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">Sipariş teslim edildi</p>
                        <p class="text-xs text-gray-500">3 saat önce</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yönetim Araçları -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
            🛠️ Yönetim Araçları
        </h2>
        <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-6">
            <a href="{{ route('admin.marketplace.trendyol.accounts') }}" class="quick-link text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="text-2xl block mb-2">👤</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Hesaplar</span>
            </a>
            <a href="{{ route('admin.marketplace.trendyol.products') }}" class="quick-link text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="text-2xl block mb-2">📦</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Ürünler</span>
            </a>
            <a href="{{ route('admin.marketplace.trendyol.orders') }}" class="quick-link text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="text-2xl block mb-2">🛍️</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Siparişler</span>
            </a>
            <a href="{{ route('admin.marketplace.trendyol.questions') }}" class="quick-link text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="text-2xl block mb-2">❓</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">Sorular</span>
            </a>
            <a href="{{ route('admin.marketplace.trendyol.claims') }}" class="quick-link text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="text-2xl block mb-2">↩️</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">İadeler</span>
            </a>
            <a href="{{ route('admin.marketplace.trendyol.invoices') }}" class="quick-link text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700">
                <span class="text-2xl block mb-2">🧾</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">E-Fatura</span>
            </a>
        </div>
    </div>
</x-admin::layouts>
