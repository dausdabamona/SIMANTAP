@extends('layouts.app')
@section('title', 'Dashboard Kaprodi')
@section('page_title', 'Dashboard Kaprodi')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-secondary fs-6">Kaprodi</span>
        <h4 class="mb-0">Kegiatan Luar Kampus — {{ now()->format('F Y') }}</h4>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Kegiatan Aktif</div>
                        <div class="stat-value text-primary">{{ $kegiatanAktif->count() }}</div>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-geo-alt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Pembayaran Draft</div>
                        <div class="stat-value {{ $pembayaranDraft->count() > 0 ? 'text-warning' : 'text-muted' }}">
                            {{ $pembayaranDraft->count() }}
                        </div>
                        <small class="text-muted">perlu diajukan</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-cash"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Disetujui Pusdik</div>
                        <div class="stat-value text-success">{{ $statusCount[\App\Models\KegiatanLuarKampus::STATUS_DISETUJUI_PUSDIK] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-patch-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card card border-0">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label text-muted mb-2">Selesai</div>
                        <div class="stat-value text-dark">{{ $statusCount[\App\Models\KegiatanLuarKampus::STATUS_SELESAI] ?? 0 }}</div>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-check-circle"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    Kegiatan Aktif Saya
                    <a href="{{ route('kegiatan-luar.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Buat Kegiatan
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Kode</th><th>Nama</th><th>Periode</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @forelse ($kegiatanAktif as $k)
                            <tr>
                                <td><small>{{ $k->kode_kegiatan }}</small></td>
                                <td>{{ Str::limit($k->nama_kegiatan, 25) }}</td>
                                <td><small>{{ $k->tanggal_mulai->format('d/m') }} – {{ $k->tanggal_selesai->format('d/m/Y') }}</small></td>
                                <td><span class="badge bg-info" style="font-size:.65rem">{{ str_replace('_', ' ', $k->status) }}</span></td>
                                <td><a href="{{ route('kegiatan-luar.show', $k) }}" class="btn btn-xs btn-outline-info btn-sm py-0 px-1"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada kegiatan aktif</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            @if ($pembayaranDraft->isNotEmpty())
            <div class="card">
                <div class="card-header fw-semibold text-warning">Pembayaran Perlu Diajukan</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light"><tr><th>Kegiatan</th><th>Tahap</th></tr></thead>
                        <tbody>
                            @foreach ($pembayaranDraft as $p)
                            <tr>
                                <td><small>{{ $p->kegiatan?->kode_kegiatan }}</small></td>
                                <td><span class="badge bg-warning">{{ $p->tahap }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-header fw-semibold">Aksi Cepat</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('kegiatan-luar.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>Buat Kegiatan Baru
                    </a>
                    <a href="{{ route('kegiatan-luar.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list me-1"></i>Semua Kegiatan
                    </a>
                    <a href="{{ route('pembayaran-luar.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-cash me-1"></i>Status Pembayaran
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
