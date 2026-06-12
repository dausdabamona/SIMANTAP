@extends('layouts.app')
@section('title', 'Detail Invoice Penyedia')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Invoice — {{ $invoicePenyedia->periode_label }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('invoice-penyedia.index') }}">Invoice Penyedia</a></li>
                <li class="breadcrumb-item active">{{ $invoicePenyedia->periode_label }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('invoice-penyedia.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>Informasi Invoice</span>
                    <span class="badge bg-{{ $invoicePenyedia->status_badge_color }}">{{ $invoicePenyedia->status_label }}</span>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Periode</dt><dd class="col-7">{{ $invoicePenyedia->periode_label }}</dd>
                        <dt class="col-5">Penyedia</dt><dd class="col-7">{{ $invoicePenyedia->penyedia?->nama ?? '-' }}</dd>
                        <dt class="col-5">No. Invoice</dt><dd class="col-7 font-monospace">{{ $invoicePenyedia->nomor_invoice ?? '-' }}</dd>
                        <dt class="col-5">Tgl Invoice</dt><dd class="col-7">{{ $invoicePenyedia->tanggal_invoice?->format('d/m/Y') ?? '-' }}</dd>
                        <dt class="col-5">Total Nilai</dt><dd class="col-7 fw-bold text-primary">Rp {{ number_format($invoicePenyedia->total_nilai, 0, ',', '.') }}</dd>
                        @if ($invoicePenyedia->file_invoice)
                        <dt class="col-5">File Invoice</dt>
                        <dd class="col-7">
                            <a href="{{ asset('storage/' . $invoicePenyedia->file_invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0">
                                <i class="bi bi-file-pdf me-1"></i>Lihat PDF
                            </a>
                        </dd>
                        @endif
                        @if ($invoicePenyedia->diverifikasi_at)
                        <dt class="col-5">Diverifikasi</dt><dd class="col-7">{{ $invoicePenyedia->diverifikasi_at->format('d/m/Y H:i') }} oleh {{ $invoicePenyedia->diverifikasiOleh?->name }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Upload form (penyedia) --}}
            @role('penyedia')
            @if ($invoicePenyedia->status === \App\Models\InvoicePenyedia::STATUS_MENUNGGU)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark fw-semibold">Upload Invoice</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('invoice-penyedia.upload', $invoicePenyedia) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">No. Invoice <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_invoice" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Tanggal Invoice <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_invoice" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Total Nilai Invoice (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="total_nilai" class="form-control form-control-sm"
                                value="{{ $invoicePenyedia->total_nilai }}" step="0.01" min="0" required>
                            <div class="form-text">Max: Rp {{ number_format($invoicePenyedia->total_nilai, 0, ',', '.') }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">File Invoice (PDF) <span class="text-danger">*</span></label>
                            <input type="file" name="file_invoice" class="form-control form-control-sm" accept=".pdf" required>
                        </div>
                        <button class="btn btn-warning btn-sm text-dark"><i class="bi bi-upload me-1"></i>Upload Invoice</button>
                    </form>
                </div>
            </div>
            @endif
            @endrole

            {{-- Verifikasi form (PPK) --}}
            @can('pembayaran.lpj')
            @if ($invoicePenyedia->status === \App\Models\InvoicePenyedia::STATUS_DITERIMA)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white fw-semibold">Verifikasi Invoice (PPK)</div>
                <div class="card-body">
                    <p class="small">Verifikasi bahwa nilai invoice sesuai dengan total transfer yang diterima penyedia.</p>
                    <form method="POST" action="{{ route('invoice-penyedia.verifikasi', $invoicePenyedia) }}"
                          onsubmit="return confirm('Verifikasi invoice ini?')">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i>Verifikasi</button>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header fw-semibold"><i class="bi bi-bank me-2"></i>Transfer Sumber (BSI + BNI)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bank</th>
                                <th>Rekening Senat</th>
                                <th>Kelas</th>
                                <th>Taruna</th>
                                <th>Total Nilai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transfers as $t)
                            <tr>
                                <td><span class="badge bg-{{ $t->bank_group === 'BSI' ? 'success' : 'primary' }}">{{ $t->bank_group }}</span></td>
                                <td><small>{{ $t->senatAccount?->nama_akun ?? '-' }}</small></td>
                                <td>{{ $t->jumlah_kelas }}</td>
                                <td>{{ number_format($t->jumlah_taruna) }}</td>
                                <td>Rp {{ number_format($t->total_nilai, 0, ',', '.') }}</td>
                                <td><span class="badge bg-{{ $t->status_badge_color }} small">{{ $t->status_label }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data transfer</td></tr>
                            @endforelse
                        </tbody>
                        @if ($transfers->isNotEmpty())
                        <tfoot class="table-light fw-semibold">
                            <tr>
                                <td colspan="4" class="text-end">Total Transfer</td>
                                <td>Rp {{ number_format($transfers->sum('total_nilai'), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
