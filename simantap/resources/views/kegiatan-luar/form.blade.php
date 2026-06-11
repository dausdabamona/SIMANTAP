@extends('layouts.app')
@section('title', $kegiatan ? 'Edit Kegiatan' : 'Tambah Kegiatan Luar Kampus')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $kegiatan ? 'Edit Kegiatan' : 'Tambah Kegiatan Luar Kampus' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kegiatan-luar.index') }}">Kegiatan Luar Kampus</a></li>
            <li class="breadcrumb-item active">{{ $kegiatan ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $kegiatan ? route('kegiatan-luar.update', $kegiatan) : route('kegiatan-luar.store') }}">
                @csrf
                @if ($kegiatan) @method('PUT') @endif

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kegiatan" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                            value="{{ old('nama_kegiatan', $kegiatan?->nama_kegiatan) }}" required>
                        @error('nama_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                        <select name="jenis_kegiatan" class="form-select @error('jenis_kegiatan') is-invalid @enderror" required>
                            <option value="">-- Pilih --</option>
                            @foreach (['pkl' => 'PKL', 'praktek_lapangan' => 'Praktek Lapangan', 'seminar' => 'Seminar', 'kunjungan' => 'Kunjungan', 'lainnya' => 'Lainnya'] as $val => $lbl)
                            <option value="{{ $val }}" @selected(old('jenis_kegiatan', $kegiatan?->jenis_kegiatan) === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('jenis_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                            value="{{ old('tanggal_mulai', $kegiatan?->tanggal_mulai?->format('Y-m-d')) }}" required>
                        @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                            value="{{ old('tanggal_selesai', $kegiatan?->tanggal_selesai?->format('Y-m-d')) }}" required>
                        @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Standar Biaya / Hari (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="standar_biaya_per_hari" class="form-control @error('standar_biaya_per_hari') is-invalid @enderror"
                            value="{{ old('standar_biaya_per_hari', $kegiatan?->standar_biaya_per_hari) }}" min="0" required>
                        @error('standar_biaya_per_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                            value="{{ old('lokasi', $kegiatan?->lokasi) }}" required>
                        @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi', $kegiatan?->deskripsi) }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                    <a href="{{ route('kegiatan-luar.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
