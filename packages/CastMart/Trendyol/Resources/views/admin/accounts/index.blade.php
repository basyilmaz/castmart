<x-admin::layouts>
    <x-slot:title>
        Trendyol Hesapları
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">👤</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Hesaplar</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trendyol API hesaplarını yönetin</p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.marketplace.trendyol.accounts.create') }}" 
               class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                ➕ Hesap Ekle
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 p-4 text-green-700 dark:bg-green-900 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    <!-- Accounts Table -->
    <div class="box-shadow rounded bg-white dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b dark:border-gray-800">
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Hesap Adı</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Supplier ID</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Ürün Sayısı</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Son Senkronizasyon</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                    <tr class="border-b dark:border-gray-800">
                        <td class="px-4 py-3 text-gray-800 dark:text-white">{{ $account->name }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $account->credentials['supplier_id'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($account->is_active)
                                <span class="rounded bg-green-100 px-2 py-1 text-xs text-green-700 dark:bg-green-900 dark:text-green-300">Aktif</span>
                            @else
                                <span class="rounded bg-red-100 px-2 py-1 text-xs text-red-700 dark:bg-red-900 dark:text-red-300">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $account->listings->count() }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $account->last_sync_at?->diffForHumans() ?? 'Henüz yok' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <button onclick="testConnection({{ $account->id }})" class="secondary-button text-xs">
                                    Test Et
                                </button>
                                <button onclick="syncOrders({{ $account->id }})" class="primary-button text-xs">
                                    Senkronize Et
                                </button>
                                <a href="{{ route('admin.marketplace.trendyol.accounts.edit', $account) }}" class="secondary-button text-xs">
                                    Düzenle
                                </a>
                                <button onclick="deleteAccount({{ $account->id }})" class="danger-button text-xs" style="background-color: #ef4444; color: white;">
                                    Sil
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            Henüz hesap eklenmemiş. 
                            <a href="{{ route('admin.marketplace.trendyol.accounts.create') }}" class="text-blue-600 hover:underline">
                                Hesap eklemek için tıklayın.
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            window.testConnection = async function(accountId) {
                try {
                    const response = await fetch(`/admin/marketplace/trendyol/accounts/${accountId}/test`, {
                        method: 'POST',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    
                    // Detaylı mesaj göster
                    let message = data.message || 'Bilinmeyen hata';
                    if (data.details) {
                        console.log('Bağlantı test detayları:', data.details);
                    }
                    alert(message);
                } catch (error) {
                    console.error('Bağlantı test hatası:', error);
                    alert('Bağlantı testi başarısız. Konsolu kontrol edin. Hata: ' + error.message);
                }
            }

            window.syncOrders = async function(accountId) {
                try {
                    const response = await fetch(`/admin/marketplace/trendyol/accounts/${accountId}/sync-orders`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const data = await response.json();
                    alert(data.message);
                    location.reload();
                } catch (error) {
                    alert('Senkronizasyon başarısız.');
                }
            }

            window.deleteAccount = async function(accountId) {
                if (!confirm('Bu hesabı silmek istediğinizden emin misiniz?')) {
                    return;
                }
                try {
                    const response = await fetch(`/admin/marketplace/trendyol/accounts/${accountId}`, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    alert(data.message);
                    location.reload();
                } catch (error) {
                    alert('Silme işlemi başarısız.');
                }
            }
        </script>
    @endPushOnce
</x-admin::layouts>
