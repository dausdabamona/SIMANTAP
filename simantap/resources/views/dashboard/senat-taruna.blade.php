@extends('layouts.app')
@section('title', 'Dashboard Senat Taruna')
@section('page_title', 'Dashboard Senat Taruna')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-success fs-6">Senat Taruna</span>
        <h4 class="mb-0">Operasional Harian — {{ now()->format('d F Y') }}</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Status Pesanan Hari Ini</div>
                        @if ($statusPesananHariIni)
                        <div class="stat-value text-success" style="font-size:1rem">
                            {{ ucfirst(str_replace('_', ' ', $statusPesananHariIni->status)) }}
                        </div>
                        @else
                        <div class="stat-value text-muted" style="font-size:1rem">Belum Ada</div>
                        @endif
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-cart3"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Rekap Bulan Ini</div>
                        <div class="stat-value text-info">{{ $totalRekapBulanIni }}</div>
                        <small class="text-muted">baris rekap</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-table"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Usulan Pemblokiran</div>
                        <div class="stat-value {{ $notifikasiPemblokiran > 0 ? 'text-danger' : 'text-muted' }}">{{ $notifikasiPemblokiran }}</div>
                        <small class="text-muted">menunggu proses</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-lock"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Transfer Pending</div>
                        <div class="stat-value {{ $transferPending > 0 ? 'text-warning' : 'text-muted' }}">{{ $transferPending }}</div>
                        <small class="text-muted">perlu konfirmasi</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-bank"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-semibold">Aksi Hari Ini</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('pemesanan.create') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-cart-plus me-1"></i>Buat Pemesanan Harian
                    </a>
                    <a href="{{ route('penerimaan.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-check2-square me-1"></i>Input Penerimaan Makan
                    </a>
                    <a href="{{ route('monitoring.create') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-camera me-1"></i>Upload Foto Monitoring
                    </a>
                    <a href="{{ route('pemblokiran.index') }}" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-lock me-1"></i>Usulkan Pemblokiran
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-semibold">Rekap & Pembayaran</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-table me-1"></i>Rekap Bulanan
                    </a>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-send me-1"></i>Status Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
