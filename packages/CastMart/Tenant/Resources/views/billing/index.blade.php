@extends('admin::layouts.master')

@section('page_title')
    Abonelik ve Faturalandırma
@endsection

@section('content-wrapper')
<div class="content full-page">
    <div class="page-header">
        <div class="page-title">
            <h1>Abonelik ve Faturalandırma</h1>
        </div>
    </div>

    <div class="page-content">
        <!-- Mevcut Plan -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Mevcut Abonelik</h4>
                        @if($subscription)
                            <span class="badge badge-{{ $subscription->status === 'active' ? 'success' : 'warning' }}">
                                {{ $subscription->status_text }}
                            </span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($subscription)
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>{{ $plans[$tenant->plan]['name'] ?? ucfirst($tenant->plan) }}</h5>
                                    <p class="text-muted mb-2">
                                        {{ number_format($subscription->price, 2) }} ₺ / 
                                        {{ $subscription->billing_cycle === 'yearly' ? 'Yıllık' : 'Aylık' }}
                                    </p>
                                    <ul class="list-unstyled">
                                        @foreach($plans[$tenant->plan]['features'] ?? [] as $feature => $value)
                                            <li>
                                                <i class="icon-done text-success"></i>
                                                {{ $this->getFeatureText($feature, $value) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="subscription-info">
                                        <p>
                                            <strong>Başlangıç:</strong> 
                                            {{ $subscription->starts_at->format('d.m.Y') }}
                                        </p>
                                        <p>
                                            <strong>Bitiş:</strong> 
                                            {{ $subscription->ends_at->format('d.m.Y') }}
                                        </p>
                                        <p>
                                            <strong>Kalan:</strong> 
                                            <span class="badge badge-info">{{ $subscription->remaining_days }} gün</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('tenant.billing.plans') }}" class="btn btn-primary">
                                    <i class="icon-arrow-up"></i> Plan Değiştir
                                </a>
                                <button class="btn btn-outline-danger" onclick="cancelSubscription()">
                                    <i class="icon-cancel"></i> Abonelik İptal
                                </button>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <h5>Aktif aboneliğiniz bulunmuyor</h5>
                                <p class="text-muted">
                                    @if($tenant->isOnTrial())
                                        Trial süreniz {{ $tenant->trial_ends_at->diffForHumans() }} sona erecek.
                                    @else
                                        Hizmetlere erişmek için bir plan seçin.
                                    @endif
                                </p>
                                <a href="{{ route('tenant.billing.plans') }}" class="btn btn-primary btn-lg">
                                    Plan Seçin
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Kullanım Özeti -->
                <div class="card">
                    <div class="card-header">
                        <h4>Kullanım Özeti</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $limits = $plans[$tenant->plan]['features'] ?? [];
                        @endphp
                        
                        <div class="usage-item mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Ürünler</span>
                                <span>0 / {{ $limits['max_products'] ?? '∞' }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div class="usage-item mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Aylık Siparişler</span>
                                <span>0 / {{ $limits['max_orders_per_month'] ?? '∞' }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                        </div>
                        
                        <div class="usage-item">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Kullanıcılar</span>
                                <span>1 / {{ $limits['max_users'] ?? '∞' }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: 10%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Faturalar -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Fatura Geçmişi</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fatura No</th>
                            <th>Tarih</th>
                            <th>Plan</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                        <tr>
                            <td>INV-{{ date('Y') }}-{{ str_pad($inv->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ $inv->starts_at->format('d.m.Y') }}</td>
                            <td>{{ $plans[$inv->plan]['name'] ?? ucfirst($inv->plan) }}</td>
                            <td>{{ number_format($inv->price, 2) }} ₺</td>
                            <td>
                                <span class="badge badge-success">Ödendi</span>
                            </td>
                            <td>
                                <a href="{{ route('tenant.billing.invoice', $inv->id) }}" 
                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="icon-view"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Henüz fatura bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function cancelSubscription() {
    if (!confirm('Aboneliğinizi iptal etmek istediğinize emin misiniz? \nMevcut dönem sonuna kadar hizmet devam edecektir.')) {
        return;
    }
    
    const reason = prompt('İptal sebebi (opsiyonel):');
    
    fetch('{{ route("tenant.billing.cancel") }}', {
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
</script>
@endsection
