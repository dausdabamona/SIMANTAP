@extends('layouts.app')
@section('title', 'Upload Invoice')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Invoice — {{ $penyedia->nama }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.penyedia') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Invoice</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Periode</th><th>Nomor SP2D</th><th>Total Nilai</th><th>Invoice</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($invoiceList as $i => $p)
                    <tr>
                        <td>{{ $invoiceList->firstItem() + $i }}</td>
                        <td>{{ $p->periode_bulan }}/{{ $p->periode_tahun }}</td>
                        <td><small>{{ $p->nomor_sp2d ?? '-' }}</small></td>
                        <td>Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            @if ($p->invoice_penyedia)
                            <a href="{{ asset('storage/' . $p->invoice_penyedia) }}" target="_blank" class="btn btn-xs btn-outline-success btn-sm py-0">
                                <i class="bi bi-file-earmark-check"></i> Tersedia
                            </a>
                            @else
                            <span class="text-muted small">Belum upload</span>
                            @endif
                        </td>
                        <td>
                            @unless ($p->invoice_penyedia)
                            <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal"
                                data-bs-target="#modalInvoice" data-id="{{ $p->id }}">
                                <i class="bi bi-upload"></i> Upload
                            </button>
                            @endunless
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data pembayaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoiceList->hasPages())
        <div class="card-footer">{{ $invoiceList->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="modalInvoice" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('penyedia.invoice.upload') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pembayaran_id" id="inputPembayaranId">
                <div class="modal-header"><h5 class="modal-title">Upload Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="form-label">File Invoice (PDF/JPG/PNG, maks 5MB)</label>
                    <input type="file" name="file_invoice" class="form-control" accept=".pdf,.jpg,.png" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('modalInvoice').addEventListener('show.bs.modal', function(e) {
    document.getElementById('inputPembayaranId').value = e.relatedTarget.dataset.id;
});
</script>
@endpush
@endsection
