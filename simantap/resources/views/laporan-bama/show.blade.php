@extends('layouts.app')
@section('title', 'Detail Laporan BAMA')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Laporan BAMA — {{ $laporanBama->periode_label }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('laporan-bama.index') }}">Laporan BAMA</a></li>
                <li class="breadcrumb-item active">{{ $laporanBama->periode_label }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('laporan-bama.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        {{-- Kolom kiri: Info & Aksi --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Informasi Laporan</div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Periode</dt><dd class="col-7">{{ $laporanBama->periode_label }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            @php $badgeMap = ['draft'=>'secondary','disetujui_wadir'=>'info','disetujui_kpa'=>'success','dikirim_pusdik'=>'dark']; @endphp
                            <span class="badge bg-{{ $badgeMap[$laporanBama->status] ?? 'secondary' }}">
                                {{ ucwords(str_replace('_', ' ', $laporanBama->status)) }}
                            </span>
                        </dd>
                        <dt class="col-5">Dibuat</dt><dd class="col-7">{{ $laporanBama->dibuatOleh?->name }}</dd>
                        @if ($laporanBama->disetujui_wadir_at)
                        <dt class="col-5">TTD Wadir</dt><dd class="col-7">{{ $laporanBama->disetujuiWadirOleh?->name }}<br><small class="text-muted">{{ $laporanBama->disetujui_wadir_at->format('d/m/Y') }}</small></dd>
                        @endif
                        @if ($laporanBama->disetujui_kpa_at)
                        <dt class="col-5">TTD KPA</dt><dd class="col-7">{{ $laporanBama->disetujuiKpaOleh?->name }}<br><small class="text-muted">{{ $laporanBama->disetujui_kpa_at->format('d/m/Y') }}</small></dd>
                        @endif
                        @if ($laporanBama->dikirim_pusdik_at)
                        <dt class="col-5">Dikirim</dt>
                        <dd class="col-7">
                            {{ $laporanBama->dikirim_pusdik_at->format('d/m/Y') }}
                            @if ($laporanBama->terlambat)
                            <span class="badge bg-danger ms-1">Terlambat</span>
                            @endif
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Tombol Aksi per Role & Status --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">Aksi</div>
                <div class="card-body d-grid gap-2">
                    {{-- Edit (draft saja) --}}
                    @can('laporan_bama.buat')
                    @if ($laporanBama->status === 'draft')
                    <a href="{{ route('laporan-bama.edit', $laporanBama) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Draft
                    </a>
                    @endif
                    @endcan

                    {{-- Setujui Wadir III --}}
                    @can('laporan_bama.setujui')
                    @if ($laporanBama->status === 'draft')
                    <form method="POST" action="{{ route('laporan-bama.setujui-wadir', $laporanBama) }}"
                          onsubmit="return confirm('Setujui laporan ini sebagai Wadir III?')">
                        @csrf
                        <button class="btn btn-outline-info btn-sm w-100">
                            <i class="bi bi-check2-circle me-1"></i>Setujui (Wadir III)
                        </button>
                    </form>
                    @endif
                    @endcan

                    {{-- Setujui KPA --}}
                    @can('laporan_bama.setujui')
                    @if ($laporanBama->status === 'disetujui_wadir')
                    <form method="POST" action="{{ route('laporan-bama.setujui-kpa', $laporanBama) }}"
                          onsubmit="return confirm('Setujui laporan ini sebagai KPA/Direktur?')">
                        @csrf
                        <button class="btn btn-success btn-sm w-100">
                            <i class="bi bi-patch-check me-1"></i>Setujui & Finalisasi (KPA)
                        </button>
                    </form>
                    @endif
                    @endcan

                    {{-- Generate PDF & Word (finalisasi) --}}
                    @can('laporan_bama.finalisasi')
                    @if (in_array($laporanBama->status, ['disetujui_kpa', 'dikirim_pusdik']))
                    <form method="POST" action="{{ route('laporan-bama.generate-pdf', $laporanBama) }}">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm w-100">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Generate & Unduh PDF
                        </button>
                    </form>
                    <a href="{{ route('laporan-bama.download-docx', $laporanBama) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-earmark-word me-1"></i>Unduh Word (.docx)
                    </a>
                    @endif
                    @endcan

                    {{-- Unduh PDF jika sudah ada --}}
                    @if ($laporanBama->file_pdf)
                    <a href="{{ asset('storage/' . $laporanBama->file_pdf) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i>Unduh PDF (Tersimpan)
                    </a>
                    @endif

                    @if ($laporanBama->file_docx)
                    <a href="{{ asset('storage/' . $laporanBama->file_docx) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download me-1"></i>Unduh Word (Tersimpan)
                    </a>
                    @endif

                    {{-- Kirim Pusdik --}}
                    @can('laporan_bama.finalisasi')
                    @if ($laporanBama->status === 'disetujui_kpa')
                    <form method="POST" action="{{ route('laporan-bama.kirim-pusdik', $laporanBama) }}"
                          onsubmit="return confirm('Tandai laporan ini sudah dikirim ke Pusdik KP?')">
                        @csrf
                        <button class="btn btn-dark btn-sm w-100">
                            <i class="bi bi-send-check me-1"></i>Tandai Sudah Dikirim ke Pusdik
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Kolom kanan: Preview Data --}}
        <div class="col-md-8">
            {{-- BAB II --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">BAB II — Ringkasan Eksekutif</div>
                <div class="card-body">
                    <p class="mb-0 small">{{ $laporanBama->ringkasan_eksekutif ?: '(belum diisi)' }}</p>
                </div>
            </div>

            {{-- BAB V Realisasi --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">BAB V — Realisasi Penyaluran</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="small text-muted mb-1">Dalam Kampus</div>
                                <div class="fw-bold text-success fs-6">Rp {{ number_format($data['totalDalamKampus'], 0, ',', '.') }}</div>
                                <small class="text-muted">{{ $data['rekapList']->count() }} taruna</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <div class="small text-muted mb-1">Luar Kampus</div>
                                <div class="fw-bold text-info fs-6">Rp {{ number_format($data['totalLuarKampus'], 0, ',', '.') }}</div>
                                <small class="text-muted">{{ $data['kegiatanLuar']->count() }} kegiatan</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="border border-primary rounded p-2 text-center bg-primary bg-opacity-10">
                                <span class="small text-muted me-2">Total Realisasi:</span>
                                <strong>Rp {{ number_format($data['totalDalamKampus'] + $data['totalLuarKampus'], 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-light"><tr><th>#</th><th>Nama / NIT</th><th>Prodi</th><th>Porsi</th><th>Nilai</th></tr></thead>
                            <tbody>
                                @forelse ($data['rekapList']->take(10) as $i => $r)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $r->taruna?->nama }}<br><small class="text-muted">{{ $r->taruna?->nit }}</small></td>
                                    <td><small>{{ $r->taruna?->prodi }}</small></td>
                                    <td>{{ $r->total_porsi }}</td>
                                    <td>Rp {{ number_format($r->nilai_bantuan, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-2">Belum ada data rekap periode ini</td></tr>
                                @endforelse
                                @if ($data['rekapList']->count() > 10)
                                <tr><td colspan="5" class="text-center text-muted small py-2">... dan {{ $data['rekapList']->count() - 10 }} taruna lainnya (lihat PDF untuk daftar lengkap)</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- BAB VI Permasalahan --}}
            @if (!empty($laporanBama->permasalahan))
            <div class="card mb-3">
                <div class="card-header fw-semibold">BAB VI — Permasalahan dan Tindak Lanjut</div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr><th>#</th><th>Jenis</th><th>Uraian</th><th>Status TL</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($laporanBama->permasalahan as $i => $p)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td><small>{{ $p['jenis'] ?? '-' }}</small></td>
                                <td>{{ $p['uraian'] ?? '-' }}</td>
                                <td>
                                    @php $stl = $p['status_tindak_lanjut'] ?? ''; @endphp
                                    <span class="badge bg-{{ $stl === 'selesai' ? 'success' : ($stl === 'dalam_proses' ? 'warning' : 'secondary') }}">
                                        {{ ucwords(str_replace('_', ' ', $stl)) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- BAB VIII Rekomendasi --}}
            @if (!empty($laporanBama->rekomendasi))
            <div class="card">
                <div class="card-header fw-semibold">BAB VIII — Rekomendasi</div>
                <div class="card-body">
                    <ol class="mb-0">
                        @foreach ($laporanBama->rekomendasi as $rek)
                        @if ($rek)
                        <li class="small mb-1">{{ $rek }}</li>
                        @endif
                        @endforeach
                    </ol>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
