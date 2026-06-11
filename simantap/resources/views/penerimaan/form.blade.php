@extends('layouts.app')
@section('title', $penerimaan ? 'Edit Penerimaan' : 'Catat Penerimaan Makan')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $penerimaan ? 'Edit Penerimaan' : 'Catat Penerimaan Makan' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('penerimaan.index') }}">Penerimaan</a></li>
            <li class="breadcrumb-item active">{{ $penerimaan ? 'Edit' : 'Catat' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-bowl-hot me-2"></i>Data Penerimaan Makan</div>
                <div class="card-body">
                    <form method="POST"
                        action="{{ $penerimaan ? route('penerimaan.update', $penerimaan) : route('penerimaan.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($penerimaan) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $penerimaan?->tanggal?->format('Y-m-d') ?? today()->format('Y-m-d')) }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jenis Makan <span class="text-danger">*</span></label>
                                <select name="jenis_makan" class="form-select @error('jenis_makan') is-invalid @enderror" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="sarapan" @selected(old('jenis_makan', $penerimaan?->jenis_makan) === 'sarapan')>Sarapan</option>
                                    <option value="makan_siang" @selected(old('jenis_makan', $penerimaan?->jenis_makan) === 'makan_siang')>Makan Siang</option>
                                    <option value="makan_malam" @selected(old('jenis_makan', $penerimaan?->jenis_makan) === 'makan_malam')>Makan Malam</option>
                                </select>
                                @error('jenis_makan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Taruna <span class="text-danger">*</span></label>
                                <select name="taruna_id" class="form-select @error('taruna_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Taruna --</option>
                                    @foreach ($tarunaList as $t)
                                        <option value="{{ $t->id }}" @selected(old('taruna_id', $penerimaan?->taruna_id) == $t->id)>
                                            {{ $t->nit }} — {{ $t->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('taruna_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Porsi Diterima <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_porsi_diterima" class="form-control @error('jumlah_porsi_diterima') is-invalid @enderror"
                                    value="{{ old('jumlah_porsi_diterima', $penerimaan?->jumlah_porsi_diterima ?? 1) }}" min="0" max="10" required>
                                @error('jumlah_porsi_diterima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Eligibilitas <span class="text-danger">*</span></label>
                                <select name="status_eligibilitas" class="form-select @error('status_eligibilitas') is-invalid @enderror" required>
                                    <option value="eligible" @selected(old('status_eligibilitas', $penerimaan?->status_eligibilitas ?? 'eligible') === 'eligible')>Eligible</option>
                                    <option value="tidak_eligible" @selected(old('status_eligibilitas', $penerimaan?->status_eligibilitas) === 'tidak_eligible')>Tidak Eligible</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Alasan Pengecualian</label>
                                <input type="text" name="alasan_pengecualian" class="form-control"
                                    value="{{ old('alasan_pengecualian', $penerimaan?->alasan_pengecualian) }}"
                                    placeholder="Isi jika tidak eligible">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Lampiran (PDF/Foto)</label>
                                <input type="file" name="file_lampiran_pengecualian" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            </div>

                            {{-- GPS --}}
                            <div class="col-12"><hr class="my-1"><small class="fw-semibold text-muted">Geotagging (opsional)</small></div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Latitude</label>
                                <input type="number" name="lat" id="latInput" class="form-control"
                                    value="{{ old('lat', $penerimaan?->lat) }}" step="0.00000001" min="-90" max="90">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Longitude</label>
                                <input type="number" name="long" id="longInput" class="form-control"
                                    value="{{ old('long', $penerimaan?->long) }}" step="0.00000001" min="-180" max="180">
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="getLocation">
                                    <i class="bi bi-geo-alt me-1"></i>Ambil Lokasi Saat Ini
                                </button>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $penerimaan ? 'Perbarui' : 'Catat' }}
                            </button>
                            <a href="{{ route('penerimaan.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('getLocation')?.addEventListener('click', () => {
    if (!navigator.geolocation) { alert('Geolokasi tidak didukung.'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('latInput').value  = pos.coords.latitude.toFixed(8);
        document.getElementById('longInput').value = pos.coords.longitude.toFixed(8);
    }, err => alert('Gagal mendapatkan lokasi: ' + err.message));
});
</script>
@endpush
