<x-admin::layouts>
    <x-slot:title>
        Komisyon Oranları
    </x-slot>

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                📋 Komisyon Oranları Yönetimi
            </p>
            <p class="!leading-normal text-gray-600 dark:text-gray-300">
                Trendyol kategori bazlı komisyon oranlarını görüntüleyin ve düzenleyin
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="showAddModal()" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Yeni Oran Ekle
            </button>
            <button onclick="refreshRates()" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition">
                🔄 Yenile
            </button>
        </div>
    </div>

    <!-- Özet -->
    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Toplam Kategori</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $rates->count() }}</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">En Düşük Komisyon</p>
            <p class="text-2xl font-bold text-green-600">%{{ $rates->min('commission_rate') }}</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">En Yüksek Komisyon</p>
            <p class="text-2xl font-bold text-red-600">%{{ $rates->max('commission_rate') }}</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Ortalama Komisyon</p>
            <p class="text-2xl font-bold text-blue-600">%{{ number_format($rates->avg('commission_rate'), 1) }}</p>
        </div>
    </div>

    <!-- Arama -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4 mb-6">
        <div class="flex gap-4">
            <input type="text" id="search-input" placeholder="Kategori ara..." 
                   class="flex-1 rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                   onkeyup="filterTable()">
            <select id="filter-range" class="rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="filterTable()">
                <option value="all">Tüm Oranlar</option>
                <option value="low">%0-10 (Düşük)</option>
                <option value="medium">%10-15 (Orta)</option>
                <option value="high">%15+ (Yüksek)</option>
            </select>
        </div>
    </div>

    <!-- Tablo -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="rates-table">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Kategori</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Komisyon (%)</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Hizmet Bedeli (₺)</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach($rates as $rate)
                    <tr class="rate-row hover:bg-gray-50 dark:hover:bg-gray-800" data-rate="{{ $rate->commission_rate }}">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-800 dark:text-gray-200">{{ $rate->category_name }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-3 py-1 rounded-full text-sm font-medium
                                @if($rate->commission_rate <= 10) bg-green-100 text-green-800
                                @elseif($rate->commission_rate <= 15) bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif">
                                %{{ $rate->commission_rate }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">
                            {{ number_format($rate->service_fee, 2) }} ₺
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($rate->is_active)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Aktif</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">Pasif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="editRate({{ $rate->id }}, '{{ $rate->category_name }}', {{ $rate->commission_rate }}, {{ $rate->service_fee }})" 
                                    class="text-blue-600 hover:underline text-sm mr-2">Düzenle</button>
                            <button onclick="deleteRate({{ $rate->id }})" 
                                    class="text-red-600 hover:underline text-sm">Sil</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bilgi Kartı -->
    <div class="box-shadow rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 mt-6">
        <h4 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">💡 Komisyon Hesaplama Bilgisi</h4>
        <p class="text-sm text-blue-700 dark:text-blue-400">
            Trendyol komisyonu, satış fiyatının KDV hariç tutarı üzerinden hesaplanır. 
            Komisyon tutarı = (Satış Fiyatı / 1.20) × Komisyon Oranı
        </p>
    </div>

    <!-- Modal: Oran Ekle/Düzenle -->
    <div id="rate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4" id="modal-title">Komisyon Oranı</h3>
            
            <form id="rate-form">
                <input type="hidden" id="rate-id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori Adı</label>
                        <input type="text" id="rate-category" required
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Komisyon Oranı (%)</label>
                        <input type="number" id="rate-commission" step="0.01" min="0" max="100" required
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hizmet Bedeli (₺)</label>
                        <input type="number" id="rate-service" step="0.01" min="0" value="4.99"
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                        İptal
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Kaydet
                    </button>
                </div>
            </form>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            // Modal göster - Ekle
            window.showAddModal = function() {
                document.getElementById('modal-title').textContent = 'Yeni Komisyon Oranı Ekle';
                document.getElementById('rate-id').value = '';
                document.getElementById('rate-category').value = '';
                document.getElementById('rate-commission').value = '';
                document.getElementById('rate-service').value = '4.99';
                openModal();
            }

            // Modal göster - Düzenle
            window.editRate = function(id, category, commission, service) {
                document.getElementById('modal-title').textContent = 'Komisyon Oranı Düzenle';
                document.getElementById('rate-id').value = id;
                document.getElementById('rate-category').value = category;
                document.getElementById('rate-commission').value = commission;
                document.getElementById('rate-service').value = service;
                openModal();
            }

            function openModal() {
                document.getElementById('rate-modal').classList.remove('hidden');
                document.getElementById('rate-modal').classList.add('flex');
            }

            window.closeModal = function() {
                document.getElementById('rate-modal').classList.add('hidden');
                document.getElementById('rate-modal').classList.remove('flex');
            }

            // Form gönder
            document.getElementById('rate-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const data = {
                    id: document.getElementById('rate-id').value,
                    category_name: document.getElementById('rate-category').value,
                    commission_rate: document.getElementById('rate-commission').value,
                    service_fee: document.getElementById('rate-service').value,
                };

                fetch('{{ route("admin.marketplace.trendyol.commission-rates.save") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert(result.message || 'Bir hata oluştu.');
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    alert('Bir hata oluştu.');
                });
            });

            // Sil
            window.deleteRate = function(id) {
                if (!confirm('Bu komisyon oranını silmek istediğinizden emin misiniz?')) {
                    return;
                }

                fetch('{{ route("admin.marketplace.trendyol.commission-rates.delete") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert(result.message || 'Bir hata oluştu.');
                    }
                });
            }

            // Filtrele
            window.filterTable = function() {
                const search = document.getElementById('search-input').value.toLowerCase();
                const range = document.getElementById('filter-range').value;
                
                document.querySelectorAll('.rate-row').forEach(row => {
                    const category = row.querySelector('td:first-child').textContent.toLowerCase();
                    const rate = parseFloat(row.dataset.rate);
                    
                    let showSearch = category.includes(search);
                    let showRange = true;
                    
                    if (range === 'low') showRange = rate <= 10;
                    else if (range === 'medium') showRange = rate > 10 && rate <= 15;
                    else if (range === 'high') showRange = rate > 15;
                    
                    row.style.display = (showSearch && showRange) ? 'table-row' : 'none';
                });
            }

            // Yenile
            window.refreshRates = function() {
                location.reload();
            }
        </script>
    @endPushOnce
</x-admin::layouts>
