@extends('admin::layouts.master')

@section('page_title')
    Tenant Yönetimi
@endsection

@section('content-wrapper')
<div class="content full-page">
    <div class="page-header">
        <div class="page-title">
            <h1>Tenant Yönetimi (SaaS)</h1>
        </div>
        <div class="page-action">
            <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">
                <i class="icon-add"></i> Yeni Tenant Oluştur
            </a>
        </div>
    </div>

    <div class="page-content">
        <!-- İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Toplam Tenant</h5>
                        <h2 class="mb-0">{{ $stats['total'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Aktif</h5>
                        <h2 class="mb-0">{{ $stats['active'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Trial'da</h5>
                        <h2 class="mb-0">{{ $stats['trial'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title">Askıda</h5>
                        <h2 class="mb-0">{{ $stats['suspended'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtreler -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="" method="GET" class="row">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Tenant ara..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">Tüm Durumlar</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Askıda</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="plan" class="form-control">
                            <option value="">Tüm Planlar</option>
                            <option value="starter" {{ request('plan') == 'starter' ? 'selected' : '' }}>Starter</option>
                            <option value="professional" {{ request('plan') == 'professional' ? 'selected' : '' }}>Professional</option>
                            <option value="enterprise" {{ request('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Filtrele</button>
                        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">Temizle</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tenant Listesi -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tenant</th>
                            <th>Subdomain</th>
                            <th>Plan</th>
                            <th>Durum</th>
                            <th>Sahip</th>
                            <th>Kayıt Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                        <tr>
                            <td>{{ $tenant->id }}</td>
                            <td>
                                <strong>{{ $tenant->name }}</strong>
                                @if($tenant->domain)
                                    <br><small class="text-muted">{{ $tenant->domain }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ $tenant->url }}" target="_blank">
                                    {{ $tenant->subdomain }}{{ config('castmart-tenant.subdomain_suffix') }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-{{ $tenant->plan == 'enterprise' ? 'primary' : ($tenant->plan == 'professional' ? 'info' : 'secondary') }}">
                                    {{ ucfirst($tenant->plan) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $tenant->status_color }}">
                                    {{ $tenant->status_text }}
                                </span>
                                @if($tenant->isOnTrial())
                                    <br><small class="text-warning">Trial: {{ $tenant->trial_ends_at->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td>
                                @if($tenant->owner)
                                    {{ $tenant->owner->name }}<br>
                                    <small class="text-muted">{{ $tenant->owner->email }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $tenant->created_at->format('d.m.Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.tenants.show', $tenant->id) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Detay">
                                        <i class="icon-view"></i>
                                    </a>
                                    <a href="{{ route('admin.tenants.edit', $tenant->id) }}" 
                                       class="btn btn-sm btn-outline-secondary" title="Düzenle">
                                        <i class="icon-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-info" 
                                            onclick="impersonate({{ $tenant->id }})" title="Giriş Yap">
                                        <i class="icon-login"></i>
                                    </button>
                                    @if($tenant->status === 'active')
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="suspendTenant({{ $tenant->id }})" title="Askıya Al">
                                            <i class="icon-cancel"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-outline-success" 
                                                onclick="activateTenant({{ $tenant->id }})" title="Aktif Et">
                                            <i class="icon-done"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Henüz tenant bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $tenants->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function impersonate(id) {
    if (!confirm('Bu tenant olarak giriş yapmak istediğinize emin misiniz?')) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/tenants/' + id + '/impersonate';
    form.innerHTML = '<input type="hidden" name="_token" value="{{ csrf_token() }}">';
    document.body.appendChild(form);
    form.submit();
}

function suspendTenant(id) {
    const reason = prompt('Askıya alma sebebi (opsiyonel):');
    if (reason === null) return; // İptal edildi
    
    fetch('/admin/tenants/' + id + '/suspend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}

function activateTenant(id) {
    if (!confirm('Bu tenant\'ı aktif etmek istediğinize emin misiniz?')) return;
    
    fetch('/admin/tenants/' + id + '/activate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    });
}
</script>
@endsection
