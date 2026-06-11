@extends('layouts.app')
@section('title', 'Dashboard Auditor')
@section('page_title', 'Dashboard Auditor')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-dark fs-6">Auditor</span>
        <h4 class="mb-0">Ringkasan Data Sistem</h4>
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['label' => 'Total Taruna',     'value' => $ringkasan['taruna'],     'icon' => 'bi-mortarboard',    'color' => 'primary'],
            ['label' => 'Total Pembayaran', 'value' => $ringkasan['pembayaran'], 'icon' => 'bi-send-fill',      'color' => 'success'],
            ['label' => 'Kegiatan Luar',    'value' => $ringkasan['kegiatan'],   'icon' => 'bi-geo-alt-fill',   'color' => 'info'],
            ['label' => 'Laporan Montev',   'value' => $ringkasan['montev'],     'icon' => 'bi-graph-up',       'color' => 'warning'],
            ['label' => 'Total User',       'value' => $ringkasan['user'],       'icon' => 'bi-people-fill',    'color' => 'secondary'],
        ] as $s)
        <div class="col-sm-6 col-xl">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">{{ $s['label'] }}</div>
                        <div class="stat-value text-{{ $s['color'] }}">{{ number_format($s['value']) }}</div>
                    </div>
                    <div class="stat-icon bg-{{ $s['color'] }} bg-opacity-10 text-{{ $s['color'] }}">
                        <i class="bi {{ $s['icon'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    Login Terakhir per User
                    <a href="{{ route('audit-log.index') }}" class="btn btn-sm btn-outline-dark">Audit Log Lengkap</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Login Terakhir</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($loginTerbaru as $i => $u)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $u->name }}</td>
                                <td class="text-muted small">{{ $u->email }}</td>
                                <td><span class="badge bg-secondary" style="font-size:.65rem">{{ $u->getRoleNames()->first() ?? '-' }}</span></td>
                                <td class="small text-muted">{{ $u->last_login_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header fw-semibold">Navigasi Cepat</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('audit-log.index') }}" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-journal-text me-1"></i>Audit Log
                    </a>
                    <a href="{{ route('taruna.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-mortarboard me-1"></i>Data Taruna
                    </a>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-send me-1"></i>Data Pembayaran
                    </a>
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-table me-1"></i>Rekap Bulanan
                    </a>
                    <a href="{{ route('laporan.rekap') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Laporan
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
