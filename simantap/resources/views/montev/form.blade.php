@extends('layouts.app')
@section('title', $montev ? 'Edit Laporan Monev' : 'Tambah Laporan Monev')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">{{ $montev ? 'Edit' : 'Tambah' }} Laporan Monev</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('montev.index') }}">Monev</a></li>
                <li class="breadcrumb-item active">{{ $montev ? 'Edit' : 'Tambah' }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('montev.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-clipboard-data me-2"></i>Form Laporan Monev</div>
                <div class="card-body">
                    <form method="POST" action="{{ $montev ? route('montev.update', $montev) : route('montev.store') }}">
                        @csrf
                        @if ($montev) @method('PUT') @endif

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
                                <select name="periode_bulan" class="form-select @error('periode_bulan') is-invalid @enderror" required>
                                    <option value="">— Pilih Bulan —</option>
                                    @foreach (\App\Helpers\DateHelper::daftarBulan() as $no => $nama)
                                    <option value="{{ $no }}" {{ old('periode_bulan', $montev?->periode_bulan) == $no ? 'selected' : '' }}>
                                        {{ $nama }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('periode_bulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                                <input type="number" name="periode_tahun" value="{{ old('periode_tahun', $montev?->periode_tahun ?? now()->year) }}"
                                    class="form-control @error('periode_tahun') is-invalid @enderror"
                                    min="2020" max="2099" required>
                                @error('periode_tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Menu yang Dievaluasi <span class="text-danger">*</span></label>
                            <textarea name="menu_dievaluasi" rows="3"
                                class="form-control @error('menu_dievaluasi') is-invalid @enderror"
                                placeholder="Daftar menu yang dievaluasi selama periode ini..." required>{{ old('menu_dievaluasi', $montev?->menu_dievaluasi) }}</textarea>
                            @error('menu_dievaluasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nilai Gizi Rata-rata (kcal/hari)</label>
                            <input type="number" name="nilai_gizi_rata" value="{{ old('nilai_gizi_rata', $montev?->nilai_gizi_rata) }}"
                                class="form-control @error('nilai_gizi_rata') is-invalid @enderror"
                                step="0.01" min="0" max="100" placeholder="Opsional">
                            @error('nilai_gizi_rata')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan Prosedur</label>
                            <textarea name="catatan_prosedur" rows="3"
                                class="form-control @error('catatan_prosedur') is-invalid @enderror"
                                placeholder="Catatan mengenai prosedur pelaksanaan (opsional)...">{{ old('catatan_prosedur', $montev?->catatan_prosedur) }}</textarea>
                            @error('catatan_prosedur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Hasil Evaluasi <span class="text-danger">*</span></label>
                            <textarea name="hasil_evaluasi" rows="5"
                                class="form-control @error('hasil_evaluasi') is-invalid @enderror"
                                placeholder="Uraian hasil monitoring dan evaluasi..." required>{{ old('hasil_evaluasi', $montev?->hasil_evaluasi) }}</textarea>
                            @error('hasil_evaluasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $montev ? 'Simpan Perubahan' : 'Simpan Laporan' }}
                            </button>
                            <a href="{{ route('montev.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
