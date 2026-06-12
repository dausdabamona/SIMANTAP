@extends('layouts.app')
@section('title', 'Detail Transfer Penyedia')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Transfer {{ $transferPenyedia->bank_group }} — {{ $transferPenyedia->periode_label }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('transfer-penyedia.index') }}">Transfer Penyedia</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol></nav>
        </div>
        <a href="{{ route('transfer-penyedia.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span>Informasi Transfer</span>
                    <span class="badge bg-{{ $transferPenyedia->status_badge_color }}">{{ $transferPenyedia->status_label }}</span>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Periode</dt><dd class="col-7">{{ $transferPenyedia->periode_label }}</dd>
                        <dt class="col-5">Bank Group</dt>
                        <dd class="col-7"><span class="badge bg-{{ $transferPenyedia->bank_group === 'BSI' ? 'success' : 'primary' }}">{{ $transferPenyedia->bank_group }}</span></dd>
                        <dt class="col-5">Rekening Senat</dt><dd class="col-7">{{ $transferPenyedia->senatAccount?->nama_akun ?? '-' }}</dd>
                        <dt class="col-5">Rekening Tujuan</dt><dd class="col-7">{{ $transferPenyedia->rekeningPenyedia?->bank ?? '-' }} — {{ $transferPenyedia->rekeningPenyedia?->nomor_rekening ?? '-' }}</dd>
                        <dt class="col-5">Jumlah Kelas</dt><dd class="col-7">{{ $transferPenyedia->jumlah_kelas }} kelas</dd>
                        <dt class="col-5">Jumlah Taruna</dt><dd class="col-7">{{ number_format($transferPenyedia->jumlah_taruna) }} taruna</dd>
                        <dt class="col-5">Total Nilai</dt><dd class="col-7 fw-bold text-primary">Rp {{ number_format($transferPenyedia->total_nilai, 0, ',', '.') }}</dd>
                        @if ($transferPenyedia->tanggal_transfer)
                        <dt class="col-5">Tgl Transfer</dt><dd class="col-7">{{ $transferPenyedia->tanggal_transfer->format('d/m/Y') }}</dd>
                        @endif
                        @if ($transferPenyedia->bukti_transfer)
                        <dt class="col-5">Bukti</dt><dd class="col-7"><a href="{{ asset('storage/' . $transferPenyedia->bukti_transfer) }}" target="_blank" class="btn btn-xs btn-outline-secondary btn-sm py-0"><i class="bi bi-file-earmark"></i> Lihat</a></dd>
                        @endif
                        @if ($transferPenyedia->catatan)
                        <dt class="col-5">Catatan</dt><dd class="col-7">{{ $transferPenyedia->catatan }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Aksi berdasarkan status --}}
            @role('wadir_iii')
            @if ($transferPenyedia->status === \App\Models\TransferPenyedia::STATUS_MENUNGGU)
            <div class="card mb-4 border-warning">
                <div class="card-header bg-warning text-dark fw-semibold">Setujui Transfer (Wadir III)</div>
                <div class="card-body">
                    <p class="small">Setujui transfer ini agar Senat dapat mengupload bukti transfer.</p>
                    <form method="POST" action="{{ route('transfer-penyedia.setujui-wadir', $transferPenyedia) }}"
                          onsubmit="return confirm('Setujui transfer {{ $transferPenyedia->bank_group }} periode {{ $transferPenyedia->periode_label }}?')">
                        @csrf
                        <button class="btn btn-warning btn-sm"><i class="bi bi-check2 me-1"></i>Setujui</button>
                    </form>
                </div>
            </div>
            @endif
            @endrole

            @role('senat_taruna')
            @if ($transferPenyedia->status === \App\Models\TransferPenyedia::STATUS_DISETUJUI_WADIR)
            <div class="card mb-4 border-info">
                <div class="card-header bg-info text-dark fw-semibold">Upload Bukti Transfer (Senat)</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transfer-penyedia.upload-bukti', $transferPenyedia) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Tanggal Transfer <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_transfer" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Bukti Transfer <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_transfer" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Catatan</label>
                            <textarea name="catatan" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <button class="btn btn-info btn-sm text-dark"><i class="bi bi-upload me-1"></i>Upload Bukti</button>
                    </form>
                </div>
            </div>
            @endif
            @endrole

            @can('pembayaran.konfirmasi')
            @if ($transferPenyedia->status === \App\Models\TransferPenyedia::STATUS_DITRANSFER)
            <div class="card mb-4 border-success">
                <div class="card-header bg-success text-white fw-semibold">Konfirmasi Penerimaan (PPK)</div>
                <div class="card-body">
                    <p class="small">Konfirmasi bahwa penyedia sudah menerima dana transfer ini.</p>
                    <form method="POST" action="{{ route('transfer-penyedia.konfirmasi', $transferPenyedia) }}"
                          onsubmit="return confirm('Konfirmasi penyedia sudah menerima dana?')">
                        @csrf
                        <button class="btn btn-success btn-sm"><i class="bi bi-check2-all me-1"></i>Konfirmasi Diterima</button>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header fw-semibold"><i class="bi bi-table me-2"></i>SPM Sumber ({{ $transferPenyedia->spms->count() }} kelas)</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>No. Pengajuan</th>
                                <th>Kelas</th>
                                <th>Taruna</th>
                                <th>Kontribusi</th>
                                <th>Status SPM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transferPenyedia->spms as $i => $spm)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td><small class="font-monospace">{{ $spm->nomor_pengajuan }}</small></td>
                                <td>{{ $spm->kelas ?? '-' }}</td>
                                <td>{{ $spm->total_taruna }}</td>
                                <td>Rp {{ number_format($spm->pivot->nilai_kontribusi, 0, ',', '.') }}</td>
                                <td><span class="badge bg-success small">{{ ucfirst(str_replace('_', ' ', $spm->status)) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada SPM terhubung</td></tr>
                            @endforelse
                        </tbody>
                        @if ($transferPenyedia->spms->isNotEmpty())
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-semibold">Total</td>
                                <td class="fw-bold">Rp {{ number_format($transferPenyedia->spms->sum('pivot.nilai_kontribusi'), 0, ',', '.') }}</td>
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
