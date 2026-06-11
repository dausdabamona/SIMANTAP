@extends('layouts.app')
@section('title', 'Upload Foto Monitoring')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Upload Foto Monitoring</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring</a></li>
                <li class="breadcrumb-item active">Upload</li>
            </ol></nav>
        </div>
        <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-camera me-2"></i>Form Upload Foto</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('monitoring.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Penerimaan Makan <span class="text-danger">*</span></label>
                            <select name="penerimaan_id" class="form-select @error('penerimaan_id') is-invalid @enderror" required>
                                <option value="">— Pilih Penerimaan —</option>
                                @foreach ($pemesananList as $p)
                                <option value="{{ $p->id }}" {{ old('penerimaan_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->tanggal?->format('d/m/Y') }}
                                    @if($p->taruna) — {{ $p->taruna->nama }} @endif
                                </option>
                                @endforeach
                            </select>
                            @error('penerimaan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Foto <span class="text-danger">*</span></label>
                            <input type="file" name="foto[]" id="fotoInput"
                                class="form-control @error('foto') is-invalid @enderror @error('foto.*') is-invalid @enderror"
                                accept="image/*" multiple required>
                            <div class="form-text">Maks 5 foto, tiap foto maks 5 MB. Format: JPG, PNG, GIF.</div>
                            @error('foto')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('foto.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Preview area --}}
                        <div id="previewArea" class="row g-2 mb-3" style="display:none!important"></div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi GPS</label>
                            <div class="input-group">
                                <input type="text" id="latInput" name="lat" value="{{ old('lat') }}"
                                    class="form-control @error('lat') is-invalid @enderror"
                                    placeholder="Latitude" readonly>
                                <input type="text" id="longInput" name="long" value="{{ old('long') }}"
                                    class="form-control @error('long') is-invalid @enderror"
                                    placeholder="Longitude" readonly>
                                <button type="button" id="getLocation" class="btn btn-outline-secondary">
                                    <i class="bi bi-geo-alt"></i>
                                </button>
                            </div>
                            <div class="form-text" id="locationStatus">Klik ikon untuk mengambil lokasi otomatis.</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i>Upload Foto
                            </button>
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
document.getElementById('fotoInput').addEventListener('change', function () {
    const area = document.getElementById('previewArea');
    area.innerHTML = '';
    if (!this.files.length) { area.style.setProperty('display', 'none', 'important'); return; }
    area.style.removeProperty('display');
    Array.from(this.files).slice(0, 5).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            area.insertAdjacentHTML('beforeend',
                `<div class="col-4 col-md-3"><img src="${e.target.result}" class="img-thumbnail w-100" style="height:100px;object-fit:cover"></div>`);
        };
        reader.readAsDataURL(file);
    });
});

document.getElementById('getLocation')?.addEventListener('click', function () {
    const status = document.getElementById('locationStatus');
    status.textContent = 'Mengambil lokasi...';
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('latInput').value  = pos.coords.latitude.toFixed(8);
            document.getElementById('longInput').value = pos.coords.longitude.toFixed(8);
            status.textContent = 'Lokasi berhasil diambil.';
        },
        err => { status.textContent = 'Gagal mengambil lokasi: ' + err.message; }
    );
});
</script>
@endpush
