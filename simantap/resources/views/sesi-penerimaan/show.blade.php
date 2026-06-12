@extends('layouts.app')
@section('title', 'Detail Sesi — ' . $sesi->sesi_label)

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4 d-flex justify-content-between align-items-start">
        <div>
            <h4>Detail Sesi: {{ $sesi->sesi_label }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('sesi-penerimaan.index', ['tanggal' => $sesi->tanggal->toDateString()]) }}">Penerimaan Makan</a></li>
                <li class="breadcrumb-item active">{{ $sesi->sesi_label }}</li>
            </ol></nav>
        </div>
        <span class="badge bg-{{ $sesi->status_badge_color }} fs-6">{{ $sesi->status_label }}</span>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-6">
            {{-- Info serah terima --}}
            <div class="card mb-4">
                <div class="card-header fw-semibold">Informasi Serah Terima</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted w-50">Tanggal</td><td>{{ $sesi->tanggal->isoFormat('D MMMM Y') }}</td></tr>
                        <tr><td class="text-muted">Waktu Serah Terima</td><td>{{ $sesi->waktu_serah_terima?->format('H:i') ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Diterima Oleh</td><td>{{ $sesi->diterimaOleh?->name ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Kondisi Makanan</td><td>
                            <span class="badge bg-{{ $sesi->kondisi_makanan === 'baik' ? 'success' : ($sesi->kondisi_makanan === 'kurang_baik' ? 'warning' : 'danger') }}">
                                {{ ucfirst(str_replace('_', ' ', $sesi->kondisi_makanan)) }}
                            </span>
                        </td></tr>
                        @if ($sesi->catatan_kondisi)
                        <tr><td class="text-muted">Catatan</td><td>{{ $sesi->catatan_kondisi }}</td></tr>
                        @endif
                        @if ($sesi->lat)
                        <tr><td class="text-muted">Lokasi GPS</td><td class="font-monospace small">{{ $sesi->lat }}, {{ $sesi->lng }}</td></tr>
                        @endif
                    </table>

                    @if ($sesi->foto && count($sesi->foto) > 0)
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach ($sesi->foto as $foto)
                        <a href="{{ Storage::url($foto) }}" target="_blank">
                            <img src="{{ Storage::url($foto) }}" alt="foto"
                                 style="height:80px;width:80px;object-fit:cover;border-radius:4px;">
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            {{-- Panel rekonsiliasi --}}
            <div class="card border-{{ $sesi->rekonsiliasiValid() ? 'success' : 'warning' }} mb-4">
                <div class="card-header fw-semibold bg-{{ $sesi->rekonsiliasiValid() ? 'success' : 'warning' }} bg-opacity-10">
                    Rekonsiliasi Porsi
                    @if ($sesi->rekonsiliasiValid())
                    <i class="bi bi-check-circle-fill text-success ms-2"></i>
                    @else
                    <i class="bi bi-exclamation-triangle-fill text-warning ms-2"></i>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row text-center g-0">
                        <div class="col">
                            <div class="fs-4 fw-bold">{{ $sesi->porsi_diterima ?? '—' }}</div>
                            <div class="small text-muted">Diterima</div>
                        </div>
                        <div class="col-auto d-flex align-items-center px-2 text-muted">=</div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-success">{{ $sesi->porsi_dimakan_taruna ?? 0 }}</div>
                            <div class="small text-muted">Taruna</div>
                        </div>
                        <div class="col-auto d-flex align-items-center px-2 text-muted">+</div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-info">{{ $sesi->porsi_redistribusi }}</div>
                            <div class="small text-muted">Redistribusi</div>
                        </div>
                        <div class="col-auto d-flex align-items-center px-2 text-muted">+</div>
                        <div class="col">
                            <div class="fs-4 fw-bold text-warning">{{ $sesi->porsi_sisa }}</div>
                            <div class="small text-muted">Sisa</div>
                        </div>
                    </div>

                    @if ($sesi->redistribusi_detail)
                    <hr class="my-2">
                    <div class="small text-muted">Detail redistribusi:</div>
                    @foreach ($sesi->redistribusi_detail as $rd)
                    <div class="d-flex justify-content-between small">
                        <span>{{ \App\Models\SesiPenerimaanMakan::KATEGORI_REDISTRIBUSI[$rd['kategori']] ?? $rd['kategori'] }}</span>
                        <span class="fw-semibold">{{ $rd['jumlah'] }} porsi</span>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            <div class="card">
                <div class="card-header fw-semibold">Aksi</div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('kehadiran-makan.index', $sesi) }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-check2-square me-1"></i>Centang Kehadiran Taruna
                    </a>
                    <a href="{{ route('sesi-penerimaan.index', ['tanggal' => $sesi->tanggal->toDateString()]) }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Sesi
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel kehadiran singkat --}}
    @if ($sesi->kehadiranMakan->isNotEmpty())
    <div class="card mt-4">
        <div class="card-header fw-semibold">
            Kehadiran Taruna
            <span class="badge bg-success ms-2">{{ $sesi->kehadiranMakan->where('hadir', true)->count() }} hadir</span>
            <span class="badge bg-danger ms-1">{{ $sesi->kehadiranMakan->where('hadir', false)->count() }} tidak hadir</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>NIT</th><th>Nama</th><th>Kelas</th><th>Hadir</th><th>Sumber</th></tr>
                </thead>
                <tbody>
                    @foreach ($sesi->kehadiranMakan as $k)
                    <tr>
                        <td class="font-monospace small">{{ $k->taruna?->nit }}</td>
                        <td>{{ $k->taruna?->nama }}</td>
                        <td>{{ $k->taruna?->kelas }}</td>
                        <td>
                            @if ($k->hadir)
                            <span class="badge bg-success"><i class="bi bi-check-lg"></i></span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-x-lg"></i></span>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary small">{{ $k->sumber }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
