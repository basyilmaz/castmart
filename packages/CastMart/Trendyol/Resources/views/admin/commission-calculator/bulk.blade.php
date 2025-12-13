<x-admin::layouts>
    <x-slot:title>
        Toplu Komisyon Hesaplama
    </x-slot>

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                📊 Toplu Komisyon Hesaplama
            </p>
            <p class="!leading-normal text-gray-600 dark:text-gray-300">
                Birden fazla ürün için aynı anda kar hesaplaması yapın
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="addRow()" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ürün Ekle
            </button>
            <button onclick="calculateAll()" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition">
                🧮 Tümünü Hesapla
            </button>
            <button onclick="exportResults()" class="flex items-center gap-2 bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700 transition">
                📥 Excel İndir
            </button>
        </div>
    </div>

    <!-- Özet Kartları -->
    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Toplam Ürün</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-200" id="total-products">0</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Toplam Ciro</p>
            <p class="text-2xl font-bold text-blue-600" id="total-revenue">0 ₺</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Toplam Kâr</p>
            <p class="text-2xl font-bold text-green-600" id="total-profit">0 ₺</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Ortalama Marj</p>
            <p class="text-2xl font-bold text-purple-600" id="avg-margin">0%</p>
        </div>
    </div>

    <!-- Global Ayarlar -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">⚙️ Varsayılan Ayarlar</h3>
        <div class="grid gap-4 md:grid-cols-5">
            <div>
                <label class="block text-xs text-gray-500 mb-1">KDV Oranı (Alış)</label>
                <select id="default-purchase-vat" class="w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="0">%0</option>
                    <option value="10">%10</option>
                    <option value="20" selected>%20</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Komisyon Oranı</label>
                <input type="number" id="default-commission" value="15" step="0.1" class="w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kargo Ücreti</label>
                <input type="number" id="default-cargo" value="29.99" step="0.01" class="w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Hizmet Bedeli</label>
                <input type="number" id="default-service" value="4.99" step="0.01" class="w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div class="flex items-end">
                <button onclick="applyDefaults()" class="w-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-sm hover:bg-gray-300">
                    Tümüne Uygula
                </button>
            </div>
        </div>
    </div>

    <!-- Ürün Tablosu -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-2 py-3 text-left font-medium text-gray-600 dark:text-gray-300 w-8">#</th>
                        <th class="px-2 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Ürün Adı</th>
                        <th class="px-2 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Alış (₺)</th>
                        <th class="px-2 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Satış (₺)</th>
                        <th class="px-2 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Kom. %</th>
                        <th class="px-2 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Kargo (₺)</th>
                        <th class="px-2 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Adet</th>
                        <th class="px-2 py-3 text-right font-medium text-gray-600 dark:text-gray-300 bg-green-50 dark:bg-green-900/20">Kâr/Adet</th>
                        <th class="px-2 py-3 text-right font-medium text-gray-600 dark:text-gray-300 bg-green-50 dark:bg-green-900/20">Toplam Kâr</th>
                        <th class="px-2 py-3 text-center font-medium text-gray-600 dark:text-gray-300 bg-blue-50 dark:bg-blue-900/20">Marj %</th>
                        <th class="px-2 py-3 text-center font-medium text-gray-600 dark:text-gray-300 w-10"></th>
                    </tr>
                </thead>
                <tbody id="products-table" class="divide-y dark:divide-gray-700">
                    <!-- Örnek satırlar JavaScript ile eklenecek -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Özet -->
    <div class="box-shadow rounded-lg bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📈 Özet Analiz</h3>
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm text-gray-500">En Kârlı Ürün</p>
                <p class="font-bold text-green-600" id="most-profitable">-</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">En Düşük Marjlı Ürün</p>
                <p class="font-bold text-red-600" id="lowest-margin">-</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Zarar Eden Ürün</p>
                <p class="font-bold text-red-600" id="losing-products">0 adet</p>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script>
            let rowCounter = 0;
            const products = [];

            // Sayfa yüklendiğinde örnek satırlar ekle
            document.addEventListener('DOMContentLoaded', function() {
                addRow('Örnek Ürün 1', 50, 99.90, 15, 29.99, 10);
                addRow('Örnek Ürün 2', 80, 149.90, 18, 29.99, 5);
                addRow('Örnek Ürün 3', 25, 49.90, 12, 29.99, 20);
                calculateAll();
            });

            // Satır ekle
            window.addRow = function(name = '', purchasePrice = '', salePrice = '', commission = '', cargo = '', quantity = 1) {
                rowCounter++;
                const tbody = document.getElementById('products-table');
                
                const defaults = {
                    commission: commission || document.getElementById('default-commission').value,
                    cargo: cargo || document.getElementById('default-cargo').value,
                };

                const row = document.createElement('tr');
                row.className = 'product-row hover:bg-gray-50 dark:hover:bg-gray-800';
                row.dataset.rowId = rowCounter;
                row.innerHTML = `
                    <td class="px-2 py-2 text-gray-500">${rowCounter}</td>
                    <td class="px-2 py-2">
                        <input type="text" class="product-name w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Ürün adı" value="${name}">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" class="purchase-price w-20 rounded border px-2 py-1 text-sm text-right dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" step="0.01" value="${purchasePrice}" onchange="calculateRow(${rowCounter})">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" class="sale-price w-20 rounded border px-2 py-1 text-sm text-right dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" step="0.01" value="${salePrice}" onchange="calculateRow(${rowCounter})">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" class="commission w-16 rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" step="0.1" value="${defaults.commission}" onchange="calculateRow(${rowCounter})">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" class="cargo w-20 rounded border px-2 py-1 text-sm text-right dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" step="0.01" value="${defaults.cargo}" onchange="calculateRow(${rowCounter})">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" class="quantity w-16 rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" min="1" value="${quantity}" onchange="calculateRow(${rowCounter})">
                    </td>
                    <td class="px-2 py-2 text-right font-medium bg-green-50 dark:bg-green-900/20">
                        <span class="profit-per-unit">0.00 ₺</span>
                    </td>
                    <td class="px-2 py-2 text-right font-bold bg-green-50 dark:bg-green-900/20">
                        <span class="total-profit">0.00 ₺</span>
                    </td>
                    <td class="px-2 py-2 text-center bg-blue-50 dark:bg-blue-900/20">
                        <span class="margin-badge px-2 py-1 rounded text-xs font-medium">0%</span>
                    </td>
                    <td class="px-2 py-2 text-center">
                        <button onclick="removeRow(${rowCounter})" class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
                calculateRow(rowCounter);
            }

            // Satır sil
            window.removeRow = function(rowId) {
                const row = document.querySelector(`tr[data-row-id="${rowId}"]`);
                if (row) {
                    row.remove();
                    updateSummary();
                }
            }

            // Tek satır hesapla
            window.calculateRow = function(rowId) {
                const row = document.querySelector(`tr[data-row-id="${rowId}"]`);
                if (!row) return;

                const purchasePrice = parseFloat(row.querySelector('.purchase-price').value) || 0;
                const salePrice = parseFloat(row.querySelector('.sale-price').value) || 0;
                const commission = parseFloat(row.querySelector('.commission').value) || 0;
                const cargo = parseFloat(row.querySelector('.cargo').value) || 0;
                const quantity = parseInt(row.querySelector('.quantity').value) || 1;
                const purchaseVat = parseFloat(document.getElementById('default-purchase-vat').value) || 20;
                const service = parseFloat(document.getElementById('default-service').value) || 4.99;

                // Hesaplamalar
                const salePriceExVat = salePrice / 1.20; // Satış KDV'si %20 varsayılan
                const commissionAmount = salePriceExVat * (commission / 100);
                const purchasePriceWithVat = purchasePrice * (1 + purchaseVat / 100);
                const cargoWithVat = cargo * 1.20;
                
                const totalDeductions = commissionAmount + cargoWithVat + service;
                const profitPerUnit = salePriceExVat - purchasePrice - totalDeductions;
                const totalProfit = profitPerUnit * quantity;
                const margin = salePrice > 0 ? (profitPerUnit / salePrice) * 100 : 0;

                // Görüntüle
                row.querySelector('.profit-per-unit').textContent = profitPerUnit.toFixed(2) + ' ₺';
                row.querySelector('.total-profit').textContent = totalProfit.toFixed(2) + ' ₺';
                
                const marginBadge = row.querySelector('.margin-badge');
                marginBadge.textContent = margin.toFixed(1) + '%';
                
                if (margin < 0) {
                    marginBadge.className = 'margin-badge px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800';
                    row.querySelector('.profit-per-unit').className = 'profit-per-unit text-red-600';
                    row.querySelector('.total-profit').className = 'total-profit text-red-600';
                } else if (margin < 10) {
                    marginBadge.className = 'margin-badge px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800';
                    row.querySelector('.profit-per-unit').className = 'profit-per-unit text-yellow-600';
                    row.querySelector('.total-profit').className = 'total-profit text-yellow-600';
                } else {
                    marginBadge.className = 'margin-badge px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800';
                    row.querySelector('.profit-per-unit').className = 'profit-per-unit text-green-600';
                    row.querySelector('.total-profit').className = 'total-profit text-green-600';
                }

                updateSummary();
            }

            // Tümünü hesapla
            window.calculateAll = function() {
                document.querySelectorAll('.product-row').forEach(row => {
                    const rowId = row.dataset.rowId;
                    calculateRow(rowId);
                });
            }

            // Özet güncelle
            function updateSummary() {
                const rows = document.querySelectorAll('.product-row');
                let totalProducts = rows.length;
                let totalRevenue = 0;
                let totalProfit = 0;
                let totalMargin = 0;
                let losingCount = 0;
                let mostProfitable = { name: '-', profit: -Infinity };
                let lowestMargin = { name: '-', margin: Infinity };

                rows.forEach(row => {
                    const name = row.querySelector('.product-name').value || 'Ürün';
                    const salePrice = parseFloat(row.querySelector('.sale-price').value) || 0;
                    const quantity = parseInt(row.querySelector('.quantity').value) || 1;
                    const profitText = row.querySelector('.total-profit').textContent;
                    const profit = parseFloat(profitText.replace(' ₺', '').replace(',', '.')) || 0;
                    const marginText = row.querySelector('.margin-badge').textContent;
                    const margin = parseFloat(marginText.replace('%', '')) || 0;

                    totalRevenue += salePrice * quantity;
                    totalProfit += profit;
                    totalMargin += margin;

                    if (profit < 0) losingCount++;
                    if (profit > mostProfitable.profit) {
                        mostProfitable = { name, profit };
                    }
                    if (margin < lowestMargin.margin && margin !== 0) {
                        lowestMargin = { name, margin };
                    }
                });

                document.getElementById('total-products').textContent = totalProducts;
                document.getElementById('total-revenue').textContent = totalRevenue.toFixed(2) + ' ₺';
                document.getElementById('total-profit').textContent = totalProfit.toFixed(2) + ' ₺';
                document.getElementById('avg-margin').textContent = totalProducts > 0 ? (totalMargin / totalProducts).toFixed(1) + '%' : '0%';
                
                document.getElementById('most-profitable').textContent = mostProfitable.name + ' (' + mostProfitable.profit.toFixed(2) + ' ₺)';
                document.getElementById('lowest-margin').textContent = lowestMargin.name + ' (' + lowestMargin.margin.toFixed(1) + '%)';
                document.getElementById('losing-products').textContent = losingCount + ' adet';
            }

            // Varsayılanları uygula
            window.applyDefaults = function() {
                const commission = document.getElementById('default-commission').value;
                const cargo = document.getElementById('default-cargo').value;

                document.querySelectorAll('.product-row').forEach(row => {
                    row.querySelector('.commission').value = commission;
                    row.querySelector('.cargo').value = cargo;
                });

                calculateAll();
            }

            // Excel export
            window.exportResults = function() {
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Ürün Adı,Alış Fiyatı,Satış Fiyatı,Komisyon %,Kargo,Adet,Kâr/Adet,Toplam Kâr,Marj %\n";

                document.querySelectorAll('.product-row').forEach(row => {
                    const name = row.querySelector('.product-name').value;
                    const purchase = row.querySelector('.purchase-price').value;
                    const sale = row.querySelector('.sale-price').value;
                    const commission = row.querySelector('.commission').value;
                    const cargo = row.querySelector('.cargo').value;
                    const quantity = row.querySelector('.quantity').value;
                    const profitPerUnit = row.querySelector('.profit-per-unit').textContent;
                    const totalProfit = row.querySelector('.total-profit').textContent;
                    const margin = row.querySelector('.margin-badge').textContent;

                    csvContent += `"${name}",${purchase},${sale},${commission},${cargo},${quantity},${profitPerUnit},${totalProfit},${margin}\n`;
                });

                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "toplu-komisyon-hesaplama-" + Date.now() + ".csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @endPushOnce
</x-admin::layouts>
