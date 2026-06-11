@extends('layouts.app')
@section('title', 'Dashboard KPA')
@section('page_title', 'Dashboard KPA / Direktur')
@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-success fs-6">KPA</span>
        <h4 class="mb-0">Ringkasan Eksekutif — {{ now()->format('F Y') }}</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Pagu Anggaran {{ now()->year }}</div>
                        <div class="stat-value text-primary" style="font-size:1.2rem">Rp {{ number_format($pagu, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Realisasi</div>
                        <div class="stat-value {{ $persenRealisasi >= 80 ? 'text-danger' : 'text-success' }}" style="font-size:1.2rem">
                            Rp {{ number_format($realisasi, 0, ',', '.') }}
                        </div>
                        <small class="{{ $persenRealisasi >= 80 ? 'text-danger' : 'text-muted' }}">{{ $persenRealisasi }}% dari pagu</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-graph-up-arrow"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Taruna Aktif</div>
                        <div class="stat-value text-info">{{ number_format($totalTaruna) }}</div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-mortarboard"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Kegiatan Luar Aktif</div>
                        <div class="stat-value text-warning">{{ $kegiatanLuarAktif }}</div>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-geo-alt"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fw-semibold">Realisasi Pembayaran 6 Bulan Terakhir</div>
                <div class="card-body">
                    <canvas id="chartRealisasi" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header fw-semibold">Progress Pagu</div>
                <div class="card-body text-center">
                    <div class="my-3">
                        <div class="progress" style="height:20px">
                            <div class="progress-bar {{ $persenRealisasi >= 80 ? 'bg-danger' : 'bg-success' }}"
                                 style="width:{{ min($persenRealisasi, 100) }}%">
                                {{ $persenRealisasi }}%
                            </div>
                        </div>
                    </div>
                    <small class="text-muted">
                        Sisa: Rp {{ number_format(max($pagu - $realisasi, 0), 0, ',', '.') }}
                    </small>
                    @if ($antrianPersetujuan > 0)
                    <div class="alert alert-warning mt-3 mb-0 py-2 small">
                        <i class="bi bi-clock-history me-1"></i>
                        {{ $antrianPersetujuan }} rekap menunggu tanda tangan KPA
                    </div>
                    @endif
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header fw-semibold">Aksi Cepat</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-table me-1"></i>Rekap Bulanan
                    </a>
                    <a href="{{ route('kegiatan-luar.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-geo-alt me-1"></i>Kegiatan Luar Kampus
                    </a>
                    <a href="{{ route('montev.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-graph-up me-1"></i>Monev
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('chartRealisasi'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($grafikRealisasi['labels']) !!},
        datasets: [{
            label: 'Realisasi (Rp)',
            data: {!! json_encode($grafikRealisasi['values']) !!},
            backgroundColor: 'rgba(13,110,253,0.7)',
            borderRadius: 4,
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
