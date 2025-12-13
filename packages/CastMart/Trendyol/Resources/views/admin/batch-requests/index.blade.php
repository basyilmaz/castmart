<x-admin::layouts>
    <x-slot:title>
        Batch İşlem Takibi
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <span class="text-2xl">📋</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Batch İşlem Takibi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trendyol'a gönderilen toplu işlemleri takip edin</p>
            </div>
        </div>
    </div>

    <!-- Batch ID Sorgulama -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Batch Durumu Sorgula</h3>
        
        <div class="flex gap-4">
            <div class="flex-1">
                <input type="text" id="batch-id-input" placeholder="Batch Request ID girin..." 
                       class="w-full rounded-md border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <button onclick="checkBatchStatus()" class="primary-button">
                Durumu Kontrol Et
            </button>
        </div>
    </div>

    <!-- Batch Durumu Sonucu -->
    <div id="batch-result" class="hidden mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Batch Sonucu</h3>
        
        <div id="batch-loading" class="hidden py-8 text-center text-gray-500">
            <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Batch durumu kontrol ediliyor...
        </div>

        <div id="batch-content" class="space-y-4"></div>
    </div>

    <!-- Bilgi Kartları -->
    <div class="grid gap-4 md:grid-cols-3 mb-4">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Batch Nedir?</p>
                    <p class="font-medium text-gray-800 dark:text-white">Toplu İşlem</p>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Trendyol'a ürün gönderdiğinizde, işlemler hemen gerçekleşmez. 
                Bir Batch ID alırsınız ve bu ID ile işlem durumunu takip edebilirsiniz.
            </p>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Başarılı İşlem</p>
                    <p class="font-medium text-gray-800 dark:text-white">Tamamlandı</p>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                İşlem başarılıysa, ürünler Trendyol'a yüklenmiştir. 
                "Ürünler" sayfasından kontrol edebilirsiniz.
            </p>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Başarısız İşlem</p>
                    <p class="font-medium text-gray-800 dark:text-white">Hata</p>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                İşlem başarısızsa, hata detayları gösterilir. 
                Eksik/hatalı bilgileri düzeltip tekrar deneyin.
            </p>
        </div>
    </div>

    <!-- Yaygın Hata Kodları -->
    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Yaygın Hata Kodları ve Çözümleri</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Hata Kodu</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Açıklama</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-300">Çözüm</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    <tr>
                        <td class="px-4 py-2 font-mono text-red-600">INVALID_CATEGORY</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Geçersiz kategori ID</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Kategori Wizard'ı kullanarak doğru kategori seçin</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono text-red-600">INVALID_BRAND</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Geçersiz marka ID</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Trendyol'da kayıtlı bir marka seçin</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono text-red-600">MISSING_REQUIRED_ATTRIBUTE</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Zorunlu özellik eksik</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Kategori Wizard'dan zorunlu özellikleri kontrol edin</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono text-red-600">DUPLICATE_BARCODE</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Barkod zaten mevcut</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Farklı bir barkod kullanın veya mevcut ürünü güncelleyin</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono text-red-600">INVALID_IMAGE_URL</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Görsel URL'si geçersiz</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">HTTPS ile başlayan geçerli görsel URL'si kullanın</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2 font-mono text-red-600">INVALID_PRICE</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Fiyat hatası</td>
                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">Liste fiyatı satış fiyatından düşük olamaz</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            window.checkBatchStatus = async function() {
                const batchId = document.getElementById('batch-id-input').value.trim();
                
                if (!batchId) {
                    alert('Lütfen Batch ID girin.');
                    return;
                }

                document.getElementById('batch-result').classList.remove('hidden');
                document.getElementById('batch-loading').classList.remove('hidden');
                document.getElementById('batch-content').innerHTML = '';

                try {
                    const response = await fetch(`/admin/marketplace/trendyol/api/batch-status/${batchId}`);
                    const data = await response.json();
                    
                    renderBatchResult(data);
                } catch (error) {
                    document.getElementById('batch-content').innerHTML = 
                        '<p class="text-red-500">Batch durumu alınamadı: ' + error.message + '</p>';
                }
                
                document.getElementById('batch-loading').classList.add('hidden');
            }

            function renderBatchResult(data) {
                let html = '';

                if (data.error) {
                    html = `<div class="rounded bg-red-50 border border-red-200 p-4 dark:bg-red-900/30 dark:border-red-800">
                        <p class="text-red-700 dark:text-red-300">${data.error}</p>
                    </div>`;
                } else {
                    const status = data.status || 'UNKNOWN';
                    const statusColor = status === 'COMPLETED' ? 'green' : (status === 'FAILED' ? 'red' : 'yellow');
                    
                    html = `
                        <div class="grid gap-4 md:grid-cols-3 mb-4">
                            <div class="rounded bg-gray-50 p-3 dark:bg-gray-800">
                                <p class="text-xs text-gray-500">Batch ID</p>
                                <p class="font-mono text-sm">${data.batchRequestId || '-'}</p>
                            </div>
                            <div class="rounded bg-${statusColor}-50 p-3 dark:bg-${statusColor}-900/30">
                                <p class="text-xs text-gray-500">Durum</p>
                                <p class="font-medium text-${statusColor}-700 dark:text-${statusColor}-300">${status}</p>
                            </div>
                            <div class="rounded bg-gray-50 p-3 dark:bg-gray-800">
                                <p class="text-xs text-gray-500">Toplam Ürün</p>
                                <p class="font-medium">${data.itemCount || 0}</p>
                            </div>
                        </div>
                    `;

                    if (data.items && data.items.length > 0) {
                        html += `<div class="mt-4">
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Ürün Detayları</h4>
                            <div class="max-h-[300px] overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left">Barkod</th>
                                            <th class="px-3 py-2 text-left">Durum</th>
                                            <th class="px-3 py-2 text-left">Hata</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y dark:divide-gray-700">`;
                        
                        data.items.forEach(item => {
                            const itemStatus = item.status || 'UNKNOWN';
                            const itemStatusColor = itemStatus === 'SUCCESS' ? 'green' : 'red';
                            const errors = item.failureReasons || [];
                            
                            html += `<tr>
                                <td class="px-3 py-2 font-mono">${item.barcode || '-'}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center rounded-full bg-${itemStatusColor}-100 px-2 py-0.5 text-xs font-medium text-${itemStatusColor}-700">
                                        ${itemStatus}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-xs text-red-600">${errors.join(', ') || '-'}</td>
                            </tr>`;
                        });
                        
                        html += `</tbody></table></div></div>`;
                    }
                }

                document.getElementById('batch-content').innerHTML = html;
            }
        </script>
    @endPushOnce
</x-admin::layouts>
