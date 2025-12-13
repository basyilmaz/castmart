<x-admin::layouts>
    <x-slot:title>
        Trendyol Siparişleri
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">🛍️</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Siparişler</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pazaryeri siparişlerini yönetin</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="location.reload()" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                🔄 Yenile
            </button>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="grid gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $newOrders = $orders->where('status', 'new')->count();
            $processingOrders = $orders->where('status', 'processing')->count();
            $shippedOrders = $orders->where('status', 'shipped')->count();
            $deliveredOrders = $orders->where('status', 'delivered')->count();
        @endphp
        
        <div class="bg-gradient-to-br from-yellow-500 to-amber-500 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Yeni</p>
                    <p class="text-3xl font-bold">{{ $newOrders }}</p>
                </div>
                <span class="text-3xl opacity-80">📋</span>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">İşleniyor</p>
                    <p class="text-3xl font-bold">{{ $processingOrders }}</p>
                </div>
                <span class="text-3xl opacity-80">⚙️</span>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-violet-500 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Kargoda</p>
                    <p class="text-3xl font-bold">{{ $shippedOrders }}</p>
                </div>
                <span class="text-3xl opacity-80">🚚</span>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl p-4 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Teslim Edildi</p>
                    <p class="text-3xl font-bold">{{ $deliveredOrders }}</p>
                </div>
                <span class="text-3xl opacity-80">✅</span>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 rounded-xl bg-green-100 p-4 text-green-700 dark:bg-green-900/30 dark:text-green-400 flex items-center gap-2">
            <span class="text-xl">✅</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl bg-red-100 p-4 text-red-700 dark:bg-red-900/30 dark:text-red-400 flex items-center gap-2">
            <span class="text-xl">❌</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- Orders Table -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Sipariş No</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Hesap</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Kargo</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Takip No</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">Tarih</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-600 dark:text-gray-300">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-800">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-800 dark:text-white">
                                {{ $order->external_order_number ?? $order->external_order_id }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $order->account->name }}</td>
                        <td class="px-4 py-3">
                            @php
                                $statusStyles = [
                                    'new' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'processing' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'shipped' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'delivered' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $statusLabels = [
                                    'new' => '📋 Yeni',
                                    'processing' => '⚙️ İşleniyor',
                                    'shipped' => '🚚 Kargoda',
                                    'delivered' => '✅ Teslim Edildi',
                                    'cancelled' => '❌ İptal',
                                ];
                            @endphp
                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium {{ $statusStyles[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $order->cargo_provider ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($order->tracking_number)
                                <span class="font-mono text-sm text-gray-600 dark:text-gray-300">{{ $order->tracking_number }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ $order->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.marketplace.trendyol.orders.detail', $order) }}" 
                               class="inline-flex items-center gap-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition">
                                👁️ Detay
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-5xl mb-3">📭</span>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Henüz sipariş yok.</p>
                                <p class="text-sm text-gray-400 mt-1">Siparişler burada görüntülenecek.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="border-t dark:border-gray-800 p-4">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</x-admin::layouts>
