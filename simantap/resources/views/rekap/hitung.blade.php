@extends('layouts.app')
@section('title', 'Hitung Rekap Bulanan')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Hitung Rekap Bulanan</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('rekap.index') }}">Rekap Bulanan</a></li>
            <li class="breadcrumb-item active">Hitung Rekap</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-calculator me-2"></i>Parameter Perhitungan</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('rekap.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
                                <select name="bulan" class="form-select @error('bulan') is-invalid @enderror" required>
                                    @foreach (range(1,12) as $b)
                                        <option value="{{ $b }}" @selected(old('bulan', now()->month) == $b)>
                                            {{ \App\Helpers\DateHelper::namaBulan($b) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                                <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror"
                                    value="{{ old('tahun', now()->year) }}" min="2020" max="2100" required>
                                @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kontrak (referensi harga porsi) <span class="text-danger">*</span></label>
                                <select name="kontrak_id" class="form-select @error('kontrak_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kontrak --</option>
                                    @foreach ($kontrakList as $k)
                                        <option value="{{ $k->id }}" @selected(old('kontrak_id') == $k->id)>
                                            {{ $k->nomor_kontrak }} — Rp {{ number_format($k->harga_porsi, 0, ',', '.') }}/porsi
                                        </option>
                                    @endforeach
                                </select>
                                @error('kontrak_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3 small">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Perhitungan ini akan meng-<em>update or create</em> rekap berdasarkan data penerimaan makan yang berstatus <strong>eligible</strong>.
                            Rekap yang sudah Final tidak akan ditimpa.
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-calculator me-1"></i>Hitung Rekap
                            </button>
                            <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
