@extends('layouts.app')
@section('title', 'Detail Kontrak')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Detail Kontrak Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kontrak.index') }}">Kontrak</a></li>
                <li class="breadcrumb-item active">{{ $kontrak->nomor_kontrak }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @can('kontrak.edit')
            <a href="{{ route('kontrak.edit', $kontrak) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ route('kontrak.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark-text me-2"></i>Informasi Kontrak</span>
                    @php $statusColor = ['draft'=>'secondary','aktif'=>'success','berakhir'=>'warning','dibatalkan'=>'danger']; @endphp
                    <span class="badge bg-{{ $statusColor[$kontrak->status] ?? 'secondary' }}">{{ ucfirst($kontrak->status) }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Nomor Kontrak</dt><dd class="col-sm-7 font-monospace fw-semibold">{{ $kontrak->nomor_kontrak }}</dd>
                        <dt class="col-sm-5">Tanggal Kontrak</dt><dd class="col-sm-7">{{ $kontrak->tanggal_kontrak->format('d F Y') }}</dd>
                        <dt class="col-sm-5">Periode</dt>
                        <dd class="col-sm-7">{{ $kontrak->tanggal_mulai->format('d/m/Y') }} s/d {{ $kontrak->tanggal_selesai->format('d/m/Y') }}</dd>
                        <dt class="col-sm-5">Penyedia</dt><dd class="col-sm-7">{{ $kontrak->penyedia?->nama }}</dd>
                        <dt class="col-sm-5">Pihak Pertama</dt><dd class="col-sm-7">{{ $kontrak->pihak_pertama }}</dd>
                        <dt class="col-sm-5">Nilai Kontrak</dt>
                        <dd class="col-sm-7 fw-semibold">Rp {{ number_format($kontrak->nilai_kontrak, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Harga per Porsi</dt>
                        <dd class="col-sm-7">Rp {{ number_format($kontrak->harga_porsi, 0, ',', '.') }}</dd>
                        @if ($kontrak->catatan)
                        <dt class="col-sm-5">Catatan</dt><dd class="col-sm-7">{{ $kontrak->catatan }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-person-check me-2"></i>Persetujuan PPK</div>
                <div class="card-body">
                    @if ($kontrak->disetujuiPpk)
                        <dl class="row mb-0">
                            <dt class="col-sm-5">PPK</dt><dd class="col-sm-7">{{ $kontrak->disetujuiPpk->name }}</dd>
                            <dt class="col-sm-5">Tanggal Persetujuan</dt>
                            <dd class="col-sm-7">{{ $kontrak->tgl_persetujuan_ppk?->format('d F Y') ?? '-' }}</dd>
                        </dl>
                    @else
                        <p class="text-muted mb-0 small">Belum ada persetujuan PPK.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-paperclip me-2"></i>Dokumen</div>
                <div class="card-body">
                    @foreach ([
                        'file_kontrak'                 => 'Kontrak',
                        'file_addendum'                => 'Addendum',
                        'file_berita_acara_penunjukan' => 'Berita Acara Penunjukan',
                        'file_notulensi_rapat'         => 'Notulensi Rapat',
                    ] as $field => $label)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small">{{ $label }}</span>
                        @if ($kontrak->$field)
                            <a href="{{ Storage::url($kontrak->$field) }}" target="_blank" class="btn btn-xs btn-outline-primary btn-sm">
                                <i class="bi bi-file-pdf me-1"></i>Buka
                            </a>
                        @else
                            <span class="badge bg-light text-muted">Tidak ada</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            @can('kontrak.edit')
            <div class="card mt-4">
                <div class="card-header"><i class="bi bi-arrow-repeat me-2"></i>Ubah Status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('kontrak.status', $kontrak) }}">
                        @csrf @method('PATCH')
                        <select name="status" class="form-select form-select-sm mb-2">
                            @foreach (['draft'=>'Draft','aktif'=>'Aktif','berakhir'=>'Berakhir','dibatalkan'=>'Dibatalkan'] as $val => $lbl)
                                <option value="{{ $val }}" @selected($kontrak->status === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Perbarui Status</button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
