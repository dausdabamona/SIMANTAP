@extends('layouts.app')
@section('title', 'Detail Pemesanan')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Pemesanan {{ $pemesanan->tanggal->format('d F Y') }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pemesanan.index') }}">Pemesanan</a></li>
                <li class="breadcrumb-item active">{{ $pemesanan->tanggal->format('d/m/Y') }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('pemesanan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @php
        $statusColors = [
            'draft' => 'secondary', 'diverifikasi_pembina' => 'info',
            'dikirim_penyedia' => 'primary', 'perubahan' => 'warning',
            'disajikan' => 'success', 'selesai' => 'dark'
        ];
        $statusLabels = [
            'draft' => 'Draft', 'diverifikasi_pembina' => 'Diverifikasi Pembina',
            'dikirim_penyedia' => 'Dikirim ke Penyedia', 'perubahan' => 'Dalam Perubahan',
            'disajikan' => 'Disajikan', 'selesai' => 'Selesai'
        ];
        $currentColor = $statusColors[$pemesanan->status] ?? 'secondary';
        $currentLabel = $statusLabels[$pemesanan->status] ?? $pemesanan->status;
    @endphp

    <div class="row g-4">
        <div class="col-lg-8">
            {{-- Info Utama --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-cart-check me-2"></i>Informasi Pemesanan</span>
                    <span class="badge bg-{{ $currentColor }} fs-6">{{ $currentLabel }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Tanggal</dt><dd class="col-sm-7 fw-semibold">{{ $pemesanan->tanggal->format('l, d F Y') }}</dd>
                        <dt class="col-sm-5">Kontrak</dt><dd class="col-sm-7">{{ $pemesanan->kontrak?->nomor_kontrak }}</dd>
                        <dt class="col-sm-5">Penyedia</dt><dd class="col-sm-7">{{ $pemesanan->kontrak?->penyedia?->nama }}</dd>
                        <dt class="col-sm-5">Taruna Hadir</dt><dd class="col-sm-7">{{ number_format($pemesanan->jumlah_taruna_hadir) }} orang</dd>
                        <dt class="col-sm-5">Total Porsi</dt><dd class="col-sm-7">{{ number_format($pemesanan->jumlah_porsi) }} porsi</dd>
                        <dt class="col-sm-5">Harga/Porsi</dt><dd class="col-sm-7">Rp {{ number_format($pemesanan->harga_porsi_snapshot, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Nilai Total</dt><dd class="col-sm-7 fw-semibold text-primary fs-6">Rp {{ number_format($pemesanan->nilai_total, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Menu Sesuai Jadwal</dt>
                        <dd class="col-sm-7">
                            @if ($pemesanan->menu_sesuai_jadwal)
                                <span class="badge bg-success">Ya</span>
                            @else
                                <span class="badge bg-warning">Perubahan</span>
                            @endif
                        </dd>
                        @if ($pemesanan->catatan_menu)
                        <dt class="col-sm-5">Catatan Menu</dt><dd class="col-sm-7">{{ $pemesanan->catatan_menu }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Workflow Tanda Tangan --}}
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-pen me-2"></i>Tanda Tangan & Verifikasi</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted mb-1">Senat Taruna</div>
                                @if ($pemesanan->ttdSenat)
                                    <div class="fw-semibold">{{ $pemesanan->ttdSenat->name }}</div>
                                    <div class="small text-success"><i class="bi bi-check-circle me-1"></i>{{ $pemesanan->ttd_senat_at?->format('d/m/Y H:i') }}</div>
                                @else
                                    <div class="text-muted small">Belum ditandatangani</div>
                                    @can('pemesanan.ttd-senat')
                                    @if ($pemesanan->status === 'draft')
                                    <form method="POST" action="{{ route('pemesanan.ttd-senat', $pemesanan) }}" class="mt-2">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="bi bi-pen me-1"></i>Tanda Tangan
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted mb-1">Pembina Karakter</div>
                                @if ($pemesanan->ttdPembina)
                                    <div class="fw-semibold">{{ $pemesanan->ttdPembina->name }}</div>
                                    <div class="small text-success"><i class="bi bi-check-circle me-1"></i>{{ $pemesanan->ttd_pembina_at?->format('d/m/Y H:i') }}</div>
                                    @if ($pemesanan->catatan_pembina)
                                        <div class="small mt-1 text-muted">Catatan: {{ $pemesanan->catatan_pembina }}</div>
                                    @endif
                                @else
                                    <div class="text-muted small">Belum diverifikasi</div>
                                    @can('pemesanan.verifikasi')
                                    @if ($pemesanan->status === 'draft' && $pemesanan->ttd_senat_id)
                                    <form method="POST" action="{{ route('pemesanan.verifikasi', $pemesanan) }}" class="mt-2">
                                        @csrf
                                        <input type="text" name="catatan_pembina" class="form-control form-control-sm mb-1" placeholder="Catatan (opsional)">
                                        <button type="submit" class="btn btn-sm btn-outline-info w-100">
                                            <i class="bi bi-check-circle me-1"></i>Verifikasi
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>

                    @can('pemesanan.kirim')
                    @if ($pemesanan->status === 'diverifikasi_pembina')
                    <form method="POST" action="{{ route('pemesanan.kirim', $pemesanan) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-send me-1"></i>Kirim ke Penyedia
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>

            {{-- Foto Monitoring --}}
            @if ($pemesanan->monitoringFoto->isNotEmpty())
            <div class="card">
                <div class="card-header"><i class="bi bi-images me-2"></i>Foto Monitoring ({{ $pemesanan->monitoringFoto->count() }})</div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($pemesanan->monitoringFoto as $foto)
                        <div class="col-4 col-md-3">
                            <img src="{{ Storage::url($foto->file_foto) }}" class="img-thumbnail w-100" alt="Foto monitoring" style="height:120px;object-fit:cover">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Actions --}}
            @if (in_array($pemesanan->status, ['draft', 'perubahan']))
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-gear me-2"></i>Aksi</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('pemesanan.edit', $pemesanan) }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Edit Pemesanan
                    </a>
                    <form id="del-pemesanan" method="POST" action="{{ route('pemesanan.destroy', $pemesanan) }}">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-outline-danger w-100"
                            onclick="konfirmasiHapus('del-pemesanan', 'pemesanan {{ $pemesanan->tanggal->format('d/m/Y') }}')">
                            <i class="bi bi-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Berita Acara Perubahan --}}
            @if ($pemesanan->beritaAcaraPerubahan->isNotEmpty())
            <div class="card">
                <div class="card-header"><i class="bi bi-file-earmark-text me-2"></i>Berita Acara Perubahan</div>
                <div class="card-body">
                    @foreach ($pemesanan->beritaAcaraPerubahan as $ba)
                    <div class="mb-2 p-2 border rounded small">
                        <div class="fw-semibold">{{ $ba->nomor_ba }}</div>
                        <div class="text-muted">{{ $ba->tanggal?->format('d/m/Y') }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
