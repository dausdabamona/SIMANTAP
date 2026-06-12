@extends('layouts.app')
@section('title', 'Terima Makanan — ' . $sesi->sesi_label)

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4>Serah Terima Makanan — {{ $sesi->sesi_label }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('sesi-penerimaan.index') }}">Penerimaan Makan</a></li>
            <li class="breadcrumb-item active">Terima — {{ $sesi->sesi_label }}</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header fw-semibold">Informasi Sesi</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Tanggal</td><td>{{ $sesi->tanggal->isoFormat('D MMMM Y') }}</td></tr>
                        <tr><td class="text-muted">Sesi</td><td>{{ $sesi->sesi_label }}</td></tr>
                        <tr><td class="text-muted">Porsi Dipesan</td><td><strong>{{ $sesi->porsi_dipesan }} porsi</strong></td></tr>
                        <tr><td class="text-muted">Maks. Diterima</td><td class="text-warning">{{ (int)ceil($sesi->porsi_dipesan * 1.1) }} porsi (+10%)</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header fw-semibold">Form Serah Terima</div>
                <div class="card-body">
                    <form method="POST"
                          action="{{ route('sesi-penerimaan.terima', $sesi) }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jumlah Porsi Diterima <span class="text-danger">*</span></label>
                            <input type="number" name="porsi_diterima" class="form-control @error('porsi_diterima') is-invalid @enderror"
                                   value="{{ old('porsi_diterima', $sesi->porsi_dipesan) }}"
                                   min="0" max="{{ (int)ceil($sesi->porsi_dipesan * 1.1) }}" required>
                            @error('porsi_diterima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kondisi Makanan <span class="text-danger">*</span></label>
                            <select name="kondisi_makanan" class="form-select @error('kondisi_makanan') is-invalid @enderror" required>
                                <option value="baik" {{ old('kondisi_makanan') === 'baik' ? 'selected' : '' }}>✅ Baik</option>
                                <option value="kurang_baik" {{ old('kondisi_makanan') === 'kurang_baik' ? 'selected' : '' }}>⚠️ Kurang Baik</option>
                                <option value="buruk" {{ old('kondisi_makanan') === 'buruk' ? 'selected' : '' }}>❌ Buruk</option>
                            </select>
                            @error('kondisi_makanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Kondisi</label>
                            <textarea name="catatan_kondisi" class="form-control" rows="2"
                                      placeholder="Opsional — jika ada masalah atau catatan">{{ old('catatan_kondisi') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Foto Makanan (maks. 5 foto)</label>
                            <input type="file" name="foto[]" class="form-control" accept="image/jpeg,image/png"
                                   multiple id="fotoInput">
                            <div class="form-text">JPG/PNG, maks 2 MB per foto.</div>
                            <div id="fotoPreview" class="d-flex gap-2 mt-2 flex-wrap"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Lokasi (GPS otomatis)</label>
                            <div class="input-group">
                                <input type="text" id="gpsDisplay" class="form-control form-control-sm" readonly
                                       placeholder="Klik tombol untuk ambil lokasi">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="ambilGps()">
                                    <i class="bi bi-geo-alt"></i> Ambil GPS
                                </button>
                            </div>
                            <input type="hidden" name="lat" id="lat">
                            <input type="hidden" name="lng" id="lng">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check2-circle me-1"></i>Konfirmasi Serah Terima
                            </button>
                            <a href="{{ route('sesi-penerimaan.index', ['tanggal' => $sesi->tanggal->toDateString()]) }}"
                               class="btn btn-outline-secondary">Batal</a>
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
function ambilGps() {
    if (!navigator.geolocation) {
        alert('Browser tidak mendukung geolokasi.');
        return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.getElementById('lat').value = pos.coords.latitude;
        document.getElementById('lng').value = pos.coords.longitude;
        document.getElementById('gpsDisplay').value =
            pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6);
    }, function() {
        alert('Gagal mengambil lokasi.');
    });
}

document.getElementById('fotoInput').addEventListener('change', function() {
    const preview = document.getElementById('fotoPreview');
    preview.innerHTML = '';
    Array.from(this.files).slice(0, 5).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style = 'height:80px;width:80px;object-fit:cover;border-radius:4px;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
