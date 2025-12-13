<x-admin::layouts>
    <x-slot:title>
        Akıllı Kategori Wizard
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <span class="text-2xl">🧙</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Kategori Wizard</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ürününüze uygun kategori ve özellikleri kolayca bulun</p>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.marketplace.trendyol.products.create') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white;">
                ➕ Ürün Gönderme Sayfası →
            </a>
        </div>
    </div>

    <!-- Arama Modu Seçimi -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <div class="flex flex-wrap gap-4">
            <button id="mode-search" onclick="setSearchMode('search')" class="flex-1 rounded-lg border-2 border-blue-500 bg-blue-50 p-4 text-center dark:bg-blue-900/30">
                <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="font-medium text-blue-700 dark:text-blue-300">Ürün Adı ile Ara</p>
                <p class="text-xs text-blue-600 mt-1">Ürün adınızı yazın, AI size kategori önersin</p>
            </button>
            
            <button id="mode-browse" onclick="setSearchMode('browse')" class="flex-1 rounded-lg border-2 border-gray-300 p-4 text-center dark:border-gray-700 hover:border-blue-500">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <p class="font-medium text-gray-700 dark:text-gray-300">Kategori Ağacından Seç</p>
                <p class="text-xs text-gray-500 mt-1">Kategori hiyerarşisinde gezinin</p>
            </button>

            <button id="mode-competitor" onclick="setSearchMode('competitor')" class="flex-1 rounded-lg border-2 border-gray-300 p-4 text-center dark:border-gray-700 hover:border-blue-500">
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <p class="font-medium text-gray-700 dark:text-gray-300">Rakip URL'den Bul</p>
                <p class="text-xs text-gray-500 mt-1">Benzer ürünün Trendyol linkini yapıştırın</p>
            </button>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <!-- Sol: Arama/Seçim -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-base font-semibold text-gray-700 dark:text-gray-300">1. Kategori Bul</h3>
            
            <!-- Arama Modu -->
            <div id="search-mode-panel">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Ürün adınızı yazın:
                    </label>
                    <input type="text" id="product-name-search" 
                           placeholder="Örn: Dijital Gebelik Testi..." 
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <button onclick="searchByProductName()" class="mt-2 w-full secondary-button">
                        🔍 Kategori Öner
                    </button>
                </div>

                <div id="ai-suggestions" class="hidden">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Önerilen Kategoriler:</p>
                    <div id="suggestion-list" class="space-y-2">
                        <!-- JavaScript ile doldurulacak -->
                    </div>
                </div>

                <div class="mt-4 border-t pt-4 dark:border-gray-700">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Veya kategori adı ara:
                    </label>
                    <input type="text" id="category-search" 
                           placeholder="Kategori ara..." 
                           class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>

                <div id="search-results" class="mt-2 max-h-[300px] overflow-y-auto"></div>
            </div>

            <!-- Ağaç Modu -->
            <div id="browse-mode-panel" class="hidden">
                <div id="breadcrumb" class="flex flex-wrap gap-1 mb-3 text-sm">
                    <span class="text-gray-500">Tüm Kategoriler</span>
                </div>
                
                <div id="category-list" class="max-h-[400px] overflow-y-auto space-y-1">
                    @foreach($categories as $cat)
                    <button onclick="selectOrDrillDown({{ $cat['id'] }}, '{{ addslashes($cat['name']) }}', {{ !empty($cat['subCategories']) ? 'true' : 'false' }})" 
                            class="w-full flex items-center justify-between rounded border px-3 py-2 text-left hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                        <span>{{ $cat['name'] }}</span>
                        @if(!empty($cat['subCategories']))
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Rakip Modu -->
            <div id="competitor-mode-panel" class="hidden">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Trendyol Ürün Linki:
                    </label>
                    <textarea id="competitor-url" rows="2"
                              placeholder="https://www.trendyol.com/xxx/urun-adi-p-123456" 
                              class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"></textarea>
                    <button onclick="analyzeCompetitor()" class="mt-2 w-full secondary-button">
                        🔍 Kategori Bilgilerini Çek
                    </button>
                </div>

                <div id="competitor-result" class="hidden rounded border p-3 dark:border-gray-700">
                    <!-- JavaScript ile doldurulacak -->
                </div>
            </div>
        </div>

        <!-- Orta: Seçilen Kategori ve Özellikleri -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-base font-semibold text-gray-700 dark:text-gray-300">2. Kategori Özellikleri</h3>
            
            <div id="selected-category-info" class="hidden mb-4">
                <div class="rounded bg-blue-50 p-3 dark:bg-blue-900/30">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-blue-700 dark:text-blue-300" id="selected-category-name">-</span>
                        <button onclick="clearCategory()" class="text-xs text-red-600 hover:underline">Değiştir</button>
                    </div>
                    <p class="text-xs text-blue-600 dark:text-blue-400">
                        Kategori ID: <span id="selected-category-id">-</span>
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400">
                        Komisyon: <span id="selected-category-commission">~%18</span>
                    </p>
                </div>
            </div>

            <div id="attributes-loading" class="hidden py-8 text-center text-gray-500">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Özellikler yükleniyor...
            </div>

            <div id="attributes-container">
                <p class="text-gray-500 dark:text-gray-400 text-sm">Sol taraftan bir kategori seçin.</p>
            </div>

            <!-- Özellik Filtresi -->
            <div id="attribute-filter" class="hidden mt-4 pt-4 border-t dark:border-gray-700">
                <div class="flex gap-2 mb-2">
                    <button onclick="filterAttributes('all')" class="px-3 py-1 rounded text-sm bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">Tümü</button>
                    <button onclick="filterAttributes('required')" class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Zorunlu</button>
                    <button onclick="filterAttributes('optional')" class="px-3 py-1 rounded text-sm bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Opsiyonel</button>
                </div>
            </div>
        </div>

        <!-- Sağ: Özet ve Kopyalama -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="mb-4 text-base font-semibold text-gray-700 dark:text-gray-300">3. Özet ve İşlemler</h3>
            
            <div id="summary-panel">
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Kategori ID:</span>
                        <span id="summary-category-id" class="font-mono font-medium">-</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Zorunlu Özellik:</span>
                        <span id="summary-required" class="font-medium text-red-600">0 adet</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Toplam Özellik:</span>
                        <span id="summary-total" class="font-medium">0 adet</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Tahmini Komisyon:</span>
                        <span id="summary-commission" class="font-medium text-orange-600">-</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <button onclick="generateJSON()" class="w-full primary-button">
                        📋 API JSON Oluştur
                    </button>
                    <button onclick="copyAttributeList()" class="w-full secondary-button">
                        📄 Özellik Listesini Kopyala
                    </button>
                    <a href="{{ route('admin.marketplace.trendyol.commission-calculator') }}" class="block w-full text-center secondary-button">
                        💰 Komisyon Hesapla →
                    </a>
                </div>
            </div>

            <!-- JSON Modal -->
            <div id="json-output" class="hidden mt-4">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">API JSON:</h4>
                <pre id="json-content" class="bg-gray-900 text-green-400 p-3 rounded text-xs overflow-x-auto max-h-[200px]"></pre>
                <button onclick="copyJSON()" class="mt-2 w-full text-sm text-blue-600 hover:underline">
                    Kopyala
                </button>
            </div>

            <!-- İpuçları -->
            <div class="mt-4 pt-4 border-t dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">💡 İpuçları</h4>
                <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1 list-disc list-inside">
                    <li>En spesifik alt kategoriyi seçin</li>
                    <li>Zorunlu özellikleri mutlaka doldurun</li>
                    <li>Renk/Beden için Trendyol'un değerlerini kullanın</li>
                    <li>Görsel URL'leri HTTPS olmalı</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Popüler Kategoriler -->
    <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">🔥 Popüler Kategoriler</h3>
        
        <div class="grid gap-2 md:grid-cols-4 lg:grid-cols-6">
            @php
            $popularCategories = [
                ['id' => 1081, 'name' => 'Kadın Giyim', 'icon' => '👗'],
                ['id' => 1082, 'name' => 'Erkek Giyim', 'icon' => '👔'],
                ['id' => 2365, 'name' => 'Kozmetik', 'icon' => '💄'],
                ['id' => 3015, 'name' => 'Elektronik', 'icon' => '📱'],
                ['id' => 4521, 'name' => 'Ev & Yaşam', 'icon' => '🏠'],
                ['id' => 1245, 'name' => 'Ayakkabı', 'icon' => '👟'],
                ['id' => 3698, 'name' => 'Sağlık', 'icon' => '💊'],
                ['id' => 2847, 'name' => 'Spor', 'icon' => '⚽'],
                ['id' => 1956, 'name' => 'Anne & Bebek', 'icon' => '👶'],
                ['id' => 4102, 'name' => 'Kitap', 'icon' => '📚'],
                ['id' => 3754, 'name' => 'Oyuncak', 'icon' => '🧸'],
                ['id' => 2103, 'name' => 'Takı', 'icon' => '💍'],
            ];
            @endphp
            @foreach($popularCategories as $cat)
            <button onclick="selectCategory({{ $cat['id'] }}, '{{ $cat['name'] }}')" 
                    class="rounded border p-3 text-center hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                <span class="text-2xl">{{ $cat['icon'] }}</span>
                <p class="text-sm mt-1">{{ $cat['name'] }}</p>
            </button>
            @endforeach
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            let selectedCategoryId = null;
            let categoryAttributes = [];
            let currentPath = [];

            // Arama modu değiştir
            window.setSearchMode = function(mode) {
                document.getElementById('search-mode-panel').classList.toggle('hidden', mode !== 'search');
                document.getElementById('browse-mode-panel').classList.toggle('hidden', mode !== 'browse');
                document.getElementById('competitor-mode-panel').classList.toggle('hidden', mode !== 'competitor');

                // Buton stillerini güncelle
                document.getElementById('mode-search').classList.toggle('border-blue-500', mode === 'search');
                document.getElementById('mode-search').classList.toggle('bg-blue-50', mode === 'search');
                document.getElementById('mode-browse').classList.toggle('border-blue-500', mode === 'browse');
                document.getElementById('mode-browse').classList.toggle('bg-blue-50', mode === 'browse');
                document.getElementById('mode-competitor').classList.toggle('border-blue-500', mode === 'competitor');
                document.getElementById('mode-competitor').classList.toggle('bg-blue-50', mode === 'competitor');
            }

            // Ürün adıyla kategori öner
            window.searchByProductName = async function() {
                const productName = document.getElementById('product-name-search').value.trim();
                if (productName.length < 3) {
                    alert('Lütfen en az 3 karakter girin.');
                    return;
                }

                // Basit keyword matching (gerçek AI yerine)
                const keywords = {
                    'gebelik': [{ id: 364877, name: 'Gebelik Testleri', commission: 18 }],
                    'test': [{ id: 364877, name: 'Tıbbi Testler', commission: 18 }],
                    'tişört': [{ id: 1081, name: 'Kadın Tişört', commission: 20 }, { id: 1082, name: 'Erkek Tişört', commission: 20 }],
                    'telefon': [{ id: 3015, name: 'Cep Telefonları', commission: 12 }],
                    'ayakkabı': [{ id: 1245, name: 'Kadın Ayakkabı', commission: 18 }, { id: 1246, name: 'Erkek Ayakkabı', commission: 18 }],
                    'parfüm': [{ id: 2365, name: 'Kadın Parfüm', commission: 20 }],
                    'saat': [{ id: 2103, name: 'Kol Saatleri', commission: 22 }],
                };

                let suggestions = [];
                const searchLower = productName.toLowerCase();
                
                for (const [keyword, cats] of Object.entries(keywords)) {
                    if (searchLower.includes(keyword)) {
                        suggestions = suggestions.concat(cats);
                    }
                }

                // Ayrıca API'den de ara
                try {
                    const response = await fetch(`/admin/marketplace/trendyol/api/search-categories?q=${encodeURIComponent(productName)}`);
                    const data = await response.json();
                    if (data.categories) {
                        data.categories.slice(0, 5).forEach(cat => {
                            suggestions.push({ id: cat.id, name: cat.name, commission: 18 });
                        });
                    }
                } catch (e) {}

                // Sonuçları göster
                const container = document.getElementById('suggestion-list');
                if (suggestions.length > 0) {
                    container.innerHTML = suggestions.slice(0, 5).map(s => `
                        <button onclick="selectCategory(${s.id}, '${s.name.replace(/'/g, "\\'")}')" 
                                class="w-full flex items-center justify-between rounded border p-2 text-left hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-blue-900/30">
                            <span class="text-sm">${s.name}</span>
                            <span class="text-xs text-gray-500">~%${s.commission}</span>
                        </button>
                    `).join('');
                    document.getElementById('ai-suggestions').classList.remove('hidden');
                } else {
                    container.innerHTML = '<p class="text-sm text-gray-500">Öneri bulunamadı. Kategori adı ile aramayı deneyin.</p>';
                    document.getElementById('ai-suggestions').classList.remove('hidden');
                }
            }

            // Kategori arama
            let searchTimeout;
            document.getElementById('category-search').addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const keyword = e.target.value.trim();
                
                if (keyword.length < 2) {
                    document.getElementById('search-results').innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(async () => {
                    const response = await fetch(`/admin/marketplace/trendyol/api/search-categories?q=${encodeURIComponent(keyword)}`);
                    const data = await response.json();
                    
                    let html = '';
                    if (data.categories && data.categories.length > 0) {
                        data.categories.slice(0, 15).forEach(cat => {
                            html += `<button onclick="selectCategory(${cat.id}, '${cat.name.replace(/'/g, "\\'")}')" 
                                         class="w-full text-left px-3 py-2 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded text-sm border-b dark:border-gray-700">
                                        ${cat.name}
                                     </button>`;
                        });
                    } else {
                        html = '<p class="text-sm text-gray-500 p-2">Sonuç bulunamadı.</p>';
                    }
                    document.getElementById('search-results').innerHTML = html;
                }, 300);
            });

            // Rakip URL analizi
            window.analyzeCompetitor = function() {
                const url = document.getElementById('competitor-url').value.trim();
                if (!url.includes('trendyol.com')) {
                    alert('Lütfen geçerli bir Trendyol ürün linki girin.');
                    return;
                }

                // URL'den product ID çıkar
                const match = url.match(/p-(\d+)/);
                if (match) {
                    document.getElementById('competitor-result').innerHTML = `
                        <p class="text-sm text-green-700 dark:text-green-300 mb-2">✅ Ürün bulundu!</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">Product ID: ${match[1]}</p>
                        <p class="text-xs text-gray-500 mt-2">Kategori bilgisi için ürün detay API'si kullanılmalıdır.</p>
                        <button onclick="fetchProductCategory('${match[1]}')" class="mt-2 w-full secondary-button text-sm">
                            Kategori Bilgisi Al
                        </button>
                    `;
                    document.getElementById('competitor-result').classList.remove('hidden');
                } else {
                    alert('Ürün ID bulunamadı. URL formatını kontrol edin.');
                }
            }

            // Kategori seç
            window.selectCategory = async function(id, name) {
                selectedCategoryId = id;
                
                document.getElementById('selected-category-info').classList.remove('hidden');
                document.getElementById('selected-category-name').textContent = name;
                document.getElementById('selected-category-id').textContent = id;
                
                document.getElementById('attributes-loading').classList.remove('hidden');
                document.getElementById('attributes-container').innerHTML = '';

                try {
                    const response = await fetch(`/admin/marketplace/trendyol/api/category-attributes/${id}`);
                    const data = await response.json();
                    categoryAttributes = data.categoryAttributes || [];
                    
                    renderAttributes(categoryAttributes);
                    updateSummary();
                    document.getElementById('attribute-filter').classList.remove('hidden');
                } catch (error) {
                    document.getElementById('attributes-container').innerHTML = 
                        '<p class="text-red-500">Özellikler yüklenemedi.</p>';
                }
                
                document.getElementById('attributes-loading').classList.add('hidden');
            }

            window.clearCategory = function() {
                selectedCategoryId = null;
                categoryAttributes = [];
                document.getElementById('selected-category-info').classList.add('hidden');
                document.getElementById('attributes-container').innerHTML = '<p class="text-gray-500 text-sm">Sol taraftan bir kategori seçin.</p>';
                document.getElementById('attribute-filter').classList.add('hidden');
                updateSummary();
            }

            function renderAttributes(attributes) {
                if (!attributes || attributes.length === 0) {
                    document.getElementById('attributes-container').innerHTML = 
                        '<p class="text-gray-500">Bu kategori için özellik bulunmuyor.</p>';
                    return;
                }

                let required = attributes.filter(a => a.required);
                let optional = attributes.filter(a => !a.required);

                let html = `<div class="text-xs text-gray-500 mb-3">
                    <span class="text-red-600 font-medium">${required.length} zorunlu</span> | 
                    ${optional.length} opsiyonel özellik
                </div>`;
                
                if (required.length > 0) {
                    html += `<div class="mb-4" data-type="required">
                        <h4 class="text-sm font-semibold text-red-600 dark:text-red-400 mb-2 flex items-center gap-1">
                            <span>⚠️</span> Zorunlu Özellikler
                        </h4>
                        <div class="space-y-2">`;
                    
                    required.forEach(attr => {
                        html += renderAttributeItem(attr, true);
                    });
                    
                    html += `</div></div>`;
                }

                if (optional.length > 0) {
                    html += `<div data-type="optional">
                        <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">
                            Opsiyonel Özellikler
                        </h4>
                        <div class="space-y-2 max-h-[200px] overflow-y-auto">`;
                    
                    optional.forEach(attr => {
                        html += renderAttributeItem(attr, false);
                    });
                    
                    html += `</div></div>`;
                }

                document.getElementById('attributes-container').innerHTML = html;
            }

            function renderAttributeItem(attr, isRequired) {
                const attrName = attr.attribute?.name || attr.name || '-';
                const attrId = attr.attribute?.id || attr.id || 0;
                const values = attr.attributeValues || [];
                
                let valuesHtml = '';
                if (values.length > 0) {
                    valuesHtml = `<select class="attr-value w-full mt-1 rounded border px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-800" data-attr-id="${attrId}">
                        <option value="">Seçiniz...</option>
                        ${values.slice(0, 100).map(v => `<option value="${v.id}">${v.name}</option>`).join('')}
                    </select>`;
                } else {
                    valuesHtml = `<input type="text" class="attr-value w-full mt-1 rounded border px-2 py-1 text-xs dark:border-gray-700 dark:bg-gray-800" 
                                        data-attr-id="${attrId}" placeholder="Değer girin...">`;
                }

                return `<div class="rounded border px-3 py-2 ${isRequired ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-700'}">
                    <div class="flex items-center justify-between">
                        <span class="text-sm ${isRequired ? 'text-red-700 dark:text-red-300 font-medium' : 'text-gray-700 dark:text-gray-300'}">
                            ${isRequired ? '* ' : ''}${attrName}
                        </span>
                        <span class="text-xs text-gray-400 font-mono">${attrId}</span>
                    </div>
                    ${valuesHtml}
                </div>`;
            }

            window.filterAttributes = function(type) {
                const container = document.getElementById('attributes-container');
                const required = container.querySelector('[data-type="required"]');
                const optional = container.querySelector('[data-type="optional"]');

                if (required) required.classList.toggle('hidden', type === 'optional');
                if (optional) optional.classList.toggle('hidden', type === 'required');
            }

            function updateSummary() {
                const required = categoryAttributes.filter(a => a.required);
                
                document.getElementById('summary-category-id').textContent = selectedCategoryId || '-';
                document.getElementById('summary-required').textContent = required.length + ' adet';
                document.getElementById('summary-total').textContent = categoryAttributes.length + ' adet';
                document.getElementById('summary-commission').textContent = selectedCategoryId ? '~%18' : '-';
            }

            window.generateJSON = function() {
                if (!selectedCategoryId) {
                    alert('Önce bir kategori seçin.');
                    return;
                }

                const attributes = [];
                document.querySelectorAll('.attr-value').forEach(el => {
                    const attrId = el.dataset.attrId;
                    const value = el.value;
                    if (value) {
                        attributes.push({
                            attributeId: parseInt(attrId),
                            attributeValueId: el.tagName === 'SELECT' ? parseInt(value) : undefined,
                            customAttributeValue: el.tagName === 'INPUT' ? value : undefined
                        });
                    }
                });

                const json = {
                    categoryId: selectedCategoryId,
                    attributes: attributes.filter(a => a.attributeValueId || a.customAttributeValue)
                };

                document.getElementById('json-content').textContent = JSON.stringify(json, null, 2);
                document.getElementById('json-output').classList.remove('hidden');
            }

            window.copyJSON = function() {
                const json = document.getElementById('json-content').textContent;
                navigator.clipboard.writeText(json).then(() => alert('JSON kopyalandı!'));
            }

            window.copyAttributeList = function() {
                if (!categoryAttributes.length) {
                    alert('Önce bir kategori seçin.');
                    return;
                }

                let text = `Kategori ID: ${selectedCategoryId}\n\n`;
                text += `ZORUNLU ÖZELLİKLER:\n`;
                categoryAttributes.filter(a => a.required).forEach(attr => {
                    text += `- ${attr.attribute?.name || attr.name} (ID: ${attr.attribute?.id || attr.id})\n`;
                });
                
                navigator.clipboard.writeText(text).then(() => alert('Özellik listesi kopyalandı!'));
            }
        </script>
    @endPushOnce
</x-admin::layouts>
