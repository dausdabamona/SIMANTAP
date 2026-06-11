@extends('layouts.app')
@section('title', $sk ? 'Edit SK Penerima' : 'Tambah SK Penerima')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $sk ? 'Edit SK Penerima' : 'Tambah SK Penerima Bantuan Makan' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('sk-penerima.index') }}">SK Penerima</a></li>
            <li class="breadcrumb-item active">{{ $sk ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-file-earmark-ruled me-2"></i>Data SK</div>
                <div class="card-body">
                    <form method="POST"
                        action="{{ $sk ? route('sk-penerima.update', $sk) : route('sk-penerima.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($sk) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor SK <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_sk" class="form-control @error('nomor_sk') is-invalid @enderror"
                                    value="{{ old('nomor_sk', $sk?->nomor_sk) }}" required>
                                @error('nomor_sk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis SK <span class="text-danger">*</span></label>
                                <input type="text" name="jenis_sk" class="form-control @error('jenis_sk') is-invalid @enderror"
                                    value="{{ old('jenis_sk', $sk?->jenis_sk) }}" placeholder="Penetapan / Penunjukan / ..." required>
                                @error('jenis_sk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Judul SK <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                                    value="{{ old('judul', $sk?->judul) }}" required>
                                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Penerbit <span class="text-danger">*</span></label>
                                <input type="text" name="penerbit" class="form-control @error('penerbit') is-invalid @enderror"
                                    value="{{ old('penerbit', $sk?->penerbit) }}" required>
                                @error('penerbit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tanggal SK <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_sk" class="form-control @error('tanggal_sk') is-invalid @enderror"
                                    value="{{ old('tanggal_sk', $sk?->tanggal_sk?->format('Y-m-d')) }}" required>
                                @error('tanggal_sk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Periode Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="periode_mulai" class="form-control @error('periode_mulai') is-invalid @enderror"
                                    value="{{ old('periode_mulai', $sk?->periode_mulai?->format('Y-m-d')) }}" required>
                                @error('periode_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Periode Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="periode_selesai" class="form-control @error('periode_selesai') is-invalid @enderror"
                                    value="{{ old('periode_selesai', $sk?->periode_selesai?->format('Y-m-d')) }}" required>
                                @error('periode_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">File SK (PDF)</label>
                                <input type="file" name="file_sk" class="form-control @error('file_sk') is-invalid @enderror" accept=".pdf">
                                @error('file_sk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if ($sk?->file_sk)
                                    <div class="mt-1">
                                        <a href="{{ Storage::url($sk->file_sk) }}" target="_blank" class="small text-primary">
                                            <i class="bi bi-file-pdf me-1"></i>Lihat file saat ini
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $sk?->keterangan) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $sk ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <a href="{{ route('sk-penerima.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
