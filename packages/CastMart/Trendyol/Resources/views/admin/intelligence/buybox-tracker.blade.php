<x-admin::layouts>
    <x-slot:title>
        BuyBox Takip
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center shadow-lg">
                <span class="text-2xl">🎯</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">BuyBox Takip</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ürün BuyBox durumları ve rakip analizi</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="refreshBuybox()" class="flex items-center gap-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-4 py-2 rounded-lg text-sm hover:shadow-lg transition">
                🔄 Yenile
            </button>
        </div>
    </div>

    <!-- Özet Kartları -->
    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="box-shadow rounded-lg bg-gradient-to-br from-green-500 to-green-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">BuyBox Kazanılan</p>
                    <p class="text-3xl font-bold" id="buybox-won">24</p>
                </div>
                <span class="text-4xl opacity-80">🏆</span>
            </div>
            <p class="text-sm mt-2 opacity-80">Toplam ürünlerin %65'i</p>
        </div>
        
        <div class="box-shadow rounded-lg bg-gradient-to-br from-red-500 to-red-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">BuyBox Kaybedilen</p>
                    <p class="text-3xl font-bold" id="buybox-lost">8</p>
                </div>
                <span class="text-4xl opacity-80">❌</span>
            </div>
            <p class="text-sm mt-2 opacity-80">Fiyat rekabeti gerekiyor</p>
        </div>
        
        <div class="box-shadow rounded-lg bg-gradient-to-br from-yellow-500 to-yellow-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">Risk Altında</p>
                    <p class="text-3xl font-bold" id="buybox-risk">5</p>
                </div>
                <span class="text-4xl opacity-80">⚠️</span>
            </div>
            <p class="text-sm mt-2 opacity-80">Rakip farkı &lt;5%</p>
        </div>
        
        <div class="box-shadow rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-80">BuyBox Oranı</p>
                    <p class="text-3xl font-bold" id="buybox-rate">65%</p>
                </div>
                <span class="text-4xl opacity-80">📊</span>
            </div>
            <p class="text-sm mt-2 opacity-80">Hedef: %80</p>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="box-shadow rounded-lg bg-white p-4 dark:bg-gray-900 mb-6">
        <div class="grid gap-4 md:grid-cols-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Durum</label>
                <select id="filter-status" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="filterProducts()">
                    <option value="all">Tümü</option>
                    <option value="won">BuyBox Kazanılan</option>
                    <option value="lost">BuyBox Kaybedilen</option>
                    <option value="risk">Risk Altında</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                <select id="filter-category" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="filterProducts()">
                    <option value="all">Tümü</option>
                    <option value="moda">Moda</option>
                    <option value="elektronik">Elektronik</option>
                    <option value="ev-yasam">Ev & Yaşam</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fiyat Farkı</label>
                <select id="filter-price-diff" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="filterProducts()">
                    <option value="all">Tümü</option>
                    <option value="cheaper">Biz Daha Ucuz</option>
                    <option value="same">Aynı Fiyat</option>
                    <option value="expensive">Biz Daha Pahalı</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sıralama</label>
                <select id="filter-sort" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="filterProducts()">
                    <option value="priority">Öncelik (Acil)</option>
                    <option value="price-diff">Fiyat Farkı</option>
                    <option value="name">Ürün Adı</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="bulkUpdatePrices()" class="w-full bg-orange-600 text-white px-4 py-2 rounded text-sm hover:bg-orange-700 transition">
                    🔄 Toplu Fiyat Güncelle
                </button>
            </div>
        </div>
    </div>

    <!-- Ürün Listesi -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">
                            <input type="checkbox" id="select-all" onchange="toggleSelectAll()" class="rounded">
                        </th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Sizin Fiyat</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Rakip Fiyat</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Fark</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">BuyBox</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Kazanma Şansı</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">İşlem</th>
                    </tr>
                </thead>
                <tbody id="products-table" class="divide-y dark:divide-gray-700">
                    <!-- Ürün 1 - BuyBox Kazanılmış -->
                    <tr class="product-row hover:bg-gray-50 dark:hover:bg-gray-800" data-status="won">
                        <td class="px-4 py-3"><input type="checkbox" class="product-checkbox rounded"></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">📦</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">Mavi Kazak - Erkek</p>
                                    <p class="text-xs text-gray-500">SKU: TY-001 | Stok: 45</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">149.90 ₺</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">159.90 ₺</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-green-600 font-medium">-10.00 ₺ (-6.7%)</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">✓ Kazanıldı</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 95%"></div>
                                </div>
                                <span class="text-xs font-medium text-green-600">95%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-blue-600 hover:underline text-sm">Detay</button>
                        </td>
                    </tr>

                    <!-- Ürün 2 - BuyBox Kaybedilmiş -->
                    <tr class="product-row hover:bg-gray-50 dark:hover:bg-gray-800 bg-red-50 dark:bg-red-900/10" data-status="lost">
                        <td class="px-4 py-3"><input type="checkbox" class="product-checkbox rounded"></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">📦</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">Siyah Pantolon - Erkek</p>
                                    <p class="text-xs text-gray-500">SKU: TY-002 | Stok: 28</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">199.90 ₺</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">189.90 ₺</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-red-600 font-medium">+10.00 ₺ (+5.3%)</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">✗ Kaybedildi</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: 15%"></div>
                                </div>
                                <span class="text-xs font-medium text-red-600">15%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="showPriceModal('TY-002', 199.90, 189.90)" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                Fiyat Düşür
                            </button>
                        </td>
                    </tr>

                    <!-- Ürün 3 - Risk Altında -->
                    <tr class="product-row hover:bg-gray-50 dark:hover:bg-gray-800 bg-yellow-50 dark:bg-yellow-900/10" data-status="risk">
                        <td class="px-4 py-3"><input type="checkbox" class="product-checkbox rounded"></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">📦</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">Beyaz Gömlek - Kadın</p>
                                    <p class="text-xs text-gray-500">SKU: TY-003 | Stok: 15</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">179.90 ₺</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">182.90 ₺</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-green-600 font-medium">-3.00 ₺ (-1.6%)</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">⚠ Risk</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 55%"></div>
                                </div>
                                <span class="text-xs font-medium text-yellow-600">55%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-yellow-600 hover:underline text-sm">İzle</button>
                        </td>
                    </tr>

                    <!-- Ürün 4 - BuyBox Kazanılmış -->
                    <tr class="product-row hover:bg-gray-50 dark:hover:bg-gray-800" data-status="won">
                        <td class="px-4 py-3"><input type="checkbox" class="product-checkbox rounded"></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">📦</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">Spor Ayakkabı - Unisex</p>
                                    <p class="text-xs text-gray-500">SKU: TY-004 | Stok: 8</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">349.90 ₺</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">379.90 ₺</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-green-600 font-medium">-30.00 ₺ (-7.9%)</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">✓ Kazanıldı</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 88%"></div>
                                </div>
                                <span class="text-xs font-medium text-green-600">88%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button class="text-blue-600 hover:underline text-sm">Detay</button>
                        </td>
                    </tr>

                    <!-- Ürün 5 - BuyBox Kaybedilmiş -->
                    <tr class="product-row hover:bg-gray-50 dark:hover:bg-gray-800 bg-red-50 dark:bg-red-900/10" data-status="lost">
                        <td class="px-4 py-3"><input type="checkbox" class="product-checkbox rounded"></td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">📦</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-200">Kış Montu - Erkek</p>
                                    <p class="text-xs text-gray-500">SKU: TY-005 | Stok: 12</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">549.90 ₺</td>
                        <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">499.90 ₺</td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-red-600 font-medium">+50.00 ₺ (+10%)</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">✗ Kaybedildi</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: 5%"></div>
                                </div>
                                <span class="text-xs font-medium text-red-600">5%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="showPriceModal('TY-005', 549.90, 499.90)" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">
                                Fiyat Düşür
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fiyat Güncelleme Modal -->
    <div id="price-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">💰 Fiyat Güncelle</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ürün</label>
                    <input type="text" id="modal-product-sku" readonly class="w-full rounded border px-3 py-2 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mevcut Fiyat</label>
                        <input type="text" id="modal-current-price" readonly class="w-full rounded border px-3 py-2 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rakip Fiyatı</label>
                        <input type="text" id="modal-competitor-price" readonly class="w-full rounded border px-3 py-2 bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yeni Fiyat</label>
                    <input type="number" id="modal-new-price" step="0.01" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <p class="text-xs text-gray-500 mt-1">Önerilen: Rakipten 1₺ daha düşük</p>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded">
                    <p class="text-sm text-blue-800 dark:text-blue-300">
                        💡 <strong>Öneri:</strong> <span id="modal-suggestion">189.89 ₺</span> fiyatla %85 BuyBox kazanma şansınız var.
                    </p>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button onclick="closePriceModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                    İptal
                </button>
                <button onclick="updatePrice()" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Güncelle
                </button>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            // Fiyat modalı göster
            window.showPriceModal = function(sku, currentPrice, competitorPrice) {
                document.getElementById('modal-product-sku').value = sku;
                document.getElementById('modal-current-price').value = currentPrice.toFixed(2) + ' ₺';
                document.getElementById('modal-competitor-price').value = competitorPrice.toFixed(2) + ' ₺';
                document.getElementById('modal-new-price').value = (competitorPrice - 0.01).toFixed(2);
                document.getElementById('modal-suggestion').textContent = (competitorPrice - 0.01).toFixed(2) + ' ₺';
                
                document.getElementById('price-modal').classList.remove('hidden');
                document.getElementById('price-modal').classList.add('flex');
            }

            // Fiyat modalı kapat
            window.closePriceModal = function() {
                document.getElementById('price-modal').classList.add('hidden');
                document.getElementById('price-modal').classList.remove('flex');
            }

            // Fiyat güncelle
            window.updatePrice = function() {
                const sku = document.getElementById('modal-product-sku').value;
                const newPrice = document.getElementById('modal-new-price').value;
                
                alert('Fiyat güncellendi: ' + sku + ' -> ' + newPrice + ' ₺');
                closePriceModal();
            }

            // Ürünleri filtrele
            window.filterProducts = function() {
                const status = document.getElementById('filter-status').value;
                const rows = document.querySelectorAll('.product-row');
                
                rows.forEach(row => {
                    if (status === 'all' || row.dataset.status === status) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Tümünü seç
            window.toggleSelectAll = function() {
                const selectAll = document.getElementById('select-all').checked;
                document.querySelectorAll('.product-checkbox').forEach(cb => {
                    cb.checked = selectAll;
                });
            }

            // Toplu fiyat güncelle
            window.bulkUpdatePrices = function() {
                const selected = document.querySelectorAll('.product-checkbox:checked').length;
                if (selected === 0) {
                    alert('Lütfen en az bir ürün seçin.');
                    return;
                }
                alert(selected + ' ürün için toplu fiyat güncelleme başlatılacak.');
            }

            // Yenile
            window.refreshBuybox = function() {
                location.reload();
            }
        </script>
    @endPushOnce
</x-admin::layouts>
