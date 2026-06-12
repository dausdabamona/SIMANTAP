@extends('layouts.app')
@section('title', 'Dashboard PPK')
@section('page_title', 'Dashboard PPK')
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-primary fs-6">PPK</span>
        <h4 class="mb-0">Dashboard Operasional — {{ now()->format('d F Y') }}</h4>
    </div>

    @if ($alertPagu)
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div><strong>Peringatan Pagu!</strong> Realisasi telah mencapai <strong>{{ $persenPagu }}%</strong> dari pagu anggaran tahun ini.</div>
    </div>
    @endif

    @if ($invoicePendingVerifikasi > 0 || $transferPendingKonfirmasi > 0)
    <div class="alert alert-warning d-flex align-items-center gap-3 mb-3" role="alert">
        <i class="bi bi-bell-fill fs-5"></i>
        <div class="d-flex gap-3 flex-wrap">
            @if ($transferPendingKonfirmasi > 0)
            <span>Transfer penyedia menunggu konfirmasi:
                <a href="{{ route('transfer-penyedia.index') }}" class="fw-bold text-dark">
                    <span class="badge bg-primary">{{ $transferPendingKonfirmasi }}</span>
                </a>
            </span>
            @endif
            @if ($invoicePendingVerifikasi > 0)
            <span>Invoice penyedia menunggu verifikasi:
                <a href="{{ route('invoice-penyedia.index') }}" class="fw-bold text-dark">
                    <span class="badge bg-danger">{{ $invoicePendingVerifikasi }}</span>
                </a>
            </span>
            @endif
        </div>
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Rekap Menunggu</div>
                        <div class="stat-value {{ $antrianRekap > 0 ? 'text-warning' : 'text-success' }}">{{ $antrianRekap }}</div>
                        <small class="text-muted">perlu dihitung PPK</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Pembayaran Aktif</div>
                        <div class="stat-value text-info">{{ $pembayaranAktif }}</div>
                        <small class="text-muted">sedang diproses</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-send"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Realisasi / Pagu</div>
                        <div class="stat-value {{ $persenPagu >= 80 ? 'text-danger' : 'text-success' }}">{{ $persenPagu }}%</div>
                        <small class="text-muted">Rp {{ number_format($realisasi/1e6, 1) }}jt / {{ number_format($pagu/1e6, 1) }}jt</small>
                    </div>
                    <div class="stat-icon bg-{{ $persenPagu >= 80 ? 'danger' : 'success' }} bg-opacity-10 text-{{ $persenPagu >= 80 ? 'danger' : 'success' }}"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Kegiatan Luar Aktif</div>
                        <div class="stat-value text-primary">{{ $kegiatanLuarAktif }}</div>
                        <small class="text-muted">perlu diproses</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-geo-alt"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fw-semibold">Realisasi Bantuan 6 Bulan Terakhir</div>
                <div class="card-body">
                    <canvas id="chartPpk" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Pemesanan Hari Ini</div>
                <div class="card-body">
                    @if ($pemesananHariIni)
                    <div class="fw-semibold">{{ $pemesananHariIni->tanggal->format('d/m/Y') }}</div>
                    <span class="badge bg-{{ $pemesananHariIni->status === 'selesai' ? 'success' : 'warning' }}">
                        {{ ucfirst(str_replace('_', ' ', $pemesananHariIni->status)) }}
                    </span>
                    @else
                    <div class="text-muted small">Belum ada pemesanan hari ini.</div>
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-header fw-semibold">Aksi Cepat</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-calculator me-1"></i>Hitung Rekap
                    </a>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-send me-1"></i>Pengajuan Pembayaran
                    </a>
                    <a href="{{ route('pemblokiran.index') }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-lock me-1"></i>Pemblokiran Dana
                    </a>
                    <a href="{{ route('kegiatan-luar.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-geo-alt me-1"></i>Kegiatan Luar Kampus
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('chartPpk'), {
    type: 'line',
    data: {
        labels: {!! json_encode($grafikRealisasi['labels']) !!},
        datasets: [{
            label: 'Realisasi (Rp)',
            data: {!! json_encode($grafikRealisasi['values']) !!},
            borderColor: 'rgba(13,110,253,1)',
            backgroundColor: 'rgba(13,110,253,0.1)',
            tension: 0.3,
            fill: true,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => 'Rp ' + (v/1e6).toFixed(1) + 'jt' } }
        }
    }
});
</script>
@endpush
