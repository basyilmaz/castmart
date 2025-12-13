@extends('admin::layouts.master')

@section('page_title')
    Kargo Yönetimi
@endsection

@section('content-wrapper')
<div class="content full-page">
    <div class="page-header">
        <div class="page-title">
            <h1>Kargo Yönetimi</h1>
        </div>
        <div class="page-action">
            <button class="btn btn-primary" onclick="refreshTracking()">
                <i class="icon-refresh"></i> Takip Güncelle
            </button>
        </div>
    </div>

    <!-- İstatistikler -->
    <div class="page-content">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Toplam Gönderi</h5>
                        <h2>{{ $stats['total'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Bekleyen</h5>
                        <h2 class="text-warning">{{ $stats['pending'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Yolda</h5>
                        <h2 class="text-info">{{ $stats['in_transit'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Teslim Edildi</h5>
                        <h2 class="text-success">{{ $stats['delivered'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kargo Firmaları -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Aktif Kargo Firmaları</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($carriers as $carrier)
                    <div class="col-md-4">
                        <div class="carrier-card p-3 border rounded mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $carrier->getName() }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $carrier->getCode() }}</small>
                                </div>
                                <span class="badge badge-success">Aktif</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Gönderi Listesi -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>Son Gönderiler</h4>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Takip No</th>
                            <th>Sipariş</th>
                            <th>Alıcı</th>
                            <th>Kargo</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                        <tr>
                            <td>
                                <a href="{{ route('admin.shipping.show', $shipment->id) }}">
                                    {{ $shipment->tracking_number }}
                                </a>
                            </td>
                            <td>
                                @if($shipment->order)
                                    <a href="{{ route('admin.sales.orders.view', $shipment->order_id) }}">
                                        #{{ $shipment->order->increment_id }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                {{ $shipment->receiver_name }}<br>
                                <small class="text-muted">{{ $shipment->receiver_city }}</small>
                            </td>
                            <td>{{ $shipment->carrier_name }}</td>
                            <td>
                                <span class="badge badge-{{ $shipment->status_color }}">
                                    {{ $shipment->status_text }}
                                </span>
                            </td>
                            <td>{{ $shipment->created_at->format('d.m.Y H:i') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.shipping.label.view', $shipment->id) }}" 
                                       class="btn btn-sm btn-outline-primary" target="_blank" title="Etiket">
                                        <i class="icon-print"></i>
                                    </a>
                                    <a href="{{ $shipment->tracking_url }}" 
                                       class="btn btn-sm btn-outline-info" target="_blank" title="Takip Et">
                                        <i class="icon-location"></i>
                                    </a>
                                    @if($shipment->status !== 'delivered')
                                    <button class="btn btn-sm btn-outline-danger" 
                                            onclick="cancelShipment({{ $shipment->id }})" title="İptal">
                                        <i class="icon-cancel"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Henüz gönderi bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $shipments->links() }}
            </div>
        </div>
    </div>
</div>

<script>
function refreshTracking() {
    fetch('{{ route("admin.shipping.bulk-update-tracking") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    });
}

function cancelShipment(id) {
    if (!confirm('Bu gönderiyi iptal etmek istediğinize emin misiniz?')) return;
    
    fetch('/admin/shipping/' + id + '/cancel', {
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
