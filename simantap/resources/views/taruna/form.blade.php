@extends('layouts.app')

@section('title', $taruna ? 'Edit Taruna' : 'Tambah Taruna')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">{{ $taruna ? 'Edit Taruna' : 'Tambah Taruna' }}</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('taruna.index') }}">Taruna</a></li>
                    <li class="breadcrumb-item active">{{ $taruna ? 'Edit' : 'Tambah' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person-badge me-2"></i>
                    {{ $taruna ? 'Edit Data Taruna: ' . $taruna->nama : 'Formulir Tambah Taruna' }}
                </div>
                <div class="card-body">
                    <form method="POST"
                        action="{{ $taruna ? route('taruna.update', $taruna) : route('taruna.store') }}">
                        @csrf
                        @if ($taruna) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIT <span class="text-danger">*</span></label>
                                <input type="text" name="nit" class="form-control @error('nit') is-invalid @enderror"
                                    value="{{ old('nit', $taruna?->nit) }}" placeholder="Nomor Induk Taruna" required>
                                @error('nit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NIK</label>
                                <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                                    value="{{ old('nik', $taruna?->nik) }}" placeholder="16 digit NIK" maxlength="16">
                                @error('nik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                    value="{{ old('nama', $taruna?->nama) }}" placeholder="Nama lengkap taruna" required>
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Angkatan <span class="text-danger">*</span></label>
                                <input type="number" name="angkatan" class="form-control @error('angkatan') is-invalid @enderror"
                                    value="{{ old('angkatan', $taruna?->angkatan) }}" placeholder="2023" min="2000" max="2100" required>
                                @error('angkatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Program Studi <span class="text-danger">*</span></label>
                                <input type="text" name="prodi" class="form-control @error('prodi') is-invalid @enderror"
                                    value="{{ old('prodi', $taruna?->prodi) }}" placeholder="Nama program studi" required>
                                @error('prodi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                                <input type="text" name="kelas" class="form-control @error('kelas') is-invalid @enderror"
                                    value="{{ old('kelas', $taruna?->kelas) }}" placeholder="TPI-1A" required>
                                @error('kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="L" @selected(old('jenis_kelamin', $taruna?->jenis_kelamin) === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('jenis_kelamin', $taruna?->jenis_kelamin) === 'P')>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status Taruna <span class="text-danger">*</span></label>
                                <select name="status_taruna" class="form-select @error('status_taruna') is-invalid @enderror" required>
                                    <option value="">-- Pilih Status --</option>
                                    @foreach([
                                        'aktif'                   => 'Aktif',
                                        'cuti'                    => 'Cuti',
                                        'pesiar'                  => 'Pesiar',
                                        'sakit_di_kampus'         => 'Sakit di Kampus',
                                        'sakit_di_rumah_keluarga' => 'Sakit di Rumah Keluarga',
                                        'penundaan_studi'         => 'Penundaan Studi',
                                    ] as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status_taruna', $taruna?->status_taruna) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status_taruna')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="penerima_bantuan"
                                        id="penerimaSwitch" value="1"
                                        @checked(old('penerima_bantuan', $taruna?->penerima_bantuan ?? true))>
                                    <label class="form-check-label" for="penerimaSwitch">
                                        Penerima Bantuan Makan
                                    </label>
                                </div>
                                <div class="form-text">Taruna yang berhak menerima bantuan makan sesuai SK.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                {{ $taruna ? 'Perbarui Data' : 'Simpan Data' }}
                            </button>
                            <a href="{{ route('taruna.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-info">
                <div class="card-header bg-info bg-opacity-10 text-info">
                    <i class="bi bi-info-circle me-2"></i>Keterangan Status
                </div>
                <div class="card-body small">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><span class="badge bg-success me-1">Aktif</span> Mendapat bantuan makan</li>
                        <li class="mb-2"><span class="badge bg-secondary me-1">Sakit Kampus</span> Mendapat bantuan makan</li>
                        <li class="mb-2"><span class="badge bg-warning me-1">Cuti</span> Tidak mendapat bantuan</li>
                        <li class="mb-2"><span class="badge bg-info me-1">Pesiar</span> Tidak mendapat bantuan</li>
                        <li class="mb-2"><span class="badge bg-danger me-1">Sakit Rumah</span> Tidak mendapat bantuan</li>
                        <li class="mb-0"><span class="badge bg-dark me-1">Penundaan</span> Tidak mendapat bantuan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
