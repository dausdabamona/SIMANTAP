@extends('layouts.app')
@section('title', $rekening ? 'Edit Rekening' : 'Tambah Rekening')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $rekening ? 'Edit' : 'Tambah' }} Rekening — {{ $penyedia->nama }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('penyedia.index') }}">Penyedia</a></li>
            <li class="breadcrumb-item"><a href="{{ route('penyedia.show', $penyedia) }}">{{ $penyedia->nama }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('penyedia.rekening.index', $penyedia) }}">Rekening</a></li>
            <li class="breadcrumb-item active">{{ $rekening ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-bank me-2"></i>Data Rekening</div>
                <div class="card-body">
                    @include('components.alert')
                    <form method="POST" action="{{ $rekening
                        ? route('penyedia.rekening.update', [$penyedia, $rekening])
                        : route('penyedia.rekening.store', $penyedia) }}">
                        @csrf
                        @if ($rekening) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Label / Keterangan</label>
                                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                                    value="{{ old('label', $rekening?->label) }}" placeholder="cth: Rekening Utama, Rekening Cadangan">
                                @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bank <span class="text-danger">*</span></label>
                                <input type="text" name="bank" class="form-control @error('bank') is-invalid @enderror"
                                    value="{{ old('bank', $rekening?->bank) }}" placeholder="BRI / BNI / Mandiri ..." required>
                                @error('bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_rekening" class="form-control font-monospace @error('nomor_rekening') is-invalid @enderror"
                                    value="{{ old('nomor_rekening', $rekening?->nomor_rekening) }}" required>
                                @error('nomor_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                                <input type="text" name="nama_pemilik" class="form-control @error('nama_pemilik') is-invalid @enderror"
                                    value="{{ old('nama_pemilik', $rekening?->nama_pemilik) }}" required>
                                @error('nama_pemilik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Berlaku Mulai</label>
                                <input type="date" name="berlaku_mulai" class="form-control @error('berlaku_mulai') is-invalid @enderror"
                                    value="{{ old('berlaku_mulai', $rekening?->berlaku_mulai?->format('Y-m-d')) }}">
                                @error('berlaku_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Berlaku Sampai</label>
                                <input type="date" name="berlaku_sampai" class="form-control @error('berlaku_sampai') is-invalid @enderror"
                                    value="{{ old('berlaku_sampai', $rekening?->berlaku_sampai?->format('Y-m-d')) }}">
                                @error('berlaku_sampai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alasan Perubahan</label>
                                <textarea name="alasan_perubahan" class="form-control @error('alasan_perubahan') is-invalid @enderror" rows="2"
                                    placeholder="Isi jika ini perubahan dari rekening sebelumnya">{{ old('alasan_perubahan', $rekening?->alasan_perubahan) }}</textarea>
                                @error('alasan_perubahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
                                        {{ old('is_active', $rekening?->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">Rekening Aktif</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault"
                                        {{ old('is_default', $rekening?->is_default ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isDefault">Jadikan Rekening Default</label>
                                </div>
                                <small class="text-muted">Rekening default digunakan untuk pembayaran baru.</small>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $rekening ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <a href="{{ route('penyedia.rekening.index', $penyedia) }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
