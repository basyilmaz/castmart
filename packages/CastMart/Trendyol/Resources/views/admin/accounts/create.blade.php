<x-admin::layouts>
    <x-slot:title>
        Trendyol Hesabı Ekle
    </x-slot>

    <!-- Header -->
    <div class="mb-5 flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="grid gap-1.5">
            <p class="text-xl font-bold !leading-normal text-gray-800 dark:text-white">
                Yeni Trendyol Hesabı
            </p>
            <p class="!leading-normal text-gray-600 dark:text-gray-300">
                Trendyol API bilgilerinizi girin
            </p>
        </div>
    </div>

    <!-- Form -->
    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
        <form action="{{ route('admin.marketplace.trendyol.accounts.store') }}" method="POST">
            @csrf

            <x-admin::form.control-group class="mb-4">
                <x-admin::form.control-group.label class="required">
                    Hesap Adı
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="name"
                    :value="old('name')"
                    rules="required"
                    :label="'Hesap Adı'"
                    placeholder="Örn: Ana Mağaza"
                />

                <x-admin::form.control-group.error control-name="name" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-4">
                <x-admin::form.control-group.label class="required">
                    Supplier ID
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="supplier_id"
                    :value="old('supplier_id')"
                    rules="required"
                    :label="'Supplier ID'"
                    placeholder="Trendyol Supplier ID"
                />

                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Trendyol Satıcı Paneli → Entegrasyon → API Bilgileri
                </p>

                <x-admin::form.control-group.error control-name="supplier_id" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-4">
                <x-admin::form.control-group.label class="required">
                    API Key
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="api_key"
                    :value="old('api_key')"
                    rules="required"
                    :label="'API Key'"
                    placeholder="API Key"
                />

                <x-admin::form.control-group.error control-name="api_key" />
            </x-admin::form.control-group>

            <x-admin::form.control-group class="mb-4">
                <x-admin::form.control-group.label class="required">
                    API Secret
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="password"
                    name="api_secret"
                    rules="required"
                    :label="'API Secret'"
                    placeholder="API Secret"
                />

                <x-admin::form.control-group.error control-name="api_secret" />
            </x-admin::form.control-group>

            <div class="flex gap-2">
                <button type="submit" class="primary-button">
                    Hesabı Kaydet
                </button>

                <a href="{{ route('admin.marketplace.trendyol.accounts') }}" class="secondary-button">
                    İptal
                </a>
            </div>
        </form>
    </div>
</x-admin::layouts>
