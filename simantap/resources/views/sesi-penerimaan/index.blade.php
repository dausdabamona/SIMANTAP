@extends('layouts.app')
@section('title', 'Penerimaan Makan — ' . \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y'))

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Penerimaan Makanan Harian</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Penerimaan Makan</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('sesi-penerimaan.index', ['tanggal' => \Carbon\Carbon::parse($tanggal)->subDay()->toDateString()]) }}"
               class="btn btn-sm btn-outline-secondary">◀ Kemarin</a>
            <span class="badge bg-primary px-3 py-2 fs-6">
                {{ $tanggalCarbon->isoFormat('dddd, D MMMM Y') }}
            </span>
            @if ($tanggal !== today()->toDateString())
            <a href="{{ route('sesi-penerimaan.index') }}" class="btn btn-sm btn-outline-primary">Hari Ini ▶</a>
            @endif
        </div>
    </div>

    @include('components.alert')

    @if (!$pemesanan)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Belum ada pemesanan harian untuk tanggal <strong>{{ $tanggalCarbon->isoFormat('D MMMM Y') }}</strong>.
        <a href="{{ route('pemesanan.create') }}" class="alert-link">Buat pemesanan</a>
    </div>
    @else

    <div class="row g-3">
        @foreach ($sesiList as $sesi)
        @php
            $ikonSesi = match($sesi->sesi) {
                'sarapan' => '🌅', 'siang' => '☀️', 'malam' => '🌙', default => '🍽️'
            };
            $borderColor = match($sesi->status) {
                'diterima' => 'success', 'ada_masalah' => 'danger', default => 'secondary'
            };
        @endphp
        <div class="col-md-4">
            <div class="card border-{{ $borderColor }} h-100">
                <div class="card-header fw-semibold d-flex align-items-center gap-2 bg-{{ $borderColor }} bg-opacity-10">
                    <span class="fs-5">{{ $ikonSesi }}</span>
                    <span class="text-uppercase">{{ $sesi->sesi }}</span>
                    <span class="ms-auto badge bg-{{ $sesi->status_badge_color }}">{{ $sesi->status_label }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-2">
                        <tr>
                            <td class="text-muted small">Dipesan</td>
                            <td class="fw-semibold">{{ $sesi->porsi_dipesan }} porsi</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Diterima</td>
                            <td class="fw-semibold">{{ $sesi->porsi_diterima ?? '—' }}{{ $sesi->porsi_diterima ? ' porsi' : '' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Taruna Makan</td>
                            <td class="fw-semibold text-success">{{ $sesi->porsi_dimakan_taruna ?? '—' }}{{ $sesi->porsi_dimakan_taruna !== null ? ' taruna' : '' }}</td>
                        </tr>
                        @if ($sesi->status !== 'menunggu')
                        <tr>
                            <td class="text-muted small">Redistribusi</td>
                            <td class="fw-semibold text-info">{{ $sesi->porsi_redistribusi }} porsi</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Sisa</td>
                            <td class="fw-semibold {{ $sesi->porsi_sisa > 0 ? 'text-warning' : 'text-muted' }}">{{ $sesi->porsi_sisa }} porsi</td>
                        </tr>
                        @endif
                    </table>

                    @if ($sesi->status !== 'menunggu')
                    <div class="d-flex align-items-center gap-1 small mb-2">
                        @if ($sesi->rekonsiliasiValid())
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span class="text-success">Rekonsiliasi valid</span>
                        @else
                        <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                        <span class="text-warning">Rekonsiliasi belum lengkap</span>
                        @endif
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent d-grid gap-1">
                    @if ($sesi->status === 'menunggu')
                    <a href="{{ route('sesi-penerimaan.form-terima', $sesi) }}"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-box-arrow-in-down me-1"></i>Terima Makanan
                    </a>
                    @else
                    <a href="{{ route('kehadiran-makan.index', $sesi) }}"
                       class="btn btn-outline-success btn-sm">
                        <i class="bi bi-check2-square me-1"></i>Centang Kehadiran
                    </a>
                    <a href="{{ route('sesi-penerimaan.show', $sesi) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-eye me-1"></i>Detail
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card mt-4">
        <div class="card-header fw-semibold small">Ringkasan Pemesanan Hari Ini</div>
        <div class="card-body py-2">
            <div class="row text-center">
                <div class="col">
                    <div class="fw-bold">{{ $pemesanan->jumlah_porsi }}</div>
                    <div class="small text-muted">Total Porsi Dipesan</div>
                </div>
                <div class="col">
                    <div class="fw-bold text-success">{{ $pemesanan->total_porsi_diterima ?? '—' }}</div>
                    <div class="small text-muted">Total Diterima</div>
                </div>
                <div class="col">
                    <div class="fw-bold text-primary">{{ $pemesanan->total_porsi_taruna ?? '—' }}</div>
                    <div class="small text-muted">Total Taruna Makan</div>
                </div>
                <div class="col">
                    <div class="fw-bold text-info">{{ $pemesanan->total_porsi_redistribusi ?? 0 }}</div>
                    <div class="small text-muted">Redistribusi</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
