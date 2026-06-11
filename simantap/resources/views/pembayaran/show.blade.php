@extends('layouts.app')
@section('title', 'Detail Pengajuan Pembayaran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Pengajuan: {{ $pembayaran->nomor_pengajuan }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
                <li class="breadcrumb-item active">{{ $pembayaran->nomor_pengajuan }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('pdf.pengajuan-pembayaran', $pembayaran) }}" class="btn btn-outline-danger btn-sm" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>PDF
        </a>
        <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Status Header --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-medical me-2"></i>Informasi Pengajuan</span>
                    <span class="badge bg-primary fs-6">{{ $pembayaran->status_label }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Nomor Pengajuan</dt><dd class="col-sm-7 fw-semibold font-monospace">{{ $pembayaran->nomor_pengajuan }}</dd>
                        <dt class="col-sm-5">Periode</dt><dd class="col-sm-7">{{ $pembayaran->nama_bulan }} {{ $pembayaran->periode_tahun }}</dd>
                        <dt class="col-sm-5">Total Taruna</dt><dd class="col-sm-7">{{ number_format($pembayaran->total_taruna) }} taruna</dd>
                        <dt class="col-sm-5">Total Porsi</dt><dd class="col-sm-7">{{ number_format($pembayaran->total_porsi) }} porsi</dd>
                        <dt class="col-sm-5">Total Nilai</dt>
                        <dd class="col-sm-7 fw-semibold text-primary fs-5">Rp {{ number_format($pembayaran->total_nilai, 0, ',', '.') }}</dd>
                        @if ($pembayaran->nomor_sp2d)
                        <dt class="col-sm-5">No. SP2D</dt><dd class="col-sm-7 font-monospace">{{ $pembayaran->nomor_sp2d }}</dd>
                        <dt class="col-sm-5">Tanggal SP2D</dt><dd class="col-sm-7">{{ $pembayaran->tanggal_sp2d?->format('d F Y') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Workflow Timeline --}}
            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-2"></i>Riwayat Alur Pembayaran</div>
                <div class="card-body">
                    @if ($pembayaran->workflow->isEmpty())
                        <p class="text-muted small">Belum ada aktivitas.</p>
                    @else
                    <div class="timeline">
                        @foreach ($pembayaran->workflow as $wf)
                        <div class="d-flex mb-3 gap-3">
                            <div class="text-primary flex-shrink-0 mt-1"><i class="bi bi-arrow-right-circle-fill"></i></div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-semibold small">{{ $wf->status_ke ? ucfirst(str_replace('_', ' ', $wf->status_ke)) : 'Dibuat' }}</span>
                                    <span class="small text-muted">{{ $wf->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="small text-muted">{{ $wf->user?->name ?? '-' }}</div>
                                @if ($wf->catatan)
                                <div class="small">{{ $wf->catatan }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action Panel --}}
        <div class="col-lg-4">
            @php
                $nextActions = match ($pembayaran->status) {
                    'draft'               => [['aksi' => 'proses_ppk', 'label' => 'Proses oleh PPK', 'color' => 'info', 'perm' => 'pembayaran.proses_ppk']],
                    'diproses_ppk'        => [['aksi' => 'setujui_kpa', 'label' => 'Setujui (KPA)', 'color' => 'primary', 'perm' => 'pembayaran.setujui_kpa']],
                    'disetujui_kpa'       => [['aksi' => 'permohonan_kppn', 'label' => 'Kirim ke KPPN', 'color' => 'warning', 'perm' => 'pembayaran.permohonan_kppn']],
                    'permohonan_kppn'     => [['aksi' => 'input_sp2d', 'label' => 'Input SP2D', 'color' => 'info', 'perm' => 'pembayaran.input_sp2d', 'form' => 'sp2d']],
                    'sp2d'                => [['aksi' => 'transfer_kppn', 'label' => 'Upload Bukti Transfer KPPN', 'color' => 'primary', 'perm' => 'pembayaran.upload', 'form' => 'upload', 'field' => 'bukti_transfer_kppn']],
                    'transfer_kppn'       => [['aksi' => 'debit_bank', 'label' => 'Upload Bukti Debit Bank', 'color' => 'warning', 'perm' => 'pembayaran.upload', 'form' => 'upload', 'field' => 'bukti_debit_bank']],
                    'debit_bank'          => [['aksi' => 'transfer_penyedia', 'label' => 'Upload Bukti Transfer Penyedia', 'color' => 'success', 'perm' => 'pembayaran.upload', 'form' => 'upload', 'field' => 'bukti_transfer_penyedia']],
                    'transfer_penyedia'   => [['aksi' => 'konfirmasi_penyedia', 'label' => 'Konfirmasi Penyedia', 'color' => 'success', 'perm' => 'pembayaran.konfirmasi']],
                    'konfirmasi_penyedia' => [['aksi' => 'lpj_ppk', 'label' => 'LPJ PPK', 'color' => 'info', 'perm' => 'pembayaran.lpj']],
                    'lpj_ppk'             => [['aksi' => 'lpj_kpa', 'label' => 'LPJ KPA', 'color' => 'info', 'perm' => 'pembayaran.lpj']],
                    'lpj_kpa'             => [['aksi' => 'selesai', 'label' => 'Tandai Selesai', 'color' => 'dark', 'perm' => 'pembayaran.selesai']],
                    default               => [],
                };
            @endphp

            @if (!empty($nextActions))
            <div class="card">
                <div class="card-header"><i class="bi bi-gear me-2"></i>Aksi Selanjutnya</div>
                <div class="card-body d-grid gap-2">
                    @foreach ($nextActions as $act)
                    @can($act['perm'])
                    @if (isset($act['form']) && $act['form'] === 'sp2d')
                    <form method="POST" action="{{ route('pembayaran.transisi', $pembayaran) }}">
                        @csrf
                        <input type="hidden" name="aksi" value="{{ $act['aksi'] }}">
                        <div class="mb-2">
                            <input type="text" name="nomor_sp2d" class="form-control form-control-sm mb-1" placeholder="Nomor SP2D *" required>
                            <input type="date" name="tanggal_sp2d" class="form-control form-control-sm" required>
                        </div>
                        <button type="submit" class="btn btn-{{ $act['color'] }} btn-sm w-100">
                            <i class="bi bi-check-lg me-1"></i>{{ $act['label'] }}
                        </button>
                    </form>
                    @elseif (isset($act['form']) && $act['form'] === 'upload')
                    <form method="POST" action="{{ route('pembayaran.transisi', $pembayaran) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="aksi" value="{{ $act['aksi'] }}">
                        <div class="mb-2">
                            <input type="file" name="{{ $act['field'] }}" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <button type="submit" class="btn btn-{{ $act['color'] }} btn-sm w-100">
                            <i class="bi bi-upload me-1"></i>{{ $act['label'] }}
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('pembayaran.transisi', $pembayaran) }}">
                        @csrf
                        <input type="hidden" name="aksi" value="{{ $act['aksi'] }}">
                        <input type="text" name="catatan" class="form-control form-control-sm mb-1" placeholder="Catatan (opsional)">
                        <button type="submit" class="btn btn-{{ $act['color'] }} btn-sm w-100">
                            <i class="bi bi-arrow-right me-1"></i>{{ $act['label'] }}
                        </button>
                    </form>
                    @endif
                    @endcan
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Dokumen Bukti --}}
            <div class="card mt-4">
                <div class="card-header"><i class="bi bi-paperclip me-2"></i>Dokumen Bukti</div>
                <div class="card-body">
                    @foreach ([
                        'invoice_penyedia'       => 'Invoice Penyedia',
                        'bukti_transfer_kppn'    => 'Bukti Transfer KPPN',
                        'bukti_debit_bank'        => 'Bukti Debit Bank',
                        'bukti_transfer_penyedia' => 'Bukti Transfer Penyedia',
                    ] as $field => $label)
                    <div class="d-flex align-items-center justify-content-between mb-2 small">
                        <span>{{ $label }}</span>
                        @if ($pembayaran->$field)
                            <a href="{{ Storage::url($pembayaran->$field) }}" target="_blank" class="btn btn-xs btn-outline-primary btn-sm">
                                <i class="bi bi-file-earmark me-1"></i>Buka
                            </a>
                        @else
                            <span class="badge bg-light text-muted">–</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
