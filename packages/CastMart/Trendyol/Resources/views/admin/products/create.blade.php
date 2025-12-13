<x-admin::layouts>
    <x-slot:title>
        Trendyol'a Ürün Gönder
    </x-slot>

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                Trendyol'a Ürün Gönder
            </p>
            <p class="!leading-normal text-gray-600 dark:text-gray-300">
                CastMart ürününü Trendyol'a gönderin
            </p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <!-- Sol: CastMart Ürün Seçimi -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-base font-semibold text-gray-700 dark:text-gray-300">1. CastMart Ürünü Seçin</h3>
            
            <div class="mb-4">
                <input type="text" id="product-search" placeholder="Ürün ara..." 
                       class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            </div>

            <div id="product-list" class="max-h-[400px] overflow-y-auto">
                @foreach($castmartProducts ?? [] as $product)
                <div class="flex cursor-pointer items-center gap-3 border-b p-3 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800" 
                     onclick="selectProduct({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price }})">
                    <input type="radio" name="product" value="{{ $product->id }}" class="product-radio">
                    <div class="h-12 w-12 overflow-hidden rounded bg-gray-100">
                        @if($product->base_image)
                            <img src="{{ $product->base_image->url }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">SKU: {{ $product->sku }} | {{ number_format($product->price, 2, ',', '.') }} ₺</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Sağ: Trendyol Bilgileri -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-base font-semibold text-gray-700 dark:text-gray-300">2. Trendyol Bilgileri</h3>

            <form id="send-form" action="{{ route('admin.marketplace.trendyol.products.send') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" id="selected-product-id">

                <x-admin::form.control-group class="mb-4">
                    <x-admin::form.control-group.label class="required">Seçilen Ürün</x-admin::form.control-group.label>
                    <input type="text" id="selected-product-name" readonly 
                           class="w-full rounded-md border bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="Sol taraftan ürün seçin">
                </x-admin::form.control-group>

                <x-admin::form.control-group class="mb-4">
                    <x-admin::form.control-group.label class="required">Trendyol Kategorisi</x-admin::form.control-group.label>
                    <select name="category_id" id="category-select" required
                            class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">Kategori seçin...</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Trendyol kategorisi seçilmelidir</p>
                </x-admin::form.control-group>

                <x-admin::form.control-group class="mb-4">
                    <x-admin::form.control-group.label class="required">Marka</x-admin::form.control-group.label>
                    <input type="text" name="brand_name" id="brand-input" required
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                           placeholder="Marka adı">
                </x-admin::form.control-group>

                <x-admin::form.control-group class="mb-4">
                    <x-admin::form.control-group.label class="required">Barkod</x-admin::form.control-group.label>
                    <input type="text" name="barcode" required
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                           placeholder="Ürün barkodu">
                </x-admin::form.control-group>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Liste Fiyatı</x-admin::form.control-group.label>
                        <input type="number" name="list_price" id="list-price" step="0.01" required
                               class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">Satış Fiyatı</x-admin::form.control-group.label>
                        <input type="number" name="sale_price" id="sale-price" step="0.01" required
                               class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    </x-admin::form.control-group>
                </div>

                <x-admin::form.control-group class="mb-4">
                    <x-admin::form.control-group.label class="required">Stok Miktarı</x-admin::form.control-group.label>
                    <input type="number" name="quantity" required
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                           placeholder="Stok adedi">
                </x-admin::form.control-group>

                <x-admin::form.control-group class="mb-4">
                    <x-admin::form.control-group.label>Kargo Firması</x-admin::form.control-group.label>
                    <select name="cargo_company_id" 
                            class="w-full rounded-md border px-3 py-2 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                        @foreach(config('trendyol.cargo_providers', []) as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </x-admin::form.control-group>

                <div class="flex gap-2">
                    <button type="submit" class="primary-button flex-1">Trendyol'a Gönder</button>
                    <a href="{{ route('admin.marketplace.trendyol.products') }}" class="secondary-button">İptal</a>
                </div>
            </form>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            // Kategorileri yükle
            async function loadCategories() {
                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.api.categories") }}');
                    const data = await response.json();
                    const select = document.getElementById('category-select');
                    
                    function addCategories(categories, prefix = '') {
                        categories.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = prefix + cat.name;
                            select.appendChild(option);
                            if (cat.subCategories) {
                                addCategories(cat.subCategories, prefix + '  ');
                            }
                        });
                    }
                    
                    if (data.categories) {
                        addCategories(data.categories);
                    }
                } catch (error) {
                    console.error('Kategoriler yüklenemedi:', error);
                }
            }
            
            loadCategories();

            window.selectProduct = function(id, name, price) {
                document.getElementById('selected-product-id').value = id;
                document.getElementById('selected-product-name').value = name;
                document.getElementById('list-price').value = price;
                document.getElementById('sale-price').value = price;
                
                document.querySelectorAll('.product-radio').forEach(r => r.checked = false);
                document.querySelector(`input[value="${id}"]`).checked = true;
            }

            // Ürün arama
            document.getElementById('product-search').addEventListener('input', function(e) {
                const search = e.target.value.toLowerCase();
                document.querySelectorAll('#product-list > div').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(search) ? '' : 'none';
                });
            });
        </script>
    @endPushOnce
</x-admin::layouts>
