@extends('layouts.app')

@section('title', 'Detail Taruna')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Detail Taruna</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('taruna.index') }}">Taruna</a></li>
                    <li class="breadcrumb-item active">{{ $taruna->nit }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            @can('taruna.edit')
            <a href="{{ route('taruna.edit', $taruna) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            @endcan
            <a href="{{ route('taruna.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-badge me-2"></i>Identitas Taruna</span>
                    @if ($taruna->is_eligible_bantuan)
                        <span class="badge bg-success">Eligible Bantuan Makan</span>
                    @else
                        <span class="badge bg-danger">Tidak Eligible</span>
                    @endif
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">NIT</dt>
                        <dd class="col-sm-8">{{ $taruna->nit }}</dd>
                        <dt class="col-sm-4">NIK</dt>
                        <dd class="col-sm-8">{{ $taruna->nik ?? '-' }}</dd>
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8 fw-semibold">{{ $taruna->nama }}</dd>
                        <dt class="col-sm-4">Angkatan</dt>
                        <dd class="col-sm-8">{{ $taruna->angkatan }}</dd>
                        <dt class="col-sm-4">Program Studi</dt>
                        <dd class="col-sm-8">{{ $taruna->prodi }}</dd>
                        <dt class="col-sm-4">Kelas</dt>
                        <dd class="col-sm-8">{{ $taruna->kelas }}</dd>
                        <dt class="col-sm-4">Jenis Kelamin</dt>
                        <dd class="col-sm-8">{{ $taruna->jenis_kelamin_label }}</dd>
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @php
                                $statusColor = ['aktif'=>'success','cuti'=>'warning','pesiar'=>'info','sakit_di_kampus'=>'secondary','sakit_di_rumah_keluarga'=>'danger','penundaan_studi'=>'dark'];
                                $color = $statusColor[$taruna->status_taruna] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $taruna->status_taruna_label }}</span>
                        </dd>
                        <dt class="col-sm-4">Penerima Bantuan</dt>
                        <dd class="col-sm-8">
                            @if ($taruna->penerima_bantuan)
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Ya</span>
                            @else
                                <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </dd>
                        <dt class="col-sm-4">Dibuat</dt>
                        <dd class="col-sm-8">{{ $taruna->created_at?->format('d/m/Y H:i') }}</dd>
                        <dt class="col-sm-4">Diperbarui</dt>
                        <dd class="col-sm-8">{{ $taruna->updated_at?->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-bank2 me-2"></i>Rekening Taruna</div>
                <div class="card-body">
                    @if ($taruna->rekening)
                        <dl class="row mb-0 small">
                            <dt class="col-5">Bank</dt>
                            <dd class="col-7">{{ $taruna->rekening->bank }}</dd>
                            <dt class="col-5">No. Rekening</dt>
                            <dd class="col-7 fw-semibold font-monospace">{{ $taruna->rekening->nomor_rekening }}</dd>
                            <dt class="col-5">Atas Nama</dt>
                            <dd class="col-7">{{ $taruna->rekening->nama_pemilik }}</dd>
                        </dl>
                        @can('rekening-taruna.edit')
                        <a href="{{ route('rekening-taruna.edit', $taruna->rekening) }}" class="btn btn-outline-primary btn-sm mt-3 w-100">
                            <i class="bi bi-pencil me-1"></i>Edit Rekening
                        </a>
                        @endcan
                    @else
                        <p class="text-muted small mb-0">Belum ada rekening terdaftar.</p>
                        @can('rekening-taruna.create')
                        <a href="{{ route('rekening-taruna.create') }}?taruna_id={{ $taruna->id }}" class="btn btn-primary btn-sm mt-3 w-100">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Rekening
                        </a>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
