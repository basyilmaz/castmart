<x-admin::layouts>
    <x-slot:title>
        İade Yönetimi
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                <span class="text-2xl">↩️</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">İade Yönetimi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trendyol iade ve iptal taleplerini yönetin</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="refreshClaims()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;">
                🔄 Yenile
            </button>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="grid gap-4 md:grid-cols-4 mb-4">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Bekleyen İadeler</p>
                <span class="text-2xl font-bold text-yellow-600">{{ count(array_filter($claims['content'] ?? [], fn($c) => ($c['claimStatus'] ?? '') === 'WAITING')) }}</span>
            </div>
        </div>
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Onaylanan</p>
                <span class="text-2xl font-bold text-green-600">{{ count(array_filter($claims['content'] ?? [], fn($c) => ($c['claimStatus'] ?? '') === 'APPROVED')) }}</span>
            </div>
        </div>
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Reddedilen</p>
                <span class="text-2xl font-bold text-red-600">{{ count(array_filter($claims['content'] ?? [], fn($c) => ($c['claimStatus'] ?? '') === 'REJECTED')) }}</span>
            </div>
        </div>
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">Toplam</p>
                <span class="text-2xl font-bold text-gray-800 dark:text-white">{{ $claims['totalElements'] ?? count($claims['content'] ?? []) }}</span>
            </div>
        </div>
    </div>

    <!-- İade Listesi -->
    <div class="box-shadow rounded bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">İade ID</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Sipariş No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Sebep</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Tutar</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Tarih</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($claims['content'] ?? [] as $claim)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-mono text-sm">{{ $claim['id'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $claim['orderNumber'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="max-w-xs truncate">
                                {{ $claim['productName'] ?? $claim['barcode'] ?? '-' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                Barkod: {{ $claim['barcode'] ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $reason = $claim['claimReason'] ?? $claim['reason'] ?? 'UNKNOWN';
                                $reasonLabels = [
                                    'DAMAGED' => 'Hasarlı',
                                    'WRONG_PRODUCT' => 'Yanlış Ürün',
                                    'MISSING_PRODUCT' => 'Eksik Ürün',
                                    'DEFECTIVE' => 'Kusurlu',
                                    'NOT_LIKE_DESCRIPTION' => 'Açıklamaya Uygun Değil',
                                    'REGRET' => 'Cayma',
                                    'OTHER' => 'Diğer',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $reasonLabels[$reason] ?? $reason }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $status = $claim['claimStatus'] ?? 'UNKNOWN';
                                $statusColors = [
                                    'WAITING' => 'yellow',
                                    'APPROVED' => 'green',
                                    'REJECTED' => 'red',
                                    'IN_PROGRESS' => 'blue',
                                ];
                                $statusLabels = [
                                    'WAITING' => 'Bekliyor',
                                    'APPROVED' => 'Onaylandı',
                                    'REJECTED' => 'Reddedildi',
                                    'IN_PROGRESS' => 'İşlemde',
                                ];
                                $color = $statusColors[$status] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-2 py-0.5 text-xs font-medium text-{{ $color }}-700 dark:bg-{{ $color }}-900 dark:text-{{ $color }}-300">
                                {{ $statusLabels[$status] ?? $status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">
                            {{ number_format($claim['claimPrice'] ?? $claim['amount'] ?? 0, 2, ',', '.') }} ₺
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">
                            @if(isset($claim['createdDate']))
                                {{ \Carbon\Carbon::createFromTimestampMs($claim['createdDate'])->format('d.m.Y H:i') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(($claim['claimStatus'] ?? '') === 'WAITING')
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="approveClaim({{ $claim['id'] }})" 
                                        class="p-1.5 rounded hover:bg-green-100 dark:hover:bg-green-900 text-green-600" 
                                        title="Onayla">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button onclick="viewClaimDetails({{ $claim['id'] }})" 
                                        class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600" 
                                        title="Detay">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>İade talebi bulunamadı.</p>
                            <p class="text-sm mt-1">Harika! Şu an bekleyen iade yok.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- İade Bilgileri -->
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">İade Sebepleri</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Hasarlı Ürün</span>
                    <span class="font-medium text-red-600">Kargo hasarı raporlayın</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Yanlış Ürün</span>
                    <span class="font-medium text-yellow-600">Sipariş kontrolü önemli</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Cayma Hakkı</span>
                    <span class="font-medium text-blue-600">14 gün içinde</span>
                </div>
            </div>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-3">İade Süreci</h3>
            <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 text-blue-600 text-xs flex items-center justify-center">1</span>
                    <span>Müşteri iade talebi oluşturur</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 text-blue-600 text-xs flex items-center justify-center">2</span>
                    <span>Satıcı talebi değerlendirir</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-100 text-blue-600 text-xs flex items-center justify-center">3</span>
                    <span>Ürün iade edilir</span>
                </li>
                <li class="flex items-start gap-2">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-100 text-green-600 text-xs flex items-center justify-center">4</span>
                    <span>Ücret iadesi yapılır</span>
                </li>
            </ol>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            window.refreshClaims = function() {
                location.reload();
            }

            window.approveClaim = async function(claimId) {
                if (!confirm('Bu iade talebini onaylamak istediğinize emin misiniz?')) {
                    return;
                }

                try {
                    const response = await fetch(`/admin/marketplace/trendyol/claims/${claimId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    alert('İade onaylanamadı.');
                }
            }

            window.viewClaimDetails = function(claimId) {
                alert('İade detayları: ID ' + claimId);
            }
        </script>
    @endPushOnce
</x-admin::layouts>
