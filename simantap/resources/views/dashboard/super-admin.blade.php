@extends('layouts.app')
@section('title', 'Dashboard Super Admin')
@section('page_title', 'Dashboard Super Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-dark fs-6">Super Admin</span>
        <h4 class="mb-0">Panel Administrasi Sistem</h4>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Total User</div>
                        <div class="stat-value text-primary">{{ number_format($totalUser) }}</div>
                        <small class="text-success">{{ $totalUserAktif }} aktif</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Total Taruna</div>
                        <div class="stat-value text-success">{{ number_format($totalTaruna) }}</div>
                        <small class="text-muted">terdaftar</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-mortarboard"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Penyedia Makan</div>
                        <div class="stat-value text-info">{{ $totalPenyedia }}</div>
                        <small class="text-muted">terdaftar</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="bi bi-shop"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Total Role</div>
                        <div class="stat-value text-warning">{{ $totalUserPerRole->count() }}</div>
                        <small class="text-muted">role aktif</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-shield-lock"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- User per Role --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header fw-semibold">Distribusi User per Role</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Role</th><th class="text-end">Jumlah</th></tr></thead>
                        <tbody>
                            @foreach ($totalUserPerRole as $role => $jml)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $role }}</span></td>
                                <td class="text-end fw-semibold">{{ $jml }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Login terbaru --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    Login Terbaru
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary">Kelola User</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Nama</th><th>Role</th><th>Login Terakhir</th></tr></thead>
                        <tbody>
                            @foreach ($aktivitasTerbaru as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td><span class="badge bg-secondary" style="font-size:.65rem">{{ $u->getRoleNames()->first() ?? '-' }}</span></td>
                                <td class="text-muted small">{{ $u->last_login_at?->diffForHumans() ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
