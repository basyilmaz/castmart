<x-admin::layouts>
    <x-slot:title>
        Varyant Yönetimi
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);">
                <span class="text-2xl">🎨</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Varyant Yönetimi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Renk, beden ve diğer varyantları yönetin</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="syncVariants()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;">
                🔄 Varyantları Senkronize Et
            </button>
        </div>
    </div>

    <!-- Varyant Eşleştirme Tablosu -->
    <div class="grid gap-4 lg:grid-cols-2">
        <!-- Renk Eşleştirme -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">Renk Eşleştirme</h3>
                <button onclick="addColorMapping()" class="text-sm text-blue-600 hover:underline">+ Yeni Ekle</button>
            </div>
            
            <p class="text-xs text-gray-500 mb-3">CastMart'taki renk isimlerini Trendyol'daki isimlerle eşleştirin.</p>
            
            <div id="color-mappings" class="space-y-2 max-h-[400px] overflow-y-auto">
                @php
                $colorMappings = [
                    ['castmart' => 'Beyaz', 'trendyol' => 'Beyaz'],
                    ['castmart' => 'Siyah', 'trendyol' => 'Siyah'],
                    ['castmart' => 'Kırmızı', 'trendyol' => 'Kırmızı'],
                    ['castmart' => 'Mavi', 'trendyol' => 'Mavi'],
                    ['castmart' => 'Yeşil', 'trendyol' => 'Yeşil'],
                    ['castmart' => 'Sarı', 'trendyol' => 'Sarı'],
                    ['castmart' => 'Pembe', 'trendyol' => 'Pembe'],
                    ['castmart' => 'Mor', 'trendyol' => 'Mor'],
                    ['castmart' => 'Turuncu', 'trendyol' => 'Turuncu'],
                    ['castmart' => 'Gri', 'trendyol' => 'Gri'],
                    ['castmart' => 'Kahverengi', 'trendyol' => 'Kahverengi'],
                    ['castmart' => 'Lacivert', 'trendyol' => 'Lacivert'],
                    ['castmart' => 'Bej', 'trendyol' => 'Bej'],
                ];
                @endphp
                @foreach($colorMappings as $mapping)
                <div class="flex items-center gap-2 rounded border p-2 dark:border-gray-700">
                    <input type="text" value="{{ $mapping['castmart'] }}" 
                           class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="CastMart Renk">
                    <span class="text-gray-400">→</span>
                    <input type="text" value="{{ $mapping['trendyol'] }}" 
                           class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="Trendyol Renk">
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Beden Eşleştirme -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">Beden Eşleştirme</h3>
                <button onclick="addSizeMapping()" class="text-sm text-blue-600 hover:underline">+ Yeni Ekle</button>
            </div>
            
            <p class="text-xs text-gray-500 mb-3">CastMart'taki beden isimlerini Trendyol'daki isimlerle eşleştirin.</p>
            
            <div id="size-mappings" class="space-y-2 max-h-[400px] overflow-y-auto">
                @php
                $sizeMappings = [
                    ['castmart' => 'XS', 'trendyol' => 'XS'],
                    ['castmart' => 'S', 'trendyol' => 'S'],
                    ['castmart' => 'M', 'trendyol' => 'M'],
                    ['castmart' => 'L', 'trendyol' => 'L'],
                    ['castmart' => 'XL', 'trendyol' => 'XL'],
                    ['castmart' => 'XXL', 'trendyol' => 'XXL'],
                    ['castmart' => '2XL', 'trendyol' => 'XXL'],
                    ['castmart' => '3XL', 'trendyol' => '3XL'],
                    ['castmart' => 'Extra Small', 'trendyol' => 'XS'],
                    ['castmart' => 'Small', 'trendyol' => 'S'],
                    ['castmart' => 'Medium', 'trendyol' => 'M'],
                    ['castmart' => 'Large', 'trendyol' => 'L'],
                    ['castmart' => 'Extra Large', 'trendyol' => 'XL'],
                    ['castmart' => 'Standart', 'trendyol' => 'STD'],
                    ['castmart' => 'Tek Beden', 'trendyol' => 'TEK EBAT'],
                ];
                @endphp
                @foreach($sizeMappings as $mapping)
                <div class="flex items-center gap-2 rounded border p-2 dark:border-gray-700">
                    <input type="text" value="{{ $mapping['castmart'] }}" 
                           class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="CastMart Beden">
                    <span class="text-gray-400">→</span>
                    <input type="text" value="{{ $mapping['trendyol'] }}" 
                           class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="Trendyol Beden">
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Varyantlı Ürün Gönderme -->
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Varyantlı Ürün Nasıl Gönderilir?</h3>
        
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded border p-4 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold">1</span>
                    <h4 class="font-medium text-gray-800 dark:text-white">Ana Ürün Oluştur</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Tüm varyantlar için aynı <strong>productMainId</strong> kullanın.
                    Bu, ürünlerin grup olarak görüntülenmesini sağlar.
                </p>
            </div>

            <div class="rounded border p-4 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold">2</span>
                    <h4 class="font-medium text-gray-800 dark:text-white">Her Varyant için Barkod</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Her renk/beden kombinasyonu için <strong>farklı barkod</strong> kullanın.
                    Örn: ABC123-BEYAZ-M, ABC123-BEYAZ-L
                </p>
            </div>

            <div class="rounded border p-4 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-3">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-bold">3</span>
                    <h4 class="font-medium text-gray-800 dark:text-white">Özellik Değerleri</h4>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Renk ve beden için <strong>Trendyol'un kabul ettiği değerleri</strong> kullanın.
                    Yukarıdaki eşleştirme tablosunu referans alın.
                </p>
            </div>
        </div>
    </div>

    <!-- Örnek JSON -->
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Örnek Varyant JSON Yapısı</h3>
        
        <pre class="bg-gray-900 text-green-400 p-4 rounded text-xs overflow-x-auto"><code>{
  "items": [
    {
      "barcode": "ABC123-BEYAZ-M",
      "title": "Örnek Ürün - Beyaz",
      "productMainId": "ABC123",
      "brandId": 123456,
      "categoryId": 789,
      "quantity": 10,
      "listPrice": 199.99,
      "salePrice": 149.99,
      "vatRate": 20,
      "cargoCompanyId": 10,
      "attributes": [
        {"attributeId": 338, "attributeValueId": 1001},  // Renk: Beyaz
        {"attributeId": 347, "attributeValueId": 2005}   // Beden: M
      ]
    },
    {
      "barcode": "ABC123-BEYAZ-L",
      "title": "Örnek Ürün - Beyaz",
      "productMainId": "ABC123",
      "brandId": 123456,
      "categoryId": 789,
      "quantity": 15,
      "listPrice": 199.99,
      "salePrice": 149.99,
      "vatRate": 20,
      "cargoCompanyId": 10,
      "attributes": [
        {"attributeId": 338, "attributeValueId": 1001},  // Renk: Beyaz
        {"attributeId": 347, "attributeValueId": 2006}   // Beden: L
      ]
    }
  ]
}</code></pre>
    </div>

    <!-- Bilgi Kartları -->
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="rounded border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/30">
            <h4 class="font-medium text-yellow-800 dark:text-yellow-300 mb-2">⚠️ Önemli Notlar</h4>
            <ul class="text-sm text-yellow-700 dark:text-yellow-400 space-y-1 list-disc list-inside">
                <li>Renk ve beden isimleri <strong>birebir aynı</strong> olmalı</li>
                <li>"XL" yerine "Extra Large" yazarsanız hata alırsınız</li>
                <li>Türkçe karakterler (ı, ğ, ü, ş, ç, ö) dikkatli kullanılmalı</li>
                <li>Trendyol'un kabul ettiği değerleri kontrol edin</li>
            </ul>
        </div>

        <div class="rounded border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30">
            <h4 class="font-medium text-blue-800 dark:text-blue-300 mb-2">💡 İpuçları</h4>
            <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
                <li>Kategori Wizard'dan renk ve beden ID'lerini alın</li>
                <li>productMainId tüm varyantlarda aynı olmalı</li>
                <li>Her varyant için ayrı stok ve fiyat belirtebilirsiniz</li>
                <li>Aynı renk için farklı beden, aynı görsel kullanabilir</li>
            </ul>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            window.addColorMapping = function() {
                const container = document.getElementById('color-mappings');
                const div = document.createElement('div');
                div.className = 'flex items-center gap-2 rounded border p-2 dark:border-gray-700';
                div.innerHTML = `
                    <input type="text" class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="CastMart Renk">
                    <span class="text-gray-400">→</span>
                    <input type="text" class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Trendyol Renk">
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;
                container.appendChild(div);
            }

            window.addSizeMapping = function() {
                const container = document.getElementById('size-mappings');
                const div = document.createElement('div');
                div.className = 'flex items-center gap-2 rounded border p-2 dark:border-gray-700';
                div.innerHTML = `
                    <input type="text" class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="CastMart Beden">
                    <span class="text-gray-400">→</span>
                    <input type="text" class="flex-1 rounded border px-2 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" placeholder="Trendyol Beden">
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700" title="Sil">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                `;
                container.appendChild(div);
            }

            window.syncVariants = function() {
                alert('Varyant senkronizasyonu henüz geliştirme aşamasında.');
            }
        </script>
    @endPushOnce
</x-admin::layouts>
