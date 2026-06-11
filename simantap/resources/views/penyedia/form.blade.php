@extends('layouts.app')
@section('title', $penyedia ? 'Edit Penyedia' : 'Tambah Penyedia')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $penyedia ? 'Edit Penyedia' : 'Tambah Penyedia Makan' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('penyedia.index') }}">Penyedia</a></li>
            <li class="breadcrumb-item active">{{ $penyedia ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-shop me-2"></i>Data Penyedia Makan</div>
                <div class="card-body">
                    <form method="POST" action="{{ $penyedia ? route('penyedia.update', $penyedia) : route('penyedia.store') }}">
                        @csrf
                        @if ($penyedia) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Penyedia / CV / PT <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                    value="{{ old('nama', $penyedia?->nama) }}" required>
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NPWP</label>
                                <input type="text" name="npwp" class="form-control @error('npwp') is-invalid @enderror"
                                    value="{{ old('npwp', $penyedia?->npwp) }}" placeholder="00.000.000.0-000.000">
                                @error('npwp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Telepon</label>
                                <input type="text" name="telp" class="form-control @error('telp') is-invalid @enderror"
                                    value="{{ old('telp', $penyedia?->telp) }}">
                                @error('telp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $penyedia?->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alamat</label>
                                <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="2">{{ old('alamat', $penyedia?->alamat) }}</textarea>
                                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12"><hr><h6 class="text-muted">Data Rekening Bank</h6></div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                                <input type="text" name="bank" class="form-control @error('bank') is-invalid @enderror"
                                    value="{{ old('bank', $penyedia?->bank) }}" placeholder="BRI / BNI / Mandiri ..." required>
                                @error('bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_rekening" class="form-control @error('nomor_rekening') is-invalid @enderror"
                                    value="{{ old('nomor_rekening', $penyedia?->nomor_rekening) }}" required>
                                @error('nomor_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemilik_rekening" class="form-control @error('nama_pemilik_rekening') is-invalid @enderror"
                                    value="{{ old('nama_pemilik_rekening', $penyedia?->nama_pemilik_rekening) }}" required>
                                @error('nama_pemilik_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $penyedia ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <a href="{{ route('penyedia.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
