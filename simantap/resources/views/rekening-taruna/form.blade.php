@extends('layouts.app')
@section('title', $rekening ? 'Edit Rekening Taruna' : 'Tambah Rekening Taruna')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $rekening ? 'Edit Rekening Taruna' : 'Tambah Rekening Taruna' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rekening-taruna.index') }}">Rekening Taruna</a></li>
            <li class="breadcrumb-item active">{{ $rekening ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-bank2 me-2"></i>Data Rekening Bank</div>
                <div class="card-body">
                    <form method="POST" action="{{ $rekening ? route('rekening-taruna.update', $rekening) : route('rekening-taruna.store') }}">
                        @csrf
                        @if ($rekening) @method('PATCH') @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Taruna <span class="text-danger">*</span></label>
                            <select name="taruna_id" class="form-select @error('taruna_id') is-invalid @enderror" required
                                {{ $rekening ? 'disabled' : '' }}>
                                <option value="">-- Pilih Taruna --</option>
                                @foreach ($tarunaList as $t)
                                    <option value="{{ $t->id }}"
                                        @selected(old('taruna_id', $rekening?->taruna_id ?? $preselect) == $t->id)>
                                        {{ $t->nit }} — {{ $t->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($rekening)
                                <input type="hidden" name="taruna_id" value="{{ $rekening->taruna_id }}">
                            @endif
                            @error('taruna_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" name="bank" class="form-control @error('bank') is-invalid @enderror"
                                value="{{ old('bank', $rekening?->bank) }}" placeholder="BRI / BNI / Mandiri ..." required>
                            @error('bank')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_rekening" class="form-control @error('nomor_rekening') is-invalid @enderror"
                                value="{{ old('nomor_rekening', $rekening?->nomor_rekening) }}" required>
                            @error('nomor_rekening')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pemilik" class="form-control @error('nama_pemilik') is-invalid @enderror"
                                value="{{ old('nama_pemilik', $rekening?->nama_pemilik) }}" required>
                            @error('nama_pemilik')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $rekening ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <a href="{{ route('rekening-taruna.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
