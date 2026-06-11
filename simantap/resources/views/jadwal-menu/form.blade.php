@extends('layouts.app')
@section('title', $jadwal ? 'Edit Jadwal Menu' : 'Tambah Jadwal Menu')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $jadwal ? 'Edit Jadwal Menu' : 'Tambah Jadwal Menu' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('jadwal-menu.index') }}">Jadwal Menu</a></li>
            <li class="breadcrumb-item active">{{ $jadwal ? 'Edit' : 'Tambah' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-calendar-check me-2"></i>Data Jadwal Menu</div>
                <div class="card-body">
                    <form method="POST" action="{{ $jadwal ? route('jadwal-menu.update', $jadwal) : route('jadwal-menu.store') }}">
                        @csrf
                        @if ($jadwal) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kontrak <span class="text-danger">*</span></label>
                                <select name="kontrak_id" class="form-select @error('kontrak_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kontrak --</option>
                                    @foreach ($kontrakList as $k)
                                        <option value="{{ $k->id }}" @selected(old('kontrak_id', $jadwal?->kontrak_id) == $k->id)>
                                            {{ $k->nomor_kontrak }} — {{ $k->penyedia?->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kontrak_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $jadwal?->tanggal?->format('Y-m-d')) }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Makan <span class="text-danger">*</span></label>
                                <select name="jenis_makan" class="form-select @error('jenis_makan') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="sarapan" @selected(old('jenis_makan', $jadwal?->jenis_makan) === 'sarapan')>Sarapan</option>
                                    <option value="makan_siang" @selected(old('jenis_makan', $jadwal?->jenis_makan) === 'makan_siang')>Makan Siang</option>
                                    <option value="makan_malam" @selected(old('jenis_makan', $jadwal?->jenis_makan) === 'makan_malam')>Makan Malam</option>
                                </select>
                                @error('jenis_makan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Menu <span class="text-danger">*</span></label>
                                <input type="text" name="menu" class="form-control @error('menu') is-invalid @enderror"
                                    value="{{ old('menu', $jadwal?->menu) }}" placeholder="Nasi + Ayam Goreng + Sayur + Buah" required>
                                @error('menu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Porsi/Taruna <span class="text-danger">*</span></label>
                                <input type="number" name="porsi_per_taruna" class="form-control @error('porsi_per_taruna') is-invalid @enderror"
                                    value="{{ old('porsi_per_taruna', $jadwal?->porsi_per_taruna ?? 1) }}" min="1" max="5" required>
                                @error('porsi_per_taruna')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $jadwal?->catatan) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $jadwal ? 'Perbarui' : 'Simpan' }}
                            </button>
                            <a href="{{ route('jadwal-menu.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
