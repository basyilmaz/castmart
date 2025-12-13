<x-admin::layouts>
    <x-slot:title>
        E-Fatura Yönetimi
    </x-slot>

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <span class="text-2xl">🧾</span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">E-Fatura Yönetimi</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Trendyol siparişleri için e-Fatura oluşturun</p>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="syncInvoices()" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;">
                🔄 Faturaları Senkronize Et
            </button>
        </div>
    </div>

    <!-- Entegrasyon Durumu -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Fatura Entegrasyon Durumu</h3>
        
        <div class="grid gap-4 md:grid-cols-4">
            <!-- BizimHesap -->
            <div class="rounded border-2 p-4 dark:border-gray-700 {{ $bizimhesapConnected ?? false ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : '' }}">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <img src="https://www.bizimhesap.com/images/logo.svg" alt="BizimHesap" class="h-6" onerror="this.style.display='none'">
                        <h4 class="font-medium text-gray-800 dark:text-white">BizimHesap</h4>
                    </div>
                    @if($bizimhesapConnected ?? false)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-300">
                        ✓ Bağlı
                    </span>
                    @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        Bağlı Değil
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mb-3">Online muhasebe ve e-fatura çözümü.</p>
                @if($bizimhesapConnected ?? false)
                    <button onclick="testBizimhesapConnection()" class="w-full secondary-button text-sm">Bağlantıyı Test Et</button>
                @else
                    <button onclick="showBizimhesapModal()" class="w-full primary-button text-sm">Bağlan</button>
                @endif
            </div>

            <!-- Paraşüt -->
            <div class="rounded border p-4 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-800 dark:text-white">Paraşüt</h4>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        Bağlı Değil
                    </span>
                </div>
                <p class="text-xs text-gray-500 mb-3">Paraşüt hesabınızı bağlayarak otomatik fatura oluşturun.</p>
                <button class="w-full secondary-button text-sm" disabled>Yakında</button>
            </div>

            <!-- Logo -->
            <div class="rounded border p-4 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-800 dark:text-white">Logo</h4>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        Bağlı Değil
                    </span>
                </div>
                <p class="text-xs text-gray-500 mb-3">Logo Tiger/Go ile entegre çalışın.</p>
                <button class="w-full secondary-button text-sm" disabled>Yakında</button>
            </div>

            <!-- Kolayları -->
            <div class="rounded border p-4 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-800 dark:text-white">Kolayları</h4>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                        Bağlı Değil
                    </span>
                </div>
                <p class="text-xs text-gray-500 mb-3">Kolayları entegrasyonu ile e-Fatura gönderin.</p>
                <button class="w-full secondary-button text-sm" disabled>Yakında</button>
            </div>
        </div>
    </div>

    @if($bizimhesapConnected ?? false)
    <!-- Hızlı Fatura Oluştur -->
    <div class="mb-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">⚡ Hızlı Fatura Oluştur</h3>
        
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trendyol Sipariş No</label>
                <input type="text" id="quick-order-number" placeholder="Örn: 2024121100123456" 
                       class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
            </div>
            <div class="flex items-end">
                <button onclick="createInvoiceFromOrder()" class="primary-button w-full">
                    Fatura Oluştur
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Fatura Ayarları ve Firma Bilgileri -->
    <div class="grid gap-4 lg:grid-cols-2 mb-4">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Fatura Ayarları</h3>
            
            <form id="invoice-settings-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Varsayılan KDV Oranı</label>
                    <select name="default_vat_rate" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        <option value="0">%0 (İstisna)</option>
                        <option value="1">%1</option>
                        <option value="10">%10</option>
                        <option value="20" selected>%20</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fatura Seri</label>
                    <input type="text" name="invoice_series" value="TRD" 
                           class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                           placeholder="Fatura seri numarası">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="auto_invoice" id="auto-invoice" class="rounded border-gray-300">
                    <label for="auto-invoice" class="text-sm text-gray-700 dark:text-gray-300">
                        Sipariş tamamlandığında otomatik fatura oluştur
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="use_trendyol_vat" id="use-trendyol-vat" class="rounded border-gray-300">
                    <label for="use-trendyol-vat" class="text-sm text-gray-700 dark:text-gray-300">
                        Trendyol'dan gelen KDV oranını kullan
                    </label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="auto_determine_invoice_type" id="auto-invoice-type" checked class="rounded border-gray-300">
                    <label for="auto-invoice-type" class="text-sm text-gray-700 dark:text-gray-300">
                        Fatura tipini otomatik belirle (Kurumsal: e-Fatura, Bireysel: e-Arşiv)
                    </label>
                </div>

                <button type="submit" class="primary-button w-full">Ayarları Kaydet</button>
            </form>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-4">Firma Bilgileri</h3>
            
            <form id="company-info-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Firma Adı</label>
                    <input type="text" name="company_name" value="{{ $companyInfo['name'] ?? '' }}" 
                           class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vergi Dairesi</label>
                        <input type="text" name="tax_office" value="{{ $companyInfo['tax_office'] ?? '' }}" 
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vergi No</label>
                        <input type="text" name="tax_number" value="{{ $companyInfo['tax_number'] ?? '' }}" 
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adres</label>
                    <textarea name="address" rows="2" class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $companyInfo['address'] ?? '' }}</textarea>
                </div>

                <button type="submit" class="secondary-button w-full">Bilgileri Güncelle</button>
            </form>
        </div>
    </div>

    <!-- Fatura Listesi -->
    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">Son Faturalar</h3>
            <div class="flex gap-2">
                <select id="invoice-filter" class="rounded border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    <option value="all">Tümü</option>
                    <option value="e_fatura">e-Fatura</option>
                    <option value="e_arsiv">e-Arşiv</option>
                </select>
                <button onclick="loadInvoices()" class="secondary-button text-sm">Yenile</button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 border-b dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Fatura No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Sipariş No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-300">Müşteri</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Tip</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600 dark:text-gray-300">Tutar</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Durum</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">Tarih</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-600 dark:text-gray-300">İşlem</th>
                    </tr>
                </thead>
                <tbody id="invoices-table-body" class="divide-y dark:divide-gray-700">
                    @forelse($invoices ?? [] as $invoice)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-mono text-sm">{{ $invoice['invoiceNumber'] ?? $invoice['no'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $invoice['orderNumber'] ?? $invoice['order'] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $invoice['customerName'] ?? $invoice['customer'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ ($invoice['invoiceType'] ?? '') === 'E_FATURA' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ($invoice['invoiceType'] ?? $invoice['type'] ?? 'e-Arşiv') === 'E_FATURA' ? 'e-Fatura' : 'e-Arşiv' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($invoice['totalAmount'] ?? $invoice['amount'] ?? 0, 2, ',', '.') }} ₺</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ ($invoice['status'] ?? 'APPROVED') === 'APPROVED' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ($invoice['status'] ?? 'Onaylandı') === 'APPROVED' ? 'Onaylandı' : ($invoice['status'] ?? 'Bekliyor') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $invoice['date'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="viewInvoice('{{ $invoice['id'] ?? '' }}')" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600" title="Görüntüle">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <button onclick="downloadInvoice('{{ $invoice['id'] ?? '' }}')" class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600" title="PDF İndir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            Henüz fatura bulunmuyor.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bilgi Kartları -->
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="rounded border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30">
            <h4 class="font-medium text-blue-800 dark:text-blue-300 mb-2">💡 e-Fatura vs e-Arşiv</h4>
            <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
                <li><strong>e-Fatura:</strong> Kurumsal müşteriler (vergi mükellefi) için</li>
                <li><strong>e-Arşiv:</strong> Bireysel müşteriler için</li>
                <li>Sistem otomatik olarak vergi numarasına göre tip belirler</li>
            </ul>
        </div>

        <div class="rounded border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/30">
            <h4 class="font-medium text-green-800 dark:text-green-300 mb-2">✅ BizimHesap Avantajları</h4>
            <ul class="text-sm text-green-700 dark:text-green-400 space-y-1 list-disc list-inside">
                <li>Otomatik e-Fatura/e-Arşiv gönderimi</li>
                <li>Muhasebe kaydı otomatik oluşturma</li>
                <li>Stok ve cari hesap takibi</li>
                <li>Trendyol siparişleri ile senkronizasyon</li>
            </ul>
        </div>
    </div>

    <!-- BizimHesap Bağlantı Modalı -->
    <div id="bizimhesap-modal" class="fixed inset-0 z-[9999] hidden overflow-y-auto" style="display: none;">
        <div class="flex min-h-screen items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeBizimhesapModal()"></div>
            
            <!-- Modal Content -->
            <div class="relative w-full max-w-md transform rounded-lg bg-white shadow-2xl transition-all dark:bg-gray-900">
                <div class="flex items-center justify-between border-b p-4 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">BizimHesap Bağlantısı</h3>
                    <button onclick="closeBizimhesapModal()" class="rounded p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <form id="bizimhesap-form" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                        <input type="text" name="api_key" required
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                               placeholder="BizimHesap API Key">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Secret</label>
                        <input type="password" name="api_secret" required
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                               placeholder="BizimHesap API Secret">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Şirket ID</label>
                        <input type="text" name="company_id" required
                               class="w-full rounded border px-3 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                               placeholder="BizimHesap Şirket ID">
                    </div>
                    <p class="text-xs text-gray-500">
                        API bilgilerini almak için <a href="mailto:destek@bizimhesap.com" class="text-blue-600 hover:underline">destek@bizimhesap.com</a> adresine mail atın.
                    </p>
                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="closeBizimhesapModal()" class="flex-1 secondary-button">İptal</button>
                        <button type="submit" class="flex-1 primary-button">Bağlan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @pushOnce('scripts')
        <script type="module">
            // BizimHesap Modal
            window.showBizimhesapModal = function() {
                const modal = document.getElementById('bizimhesap-modal');
                modal.classList.remove('hidden');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }

            window.closeBizimhesapModal = function() {
                const modal = document.getElementById('bizimhesap-modal');
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            // BizimHesap bağlantı formu
            document.getElementById('bizimhesap-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.invoices.connect-bizimhesap") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            api_key: formData.get('api_key'),
                            api_secret: formData.get('api_secret'),
                            company_id: formData.get('company_id')
                        })
                    });
                    const data = await response.json();
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    alert('Bağlantı hatası: ' + error.message);
                }
            });

            window.testBizimhesapConnection = async function() {
                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.invoices.test-bizimhesap") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    alert(data.message);
                } catch (error) {
                    alert('Test hatası: ' + error.message);
                }
            }

            window.createInvoiceFromOrder = async function() {
                const orderNumber = document.getElementById('quick-order-number').value.trim();
                if (!orderNumber) {
                    alert('Lütfen sipariş numarası girin.');
                    return;
                }

                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.invoices.create-from-order") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ order_number: orderNumber })
                    });
                    const data = await response.json();
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    }
                } catch (error) {
                    alert('Fatura oluşturma hatası: ' + error.message);
                }
            }

            window.syncInvoices = function() {
                location.reload();
            }

            window.loadInvoices = function() {
                location.reload();
            }

            window.viewInvoice = function(invoiceId) {
                alert('Fatura detayı: ' + invoiceId);
            }

            window.downloadInvoice = async function(invoiceId) {
                window.open(`/admin/marketplace/trendyol/invoices/${invoiceId}/download`, '_blank');
            }

            // Ayarlar formu
            document.getElementById('invoice-settings-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                try {
                    const response = await fetch('{{ route("admin.marketplace.trendyol.invoices.settings") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    });
                    const data = await response.json();
                    alert(data.message);
                } catch (error) {
                    alert('Kaydetme hatası');
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
