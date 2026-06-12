@extends('layouts.app')
@section('title', 'Dashboard Wadir III')
@section('page_title', 'Dashboard Wakil Direktur III')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-teal fs-6" style="background-color:#0d9488 !important">Wadir III</span>
        <h4 class="mb-0">Pengawasan & Persetujuan — {{ now()->format('F Y') }}</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Rekap Menunggu Persetujuan</div>
                        <div class="stat-value {{ $rekapMenunggu > 0 ? 'text-warning' : 'text-success' }}">{{ $rekapMenunggu }}</div>
                        <small class="text-muted">status Draft</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-clipboard-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Taruna Aktif</div>
                        <div class="stat-value text-success">{{ number_format($totalTaruna) }}</div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-mortarboard"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Kegiatan Luar Aktif</div>
                        <div class="stat-value text-info">{{ $kegiatanLuarAktif->count() }}</div>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-geo-alt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Kegiatan Bulan Ini</div>
                        <div class="stat-value text-primary">{{ $totalKegiatanBulanIni }}</div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar-event"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    Kegiatan Luar Kampus Terkini
                    <a href="{{ route('kegiatan-luar.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Kaprodi</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse ($kegiatanLuarAktif as $k)
                            <tr>
                                <td><small>{{ $k->kode_kegiatan }}</small></td>
                                <td>{{ Str::limit($k->nama_kegiatan, 30) }}</td>
                                <td><small class="text-muted">{{ $k->kaprodi?->name }}</small></td>
                                <td><span class="badge bg-info" style="font-size:.65rem">{{ str_replace('_', ' ', $k->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada kegiatan aktif</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Aksi Prioritas</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('rekap.index') }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-check2-all me-1"></i>
                        Setujui Rekap Bulanan
                        @if ($rekapMenunggu > 0)
                        <span class="badge bg-dark ms-1">{{ $rekapMenunggu }}</span>
                        @endif
                    </a>
                    <a href="{{ route('transfer-penyedia.index') }}" class="btn btn-{{ $transferMenungguWadir > 0 ? 'success' : 'outline-success' }} btn-sm">
                        <i class="bi bi-shop me-1"></i>
                        Transfer ke Penyedia
                        @if ($transferMenungguWadir > 0)
                        <span class="badge bg-dark ms-1">{{ $transferMenungguWadir }}</span>
                        @endif
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
