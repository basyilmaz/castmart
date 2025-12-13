<x-admin::layouts>
    <x-slot:title>
        Varyant Bazlı Hesaplama
    </x-slot>

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                👕 Varyant Bazlı Komisyon Hesaplama
            </p>
            <p class="!leading-normal text-gray-600 dark:text-gray-300">
                Farklı beden ve renk varyantları için ayrı ayrı kar hesaplaması yapın
            </p>
        </div>
        <div class="flex gap-2">
            <button onclick="addVariant()" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Varyant Ekle
            </button>
            <button onclick="calculateAllVariants()" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700 transition">
                🧮 Tümünü Hesapla
            </button>
        </div>
    </div>

    <!-- Ana Ürün Bilgileri -->
    <div class="box-shadow rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📦 Ana Ürün Bilgileri</h3>
        
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ürün Adı</label>
                <input type="text" id="product-name" value="Erkek Basic T-Shirt" 
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                <select id="product-category" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="updateCommissionFromCategory()">
                    <option value="18">Erkek Giyim (%18)</option>
                    <option value="18">Kadın Giyim (%18)</option>
                    <option value="17">Çocuk Giyim (%17)</option>
                    <option value="16">Ayakkabı (%16)</option>
                    <option value="15">Spor Giyim (%15)</option>
                    <option value="12">Elektronik (%12)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temel Alış Fiyatı (₺)</label>
                <input type="number" id="base-purchase-price" value="25" step="0.01"
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                       onchange="applyBasePriceToAll()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temel Satış Fiyatı (₺)</label>
                <input type="number" id="base-sale-price" value="79.90" step="0.01"
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                       onchange="applyBasePriceToAll()">
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button onclick="applyBasePriceToAll()" class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                Tüm Varyantlara Uygula
            </button>
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <input type="checkbox" id="size-price-variation" class="rounded" onchange="toggleSizePricing()">
                Beden bazlı fiyat farkı uygula
            </label>
        </div>
    </div>

    <!-- Beden Bazlı Fiyat Farkları -->
    <div id="size-pricing-section" class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4 mb-6 hidden">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">📏 Beden Bazlı Fiyat Farkları</h4>
        <div class="grid gap-4 md:grid-cols-6">
            <div>
                <label class="block text-xs text-gray-500 mb-1">XS</label>
                <input type="number" id="size-xs-diff" value="0" step="0.01" class="w-full rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">S</label>
                <input type="number" id="size-s-diff" value="0" step="0.01" class="w-full rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">M</label>
                <input type="number" id="size-m-diff" value="0" step="0.01" class="w-full rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">L</label>
                <input type="number" id="size-l-diff" value="5" step="0.01" class="w-full rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">XL</label>
                <input type="number" id="size-xl-diff" value="10" step="0.01" class="w-full rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">XXL</label>
                <input type="number" id="size-xxl-diff" value="15" step="0.01" class="w-full rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">💡 Büyük bedenlerde alış maliyeti farkı (₺)</p>
    </div>

    <!-- Özet Kartları -->
    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Toplam Varyant</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-gray-200" id="total-variants">0</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Toplam Stok</p>
            <p class="text-2xl font-bold text-blue-600" id="total-stock">0</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Tahmini Toplam Kâr</p>
            <p class="text-2xl font-bold text-green-600" id="total-profit">0 ₺</p>
        </div>
        <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4">
            <p class="text-sm text-gray-500">Ortalama Marj</p>
            <p class="text-2xl font-bold text-purple-600" id="avg-margin">0%</p>
        </div>
    </div>

    <!-- Varyant Tablosu -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Beden</th>
                        <th class="px-3 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Renk</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Barkod</th>
                        <th class="px-3 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Alış (₺)</th>
                        <th class="px-3 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Satış (₺)</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Stok</th>
                        <th class="px-3 py-3 text-right font-medium text-gray-600 dark:text-gray-300 bg-green-50 dark:bg-green-900/20">Kâr/Adet</th>
                        <th class="px-3 py-3 text-right font-medium text-gray-600 dark:text-gray-300 bg-green-50 dark:bg-green-900/20">Toplam Kâr</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 dark:text-gray-300 bg-blue-50 dark:bg-blue-900/20">Marj</th>
                        <th class="px-3 py-3 text-center font-medium text-gray-600 dark:text-gray-300 w-10"></th>
                    </tr>
                </thead>
                <tbody id="variants-table" class="divide-y dark:divide-gray-700">
                    <!-- Varyantlar JavaScript ile eklenecek -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hızlı Varyant Ekleme -->
    <div class="box-shadow rounded-lg bg-white dark:bg-gray-900 p-4 mt-6">
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">⚡ Hızlı Varyant Oluşturma</h4>
        
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Bedenler</label>
                <div class="flex flex-wrap gap-2">
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="size-checkbox rounded" value="XS"> XS
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="size-checkbox rounded" value="S" checked> S
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="size-checkbox rounded" value="M" checked> M
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="size-checkbox rounded" value="L" checked> L
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="size-checkbox rounded" value="XL" checked> XL
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="size-checkbox rounded" value="XXL"> XXL
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm text-gray-600 dark:text-gray-400 mb-2">Renkler</label>
                <div class="flex flex-wrap gap-2">
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="color-checkbox rounded" value="Siyah" checked> ⬛ Siyah
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="color-checkbox rounded" value="Beyaz" checked> ⬜ Beyaz
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="color-checkbox rounded" value="Lacivert"> 🟦 Lacivert
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="color-checkbox rounded" value="Gri"> 🔲 Gri
                    </label>
                    <label class="flex items-center gap-1 text-sm">
                        <input type="checkbox" class="color-checkbox rounded" value="Kırmızı"> 🟥 Kırmızı
                    </label>
                </div>
            </div>
        </div>
        
        <div class="mt-4 flex gap-2">
            <button onclick="generateVariants()" class="bg-purple-600 text-white px-4 py-2 rounded text-sm hover:bg-purple-700">
                🎨 Varyantları Oluştur
            </button>
            <button onclick="clearAllVariants()" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm hover:bg-gray-300">
                🗑️ Tümünü Temizle
            </button>
        </div>
    </div>

    <!-- Export -->
    <div class="mt-6 flex gap-2">
        <button onclick="exportVariants()" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
            📥 Excel İndir
        </button>
        <button onclick="printVariants()" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
            🖨️ Yazdır
        </button>
    </div>

    @pushOnce('scripts')
        <script>
            let variantCounter = 0;
            const variants = [];

            // Sayfa yüklendiğinde örnek varyantlar
            document.addEventListener('DOMContentLoaded', function() {
                generateVariants();
            });

            // Varyantları oluştur
            window.generateVariants = function() {
                clearAllVariants();
                
                const sizes = Array.from(document.querySelectorAll('.size-checkbox:checked')).map(cb => cb.value);
                const colors = Array.from(document.querySelectorAll('.color-checkbox:checked')).map(cb => cb.value);
                
                if (sizes.length === 0 || colors.length === 0) {
                    alert('Lütfen en az bir beden ve bir renk seçin.');
                    return;
                }

                const basePurchase = parseFloat(document.getElementById('base-purchase-price').value) || 0;
                const baseSale = parseFloat(document.getElementById('base-sale-price').value) || 0;

                sizes.forEach(size => {
                    colors.forEach(color => {
                        const sizeDiff = getSizeDiff(size);
                        addVariantRow(size, color, basePurchase + sizeDiff, baseSale);
                    });
                });

                calculateAllVariants();
            }

            // Beden farkını al
            function getSizeDiff(size) {
                if (!document.getElementById('size-price-variation').checked) return 0;
                
                const diffInputs = {
                    'XS': 'size-xs-diff',
                    'S': 'size-s-diff',
                    'M': 'size-m-diff',
                    'L': 'size-l-diff',
                    'XL': 'size-xl-diff',
                    'XXL': 'size-xxl-diff',
                };
                
                const input = document.getElementById(diffInputs[size]);
                return input ? parseFloat(input.value) || 0 : 0;
            }

            // Varyant satırı ekle
            window.addVariant = function() {
                const basePurchase = parseFloat(document.getElementById('base-purchase-price').value) || 0;
                const baseSale = parseFloat(document.getElementById('base-sale-price').value) || 0;
                addVariantRow('M', 'Siyah', basePurchase, baseSale);
                calculateAllVariants();
            }

            function addVariantRow(size, color, purchase, sale, stock = 10) {
                variantCounter++;
                const tbody = document.getElementById('variants-table');
                const barcode = 'TY' + Date.now().toString().slice(-6) + variantCounter;
                
                const row = document.createElement('tr');
                row.className = 'variant-row hover:bg-gray-50 dark:hover:bg-gray-800';
                row.dataset.variantId = variantCounter;
                row.innerHTML = `
                    <td class="px-3 py-2">
                        <select class="variant-size w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" onchange="calculateVariant(${variantCounter})">
                            <option value="XS" ${size === 'XS' ? 'selected' : ''}>XS</option>
                            <option value="S" ${size === 'S' ? 'selected' : ''}>S</option>
                            <option value="M" ${size === 'M' ? 'selected' : ''}>M</option>
                            <option value="L" ${size === 'L' ? 'selected' : ''}>L</option>
                            <option value="XL" ${size === 'XL' ? 'selected' : ''}>XL</option>
                            <option value="XXL" ${size === 'XXL' ? 'selected' : ''}>XXL</option>
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <select class="variant-color w-full rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            <option value="Siyah" ${color === 'Siyah' ? 'selected' : ''}>⬛ Siyah</option>
                            <option value="Beyaz" ${color === 'Beyaz' ? 'selected' : ''}>⬜ Beyaz</option>
                            <option value="Lacivert" ${color === 'Lacivert' ? 'selected' : ''}>🟦 Lacivert</option>
                            <option value="Gri" ${color === 'Gri' ? 'selected' : ''}>🔲 Gri</option>
                            <option value="Kırmızı" ${color === 'Kırmızı' ? 'selected' : ''}>🟥 Kırmızı</option>
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" class="variant-barcode w-24 rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" value="${barcode}">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" class="variant-purchase w-20 rounded border px-2 py-1 text-sm text-right dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" step="0.01" value="${purchase.toFixed(2)}" onchange="calculateVariant(${variantCounter})">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" class="variant-sale w-20 rounded border px-2 py-1 text-sm text-right dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" step="0.01" value="${sale.toFixed(2)}" onchange="calculateVariant(${variantCounter})">
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" class="variant-stock w-16 rounded border px-2 py-1 text-sm text-center dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" min="0" value="${stock}" onchange="calculateVariant(${variantCounter})">
                    </td>
                    <td class="px-3 py-2 text-right font-medium bg-green-50 dark:bg-green-900/20">
                        <span class="variant-profit-unit">0.00 ₺</span>
                    </td>
                    <td class="px-3 py-2 text-right font-bold bg-green-50 dark:bg-green-900/20">
                        <span class="variant-profit-total">0.00 ₺</span>
                    </td>
                    <td class="px-3 py-2 text-center bg-blue-50 dark:bg-blue-900/20">
                        <span class="variant-margin px-2 py-1 rounded text-xs font-medium">0%</span>
                    </td>
                    <td class="px-3 py-2">
                        <button onclick="removeVariant(${variantCounter})" class="text-red-500 hover:text-red-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            }

            // Varyantı hesapla
            window.calculateVariant = function(variantId) {
                const row = document.querySelector(`tr[data-variant-id="${variantId}"]`);
                if (!row) return;

                const commission = parseFloat(document.getElementById('product-category').value) || 18;
                const purchase = parseFloat(row.querySelector('.variant-purchase').value) || 0;
                const sale = parseFloat(row.querySelector('.variant-sale').value) || 0;
                const stock = parseInt(row.querySelector('.variant-stock').value) || 0;

                // Hesaplamalar
                const saleExVat = sale / 1.20;
                const commissionAmount = saleExVat * (commission / 100);
                const cargo = 29.99 * 1.20;
                const service = 4.99;
                
                const totalDeductions = commissionAmount + cargo + service;
                const profitUnit = saleExVat - purchase - totalDeductions;
                const profitTotal = profitUnit * stock;
                const margin = sale > 0 ? (profitUnit / sale) * 100 : 0;

                // Göster
                row.querySelector('.variant-profit-unit').textContent = profitUnit.toFixed(2) + ' ₺';
                row.querySelector('.variant-profit-total').textContent = profitTotal.toFixed(2) + ' ₺';
                
                const marginEl = row.querySelector('.variant-margin');
                marginEl.textContent = margin.toFixed(1) + '%';
                
                if (margin < 0) {
                    marginEl.className = 'variant-margin px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800';
                    row.querySelector('.variant-profit-unit').className = 'variant-profit-unit text-red-600';
                    row.querySelector('.variant-profit-total').className = 'variant-profit-total text-red-600';
                } else if (margin < 10) {
                    marginEl.className = 'variant-margin px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800';
                    row.querySelector('.variant-profit-unit').className = 'variant-profit-unit text-yellow-600';
                    row.querySelector('.variant-profit-total').className = 'variant-profit-total text-yellow-600';
                } else {
                    marginEl.className = 'variant-margin px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800';
                    row.querySelector('.variant-profit-unit').className = 'variant-profit-unit text-green-600';
                    row.querySelector('.variant-profit-total').className = 'variant-profit-total text-green-600';
                }

                updateSummary();
            }

            // Tüm varyantları hesapla
            window.calculateAllVariants = function() {
                document.querySelectorAll('.variant-row').forEach(row => {
                    calculateVariant(row.dataset.variantId);
                });
            }

            // Varyant sil
            window.removeVariant = function(variantId) {
                const row = document.querySelector(`tr[data-variant-id="${variantId}"]`);
                if (row) {
                    row.remove();
                    updateSummary();
                }
            }

            // Tümünü temizle
            window.clearAllVariants = function() {
                document.getElementById('variants-table').innerHTML = '';
                variantCounter = 0;
                updateSummary();
            }

            // Özet güncelle
            function updateSummary() {
                const rows = document.querySelectorAll('.variant-row');
                let totalVariants = rows.length;
                let totalStock = 0;
                let totalProfit = 0;
                let totalMargin = 0;

                rows.forEach(row => {
                    totalStock += parseInt(row.querySelector('.variant-stock').value) || 0;
                    const profitText = row.querySelector('.variant-profit-total').textContent;
                    totalProfit += parseFloat(profitText.replace(' ₺', '').replace(',', '.')) || 0;
                    const marginText = row.querySelector('.variant-margin').textContent;
                    totalMargin += parseFloat(marginText.replace('%', '')) || 0;
                });

                document.getElementById('total-variants').textContent = totalVariants;
                document.getElementById('total-stock').textContent = totalStock;
                document.getElementById('total-profit').textContent = totalProfit.toFixed(2) + ' ₺';
                document.getElementById('avg-margin').textContent = totalVariants > 0 ? (totalMargin / totalVariants).toFixed(1) + '%' : '0%';
            }

            // Temel fiyatı tümüne uygula
            window.applyBasePriceToAll = function() {
                const basePurchase = parseFloat(document.getElementById('base-purchase-price').value) || 0;
                const baseSale = parseFloat(document.getElementById('base-sale-price').value) || 0;

                document.querySelectorAll('.variant-row').forEach(row => {
                    const size = row.querySelector('.variant-size').value;
                    const sizeDiff = getSizeDiff(size);
                    row.querySelector('.variant-purchase').value = (basePurchase + sizeDiff).toFixed(2);
                    row.querySelector('.variant-sale').value = baseSale.toFixed(2);
                });

                calculateAllVariants();
            }

            // Beden fiyatlandırma toggle
            window.toggleSizePricing = function() {
                const section = document.getElementById('size-pricing-section');
                section.classList.toggle('hidden');
            }

            // Kategoriden komisyon güncelle
            window.updateCommissionFromCategory = function() {
                calculateAllVariants();
            }

            // Excel export
            window.exportVariants = function() {
                const productName = document.getElementById('product-name').value;
                let csvContent = "data:text/csv;charset=utf-8,";
                csvContent += "Ürün,Beden,Renk,Barkod,Alış,Satış,Stok,Kâr/Adet,Toplam Kâr,Marj\n";

                document.querySelectorAll('.variant-row').forEach(row => {
                    const size = row.querySelector('.variant-size').value;
                    const color = row.querySelector('.variant-color').value;
                    const barcode = row.querySelector('.variant-barcode').value;
                    const purchase = row.querySelector('.variant-purchase').value;
                    const sale = row.querySelector('.variant-sale').value;
                    const stock = row.querySelector('.variant-stock').value;
                    const profitUnit = row.querySelector('.variant-profit-unit').textContent;
                    const profitTotal = row.querySelector('.variant-profit-total').textContent;
                    const margin = row.querySelector('.variant-margin').textContent;

                    csvContent += `"${productName}",${size},"${color}",${barcode},${purchase},${sale},${stock},${profitUnit},${profitTotal},${margin}\n`;
                });

                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "varyant-hesaplama-" + Date.now() + ".csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            // Yazdır
            window.printVariants = function() {
                window.print();
            }
        </script>
    @endPushOnce
</x-admin::layouts>
