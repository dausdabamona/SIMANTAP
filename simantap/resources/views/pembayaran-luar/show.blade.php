@extends('layouts.app')
@section('title', 'Detail Pembayaran Luar Kampus')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Pembayaran Tahap {{ $pembayaran->tahap }} — {{ $pembayaran->kegiatan?->kode_kegiatan }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pembayaran-luar.index') }}">Pembayaran Luar Kampus</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol></nav>
        </div>
        <a href="{{ route('pembayaran-luar.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Detail Pembayaran</div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-6">Kegiatan</dt><dd class="col-6">{{ $pembayaran->kegiatan?->nama_kegiatan }}</dd>
                        <dt class="col-6">Kode</dt><dd class="col-6">{{ $pembayaran->kegiatan?->kode_kegiatan }}</dd>
                        <dt class="col-6">Tahap</dt><dd class="col-6">{{ $pembayaran->tahap }}</dd>
                        <dt class="col-6">Nilai Diajukan</dt><dd class="col-6">Rp {{ number_format($pembayaran->nilai_diajukan, 0, ',', '.') }}</dd>
                        <dt class="col-6">Nilai Disetujui</dt><dd class="col-6 fw-bold">Rp {{ number_format($pembayaran->nilai_disetujui, 0, ',', '.') }}</dd>
                        <dt class="col-6">Nomor SP2D</dt><dd class="col-6">{{ $pembayaran->nomor_sp2d ?? '-' }}</dd>
                        <dt class="col-6">Tanggal SP2D</dt><dd class="col-6">{{ $pembayaran->tanggal_sp2d?->format('d/m/Y') ?? '-' }}</dd>
                        <dt class="col-6">Status</dt><dd class="col-6"><span class="badge bg-secondary">{{ str_replace('_', ' ', $pembayaran->status) }}</span></dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header fw-semibold">Aksi</div>
                <div class="card-body d-grid gap-2">
                    @can('pembayaran_luar.proses')
                    @if ($pembayaran->status === 'draft')
                    <form method="POST" action="{{ route('pembayaran-luar.verifikasi-ppk', $pembayaran) }}">
                        @csrf
                        <button class="btn btn-outline-info btn-sm w-100"><i class="bi bi-check-circle me-1"></i>Verifikasi PPK</button>
                    </form>
                    @elseif ($pembayaran->status === 'diverifikasi_ppk')
                    <form method="POST" action="{{ route('pembayaran-luar.ajukan-kppn', $pembayaran) }}">
                        @csrf
                        <button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-send me-1"></i>Ajukan ke KPPN</button>
                    </form>
                    @elseif ($pembayaran->status === 'sp2d_terbit')
                    <form method="POST" action="{{ route('pembayaran-luar.transfer', $pembayaran) }}">
                        @csrf
                        <button class="btn btn-outline-success btn-sm w-100"><i class="bi bi-bank me-1"></i>Konfirmasi Transfer</button>
                    </form>
                    @elseif ($pembayaran->status === 'transfer_selesai')
                    <form method="POST" action="{{ route('pembayaran-luar.konfirmasi-taruna', $pembayaran) }}">
                        @csrf
                        <button class="btn btn-outline-dark btn-sm w-100"><i class="bi bi-person-check me-1"></i>Konfirmasi Taruna</button>
                    </form>
                    @endif
                    @endcan

                    @can('pembayaran_luar.input_sp2d')
                    @if ($pembayaran->status === 'diajukan_kppn')
                    <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalSp2d">
                        <i class="bi bi-file-earmark-text me-1"></i>Input SP2D
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header fw-semibold">Daftar Peserta Kegiatan</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>NIT</th><th>Nama Taruna</th><th>Hari Hadir</th><th>Nilai Bantuan</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($pembayaran->kegiatan?->peserta ?? [] as $i => $p)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $p->taruna?->nit }}</td>
                                <td>{{ $p->taruna?->nama }}</td>
                                <td>{{ $p->hari_hadir }}</td>
                                <td>Rp {{ number_format($p->nilai_bantuan, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada peserta</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@can('pembayaran_luar.input_sp2d')
@if ($pembayaran->status === 'diajukan_kppn')
<div class="modal fade" id="modalSp2d" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('pembayaran-luar.sp2d', $pembayaran) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Input SP2D</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Nomor SP2D</label>
                        <input type="text" name="nomor_sp2d" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tanggal SP2D</label>
                        <input type="date" name="tanggal_sp2d" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nilai Disetujui (Rp)</label>
                        <input type="number" name="nilai_disetujui" class="form-control" min="0" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">File SP2D (PDF, maks 5MB)</label>
                        <input type="file" name="file_sp2d" class="form-control" accept=".pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan
@endsection
