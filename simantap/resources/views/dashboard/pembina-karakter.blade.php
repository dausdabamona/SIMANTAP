@extends('layouts.app')
@section('title', 'Dashboard Pembina Karakter')
@section('page_title', 'Dashboard Pembina Karakter')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-primary fs-6">Pembina Karakter</span>
        <h4 class="mb-0">Monitoring Harian — {{ now()->format('d F Y') }}</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Antrian Verifikasi</div>
                        <div class="stat-value {{ $antrianVerifikasi > 0 ? 'text-warning' : 'text-success' }}">{{ $antrianVerifikasi }}</div>
                        <small class="text-muted">pemesanan draft</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-cart-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Tidak Dapat Makan Hari Ini</div>
                        <div class="stat-value {{ $tidakEligibleHariIni->count() > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $tidakEligibleHariIni->count() }}
                        </div>
                        <small class="text-muted">taruna tidak eligible</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Rekap Perlu TTD</div>
                        <div class="stat-value {{ $totalRekapTtd > 0 ? 'text-info' : 'text-muted' }}">{{ $totalRekapTtd }}</div>
                        <small class="text-muted">status dihitung PPK</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-pen"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Tanggal Hari Ini</div>
                        <div class="fw-bold text-primary" style="font-size:1rem">{{ now()->format('d M Y') }}</div>
                        <small class="text-muted">{{ now()->format('l') }}</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-calendar-date"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-semibold">Pemesanan Hari Ini</div>
                <div class="card-body">
                    @if ($pemesananHariIni)
                    <dl class="row small mb-0">
                        <dt class="col-5">Tanggal</dt><dd class="col-7">{{ $pemesananHariIni->tanggal->format('d/m/Y') }}</dd>
                        <dt class="col-5">Status</dt><dd class="col-7">
                            <span class="badge bg-{{ $pemesananHariIni->status === 'selesai' ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $pemesananHariIni->status)) }}
                            </span>
                        </dd>
                    </dl>
                    <a href="{{ route('pemesanan.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-eye me-1"></i>Detail Pemesanan
                    </a>
                    @else
                    <div class="text-muted small py-2">Belum ada pemesanan hari ini.</div>
                    <a href="{{ route('pemesanan.index') }}" class="btn btn-sm btn-outline-primary mt-1">
                        <i class="bi bi-cart-plus me-1"></i>Lihat Pemesanan
                    </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-semibold text-danger">Taruna Tidak Dapat Makan Hari Ini</div>
                <div class="card-body p-0">
                    @if ($tidakEligibleHariIni->isEmpty())
                    <div class="text-center text-muted py-4 small">Semua taruna eligible hari ini.</div>
                    @else
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Nama Taruna</th><th>Alasan</th></tr></thead>
                        <tbody>
                            @foreach ($tidakEligibleHariIni->take(8) as $p)
                            <tr>
                                <td>{{ $p->taruna?->nama }}</td>
                                <td><small class="text-muted">{{ $p->alasan_pengecualian ?? '-' }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="d-flex gap-2">
            <a href="{{ route('pemesanan.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-cart-check me-1"></i>Verifikasi Pemesanan
            </a>
            <a href="{{ route('monitoring.index') }}" class="btn btn-outline-info btn-sm">
                <i class="bi bi-camera me-1"></i>Monitoring Foto
            </a>
            <a href="{{ route('rekap.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pen me-1"></i>Rekap TTD Pembina
            </a>
        </div>
    </div>
</div>
@endsection
