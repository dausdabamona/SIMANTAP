@extends('layouts.app')
@section('title', $pagu ? 'Edit Pagu' : 'Tambah Pagu Anggaran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $pagu ? 'Edit Pagu Anggaran' : 'Tambah Pagu Anggaran DIPA' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pagu.index') }}">Pagu</a></li>
            <li class="breadcrumb-item active">{{ $pagu ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-wallet2 me-2"></i>Data Pagu Anggaran</div>
                <div class="card-body">
                    <form method="POST" action="{{ $pagu ? route('pagu.update', $pagu) : route('pagu.store') }}">
                        @csrf
                        @if ($pagu) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tahun Anggaran <span class="text-danger">*</span></label>
                                <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror"
                                    value="{{ old('tahun', $pagu?->tahun ?? now()->year) }}" min="2020" max="2100" required>
                                @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Akun Belanja <span class="text-danger">*</span></label>
                                <input type="text" name="akun_belanja" class="form-control @error('akun_belanja') is-invalid @enderror"
                                    value="{{ old('akun_belanja', $pagu?->akun_belanja) }}"
                                    placeholder="521119 / Belanja Bantuan Makan Taruna" required>
                                @error('akun_belanja')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Nilai Pagu (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="nilai_pagu" class="form-control @error('nilai_pagu') is-invalid @enderror"
                                    value="{{ old('nilai_pagu', $pagu?->nilai_pagu) }}" min="0" step="0.01" required>
                                @error('nilai_pagu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Keterangan</label>
                                <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $pagu?->keterangan) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $pagu ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <a href="{{ route('pagu.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
