<x-admin::layouts>
    <x-slot:title>
        Kargo Yönetimi
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <span class="text-2xl">📦</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Kargo Yönetimi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Takip numaraları ekleyin ve kargo firmalarını yönetin</p>
            </div>
        </div>
    </div>

    <!-- Kargo Firmaları -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Trendyol Kargo Firmaları</h3>
        
        <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-4">
            @php
            $cargoProviders = [
                ['id' => 10, 'name' => 'MNG Kargo', 'code' => 'MNGKARGO'],
                ['id' => 14, 'name' => 'Yurtiçi Kargo', 'code' => 'YURTICI'],
                ['id' => 4, 'name' => 'Aras Kargo', 'code' => 'ARASKARGO'],
                ['id' => 6, 'name' => 'Sürat Kargo', 'code' => 'SURATKARGO'],
                ['id' => 19, 'name' => 'PTT Kargo', 'code' => 'PTTGLOBAL'],
                ['id' => 7, 'name' => 'Horoz Lojistik', 'code' => 'HOROZLOJISTIK'],
                ['id' => 9, 'name' => 'Ceva Lojistik', 'code' => 'CEVA'],
                ['id' => 20, 'name' => 'Trendyol Express', 'code' => 'TEX'],
                ['id' => 30, 'name' => 'Sendeo', 'code' => 'SENDEO'],
                ['id' => 17, 'name' => 'Octo', 'code' => 'OCTO'],
            ];
            @endphp
            @foreach($cargoProviders as $provider)
            <div class="flex items-center justify-between rounded border p-3 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-800 dark:text-white">{{ $provider['name'] }}</p>
                    <p class="text-xs text-gray-500">ID: {{ $provider['id'] }} | Kod: {{ $provider['code'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Bekleyen Kargolar -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">Kargo Bekleyen Siparişler</h3>
            <button onclick="refreshOrders()" class="text-sm text-blue-600 hover:underline">Yenile</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Sipariş No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Kargo Firması</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Takip No</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700" id="pending-orders">
                    @foreach($orders ?? [] as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-mono">{{ $order['orderNumber'] ?? $order->external_order_number ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if(isset($order['lines']) || isset($order->items_data))
                                @php $lines = $order['lines'] ?? $order->items_data ?? []; @endphp
                                @foreach(array_slice($lines, 0, 2) as $line)
                                    <div class="text-sm">{{ $line['productName'] ?? $line['productTitle'] ?? '-' }}</div>
                                @endforeach
                                @if(count($lines) > 2)
                                    <div class="text-xs text-gray-500">+{{ count($lines) - 2 }} ürün daha</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                                {{ $order['status'] ?? $order->status ?? 'Bekliyor' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <select class="cargo-select rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" 
                                    data-order-id="{{ $order['id'] ?? $order->id ?? '' }}">
                                <option value="">Seçiniz</option>
                                @foreach($cargoProviders as $provider)
                                    <option value="{{ $provider['id'] }}" 
                                            {{ ($order['cargoProviderName'] ?? $order->cargo_provider ?? '') == $provider['name'] ? 'selected' : '' }}>
                                        {{ $provider['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" class="tracking-input rounded border px-2 py-1 text-sm w-32 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" 
                                   placeholder="Takip No" 
                                   value="{{ $order['cargoTrackingNumber'] ?? $order->tracking_number ?? '' }}"
                                   data-order-id="{{ $order['id'] ?? $order->id ?? '' }}">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="updateTracking('{{ $order['id'] ?? $order->id ?? '' }}')" 
                                    class="px-3 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700">
                                Güncelle
                            </button>
                        </td>
                    </tr>
                    @endforeach

                    @if(empty($orders))
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            <p>Kargo bekleyen sipariş bulunmuyor.</p>
                            <a href="{{ route('admin.marketplace.trendyol.orders') }}" class="text-blue-600 hover:underline text-sm">
                                Tüm siparişleri görüntüle →
                            </a>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Toplu Kargo İşlemleri -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Toplu Kargo İşlemleri</h3>
        
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded border p-4 dark:border-gray-700">
                <h4 class="font-medium text-gray-800 dark:text-white mb-3">CSV ile Toplu Takip No Güncelleme</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Sipariş numarası ve takip numarasını içeren CSV dosyası yükleyin.
                </p>
                <div class="flex gap-2">
                    <input type="file" accept=".csv" id="csv-upload" 
                           class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <button onclick="uploadCSV()" class="secondary-button">Yükle</button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Format: siparis_no,takip_no</p>
            </div>

            <div class="rounded border p-4 dark:border-gray-700">
                <h4 class="font-medium text-gray-800 dark:text-white mb-3">Varsayılan Kargo Firması</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Yeni ürünler için varsayılan kargo firmasını seçin.
                </p>
                <select class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    @foreach($cargoProviders as $provider)
                        <option value="{{ $provider['id'] }}">{{ $provider['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Kargo Bilgileri -->
    <div class="grid gap-4 md:grid-cols-3">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-medium text-gray-800 dark:text-white">Sevk Süresi</h4>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Siparişler 48 saat içinde kargoya verilmelidir. Gecikmeler cezaya neden olabilir.
            </p>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-medium text-gray-800 dark:text-white">Takip No</h4>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Takip numarası girilmeden sipariş "Kargoda" durumuna geçemez.
            </p>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 dark:bg-yellow-900">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h4 class="font-medium text-gray-800 dark:text-white">TEX Kargo</h4>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Trendyol Express (TEX) kullanıyorsanız takip numarasını getShipmentPackages'tan alın.
            </p>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            window.refreshOrders = function() {
                location.reload();
            }

            window.updateTracking = async function(orderId) {
                const row = document.querySelector(`[data-order-id="${orderId}"]`).closest('tr');
                const cargoSelect = row.querySelector('.cargo-select');
                const trackingInput = row.querySelector('.tracking-input');
                
                const cargoId = cargoSelect.value;
                const trackingNumber = trackingInput.value.trim();

                if (!cargoId || !trackingNumber) {
                    alert('Lütfen kargo firması ve takip numarası girin.');
                    return;
                }

                try {
                    const response = await fetch(`/admin/marketplace/trendyol/orders/${orderId}/tracking`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            tracking_number: trackingNumber,
                            cargo_provider_id: cargoId
                        })
                    });
                    const data = await response.json();
                    alert(data.message || 'Takip numarası güncellendi.');
                    location.reload();
                } catch (error) {
                    alert('Güncelleme başarısız: ' + error.message);
                }
            }

            window.uploadCSV = function() {
                alert('CSV yükleme özelliği henüz geliştirme aşamasında.');
            }
        </script>
    @endPushOnce
</x-admin::layouts>
