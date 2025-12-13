<x-admin::layouts>
    <x-slot:title>
        Trendyol Ürünleri
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">📦</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Ürünler</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Toplam {{ $products['totalElements'] ?? 0 }} ürün | Sayfa {{ ($products['page'] ?? 0) + 1 }} / {{ $products['totalPages'] ?? 1 }}
                </p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.marketplace.trendyol.products.create') }}" class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                ➕ Yeni Ürün Gönder
            </a>
            <button onclick="importProducts()" class="flex items-center gap-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                📥 Trendyol'dan İçe Aktar
            </button>
            <button onclick="syncStock()" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                🔄 Stok/Fiyat Güncelle
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <form method="GET" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Ürün ara (barkod, ad, SKU)..." 
                       class="w-full rounded-md border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <select name="approved" class="rounded-md border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Tüm Durumlar</option>
                    <option value="1" {{ request('approved') === '1' ? 'selected' : '' }}>Onaylı</option>
                    <option value="0" {{ request('approved') === '0' ? 'selected' : '' }}>Onay Bekliyor</option>
                </select>
            </div>
            <div>
                <select name="onSale" class="rounded-md border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="">Satış Durumu</option>
                    <option value="1" {{ request('onSale') === '1' ? 'selected' : '' }}>Satışta</option>
                    <option value="0" {{ request('onSale') === '0' ? 'selected' : '' }}>Satışta Değil</option>
                </select>
            </div>
            <button type="submit" class="secondary-button">Filtrele</button>
            <a href="{{ route('admin.marketplace.trendyol.products') }}" class="text-sm text-gray-500 hover:text-gray-700">Temizle</a>
        </form>
    </div>

    <!-- Products Table -->
    <div class="box-shadow rounded bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Görsel</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün Bilgileri</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Stok</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Liste Fiyatı</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Satış Fiyatı</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Eşleşme</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @forelse($products['content'] ?? [] as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <!-- Görsel -->
                        <td class="px-4 py-3">
                            <div class="w-16 h-16 rounded overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0">
                                @if(!empty($product['images']))
                                    <img src="{{ $product['images'][0]['url'] ?? '' }}" 
                                         alt="" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <!-- Ürün Bilgileri -->
                        <td class="px-4 py-3">
                            <div class="max-w-md">
                                <p class="font-medium text-gray-800 dark:text-white line-clamp-2 mb-1">
                                    {{ $product['title'] ?? 'Ürün' }}
                                </p>
                                <div class="flex flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Barkod: <strong>{{ $product['barcode'] ?? '-' }}</strong></span>
                                    <span>|</span>
                                    <span>SKU: {{ $product['stockCode'] ?? '-' }}</span>
                                    <span>|</span>
                                    <span>Marka: {{ $product['brand'] ?? '-' }}</span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Kategori: {{ $product['categoryName'] ?? '-' }}
                                </div>
                            </div>
                        </td>

                        <!-- Durum -->
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col gap-1 items-center">
                                @if($product['approved'] ?? false)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                                        ✓ Onaylı
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                                        ⏳ Bekliyor
                                    </span>
                                @endif

                                @if($product['onSale'] ?? false)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                        Satışta
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                        Pasif
                                    </span>
                                @endif

                                @if($product['locked'] ?? false)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900 dark:text-red-300">
                                        🔒 Kilitli
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Stok -->
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold {{ ($product['quantity'] ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $product['quantity'] ?? 0 }}
                            </span>
                        </td>

                        <!-- Liste Fiyatı -->
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400">
                            {{ number_format($product['listPrice'] ?? 0, 2, ',', '.') }} ₺
                        </td>

                        <!-- Satış Fiyatı -->
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-green-600">
                                {{ number_format($product['salePrice'] ?? 0, 2, ',', '.') }} ₺
                            </span>
                        </td>

                        <!-- Eşleşme -->
                        <td class="px-4 py-3 text-center">
                            @php
                                $listing = \CastMart\Marketplace\Models\MarketplaceListing::where('external_id', $product['barcode'] ?? '')->first();
                            @endphp
                            @if($listing && $listing->product_id)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                                    ✓ ID: {{ $listing->product_id }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    Eşleşmemiş
                                </span>
                            @endif
                        </td>

                        <!-- İşlemler -->
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="editProduct('{{ $product['barcode'] ?? '' }}', {{ $product['quantity'] ?? 0 }}, {{ $product['listPrice'] ?? 0 }}, {{ $product['salePrice'] ?? 0 }})" 
                                        class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600" title="Stok/Fiyat Düzenle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button onclick="linkProduct('{{ $product['barcode'] ?? '' }}', '{{ addslashes($product['title'] ?? '') }}')" 
                                        class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-purple-600" title="CastMart ile Eşleştir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                </button>
                                <a href="{{ $product['productUrl'] ?? '#' }}" target="_blank" 
                                   class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400" title="Trendyol'da Görüntüle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p>Ürün bulunamadı.</p>
                            <p class="text-sm mt-1">Trendyol'dan ürün içe aktarın veya yeni ürün gönderin.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if(($products['totalPages'] ?? 0) > 1)
        <div class="px-4 py-3 border-t dark:border-gray-700 flex items-center justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ ($products['page'] ?? 0) * ($products['size'] ?? 20) + 1 }} - 
                {{ min((($products['page'] ?? 0) + 1) * ($products['size'] ?? 20), $products['totalElements'] ?? 0) }} 
                / {{ $products['totalElements'] ?? 0 }} ürün
            </div>
            <div class="flex items-center gap-1">
                @if(($products['page'] ?? 0) > 0)
                    <a href="?page={{ ($products['page'] ?? 0) - 1 }}" class="px-3 py-1 rounded border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        ← Önceki
                    </a>
                @endif
                
                @for($i = max(0, ($products['page'] ?? 0) - 2); $i < min($products['totalPages'], ($products['page'] ?? 0) + 3); $i++)
                    <a href="?page={{ $i }}" 
                       class="px-3 py-1 rounded {{ ($products['page'] ?? 0) == $i ? 'bg-blue-600 text-white' : 'border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                        {{ $i + 1 }}
                    </a>
                @endfor
                
                @if(($products['page'] ?? 0) < ($products['totalPages'] ?? 1) - 1)
                    <a href="?page={{ ($products['page'] ?? 0) + 1 }}" class="px-3 py-1 rounded border dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        Sonraki →
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Edit Product Modal -->
    <div id="edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Stok ve Fiyat Güncelle</h3>
            
            <form id="edit-form">
                <input type="hidden" id="edit-barcode">
                
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Miktarı</label>
                    <input type="number" id="edit-quantity" min="0"
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Liste Fiyatı (₺)</label>
                        <input type="number" id="edit-list-price" step="0.01" min="0"
                               class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Satış Fiyatı (₺)</label>
                        <input type="number" id="edit-sale-price" step="0.01" min="0"
                               class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="primary-button flex-1">Güncelle</button>
                    <button type="button" onclick="closeEditModal()" class="secondary-button">İptal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Link Product Modal -->
    <div id="link-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-md rounded-lg bg-white p-6 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">CastMart Ürünü ile Eşleştir</h3>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300" id="link-product-name"></p>
            
            <form id="link-form">
                <input type="hidden" id="link-barcode">
                
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">CastMart Ürün ID</label>
                    <input type="number" id="link-product-id" 
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="Ürün ID girin">
                    <p class="mt-1 text-xs text-gray-500">CastMart'taki ürünün ID'sini girin.</p>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="primary-button flex-1">Eşleştir</button>
                    <button type="button" onclick="closeLinkModal()" class="secondary-button">İptal</button>
                </div>
            </form>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            // Import Products
            window.importProducts = async function() {
                if (!confirm('Trendyol\'daki tüm ürünler içe aktarılacak. Devam etmek istiyor musunuz?')) return;
                
                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.products.import") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    alert(data.message);
                    location.reload();
                } catch (error) {
                    alert('İçe aktarma başarısız.');
                }
            }

            // Sync Stock
            window.syncStock = async function() {
                if (!confirm('Eşleştirilmiş ürünlerin stok ve fiyatları Trendyol\'a gönderilecek. Devam?')) return;
                
                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.products.sync-stock") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    alert(data.message);
                } catch (error) {
                    alert('Senkronizasyon başarısız.');
                }
            }

            // Edit Product Modal
            window.editProduct = function(barcode, quantity, listPrice, salePrice) {
                document.getElementById('edit-barcode').value = barcode;
                document.getElementById('edit-quantity').value = quantity;
                document.getElementById('edit-list-price').value = listPrice;
                document.getElementById('edit-sale-price').value = salePrice;
                document.getElementById('edit-modal').classList.remove('hidden');
                document.getElementById('edit-modal').classList.add('flex');
            }

            window.closeEditModal = function() {
                document.getElementById('edit-modal').classList.add('hidden');
                document.getElementById('edit-modal').classList.remove('flex');
            }

            document.getElementById('edit-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const barcode = document.getElementById('edit-barcode').value;
                const quantity = document.getElementById('edit-quantity').value;
                const listPrice = document.getElementById('edit-list-price').value;
                const salePrice = document.getElementById('edit-sale-price').value;

                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.products.sync-stock") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            items: [{
                                barcode: barcode,
                                quantity: parseInt(quantity),
                                listPrice: parseFloat(listPrice),
                                salePrice: parseFloat(salePrice)
                            }]
                        })
                    });
                    const data = await response.json();
                    alert(data.message);
                    if (data.success) {
                        closeEditModal();
                        location.reload();
                    }
                } catch (error) {
                    alert('Güncelleme başarısız.');
                }
            });

            // Link Product Modal
            window.linkProduct = function(barcode, title) {
                document.getElementById('link-barcode').value = barcode;
                document.getElementById('link-product-name').textContent = title;
                document.getElementById('link-modal').classList.remove('hidden');
                document.getElementById('link-modal').classList.add('flex');
            }

            window.closeLinkModal = function() {
                document.getElementById('link-modal').classList.add('hidden');
                document.getElementById('link-modal').classList.remove('flex');
            }

            document.getElementById('link-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const barcode = document.getElementById('link-barcode').value;
                const productId = document.getElementById('link-product-id').value;

                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.products.link") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ barcode, product_id: productId })
                    });
                    const data = await response.json();
                    alert(data.message);
                    if (data.success) {
                        closeLinkModal();
                        location.reload();
                    }
                } catch (error) {
                    alert('Eşleştirme başarısız.');
                }
            });

            // Close modals on backdrop click
            document.getElementById('edit-modal').addEventListener('click', function(e) {
                if (e.target === this) closeEditModal();
            });
            document.getElementById('link-modal').addEventListener('click', function(e) {
                if (e.target === this) closeLinkModal();
            });
        </script>
    @endPushOnce
</x-admin::layouts>
