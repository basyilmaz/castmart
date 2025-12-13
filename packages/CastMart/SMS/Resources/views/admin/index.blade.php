@extends('admin::layouts.master')

@section('page_title')
    SMS Yönetimi
@endsection

@section('content-wrapper')
<div class="content full-page">
    <div class="page-header">
        <div class="page-title">
            <h1>SMS Yönetimi</h1>
        </div>
        <div class="page-action">
            <button class="btn btn-primary" data-toggle="modal" data-target="#sendSmsModal">
                <i class="icon-add"></i> SMS Gönder
            </button>
        </div>
    </div>

    <div class="page-content">
        <!-- İstatistikler -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Bugün Gönderilen</h5>
                        <h2>{{ $stats['total_today'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Başarılı</h5>
                        <h2 class="text-success">{{ $stats['sent_today'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Başarısız</h5>
                        <h2 class="text-danger">{{ $stats['failed_today'] ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted">Kalan Kredi</h5>
                        <h2 class="text-info">
                            {{ $stats['balance']['credits'] ?? 'N/A' }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Logları -->
        <div class="card mt-4">
            <div class="card-header">
                <h4>SMS Geçmişi</h4>
            </div>
            <div class="card-body">
                <form action="" method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Telefon veya mesaj ara..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">Tüm Durumlar</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Gönderildi</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Başarısız</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="provider" class="form-control">
                                <option value="">Tüm Sağlayıcılar</option>
                                <option value="netgsm" {{ request('provider') == 'netgsm' ? 'selected' : '' }}>Netgsm</option>
                                <option value="iletimerkezi" {{ request('provider') == 'iletimerkezi' ? 'selected' : '' }}>İletimerkezi</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filtrele</button>
                        </div>
                    </div>
                </form>

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Telefon</th>
                            <th>Mesaj</th>
                            <th>Sağlayıcı</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->phone }}</td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 300px;" title="{{ $log->message }}">
                                    {{ Str::limit($log->message, 50) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary">{{ strtoupper($log->provider) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->status == 'sent' ? 'success' : 'danger' }}">
                                    {{ $log->status_text }}
                                </span>
                            </td>
                            <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Henüz SMS kaydı bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<!-- SMS Gönder Modal -->
<div class="modal fade" id="sendSmsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Gönder</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Telefon Numarası</label>
                    <input type="text" id="smsPhone" class="form-control" placeholder="05XX XXX XX XX">
                </div>
                <div class="form-group">
                    <label>Mesaj</label>
                    <textarea id="smsMessage" class="form-control" rows="4" maxlength="918"></textarea>
                    <small class="text-muted">
                        <span id="charCount">0</span>/918 karakter
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="sendSms()">Gönder</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('smsMessage').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

function sendSms() {
    const phone = document.getElementById('smsPhone').value;
    const message = document.getElementById('smsMessage').value;
    
    if (!phone || !message) {
        alert('Lütfen telefon ve mesaj alanlarını doldurun');
        return;
    }
    
    fetch('{{ route("admin.sms.send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ phone, message })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('SMS başarıyla gönderildi!');
            location.reload();
        } else {
            alert('Hata: ' + data.message);
        }
    });
}
</script>
@endsection
