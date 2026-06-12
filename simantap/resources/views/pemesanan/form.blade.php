@extends('layouts.app')
@section('title', $pemesanan ? 'Edit Pemesanan' : 'Buat Pemesanan Harian')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $pemesanan ? 'Edit Pemesanan' : 'Buat Pemesanan Harian' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemesanan.index') }}">Pemesanan</a></li>
            <li class="breadcrumb-item active">{{ $pemesanan ? 'Edit' : 'Buat' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-cart-check me-2"></i>Data Pemesanan</div>
                <div class="card-body">
                    <form method="POST" action="{{ $pemesanan ? route('pemesanan.update', $pemesanan) : route('pemesanan.store') }}">
                        @csrf
                        @if ($pemesanan) @method('PATCH') @endif

                        <div class="row g-3">
                            @if (!$pemesanan)
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Pemesanan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                                    value="{{ old('tanggal', $tanggal) }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Pemesanan untuk hari esok (H-1).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kontrak <span class="text-danger">*</span></label>
                                <select name="kontrak_id" class="form-select @error('kontrak_id') is-invalid @enderror" id="kontrakSelect" required>
                                    <option value="">-- Pilih Kontrak --</option>
                                    @foreach ($kontrakList as $k)
                                        <option value="{{ $k->id }}"
                                                data-harga="{{ $k->harga_porsi }}"
                                                data-harga-porsi="{{ $k->harga_porsi }}"
                                            @selected(old('kontrak_id', $kontrakList->count() === 1 ? $k->id : null) == $k->id)>
                                            {{ $k->nomor_kontrak }} — Rp {{ number_format($k->harga_porsi, 0, ',', '.') }}/porsi
                                            (s.d. {{ $k->tanggal_selesai->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('kontrak_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            @else
                            <div class="col-12">
                                <div class="alert alert-secondary small mb-0">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    Tanggal: <strong>{{ $pemesanan->tanggal->format('d F Y') }}</strong>
                                    — Kontrak: <strong>{{ $pemesanan->kontrak?->nomor_kontrak }}</strong>
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jumlah Taruna Hadir <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_taruna_hadir" id="jumlahTaruna"
                                    class="form-control @error('jumlah_taruna_hadir') is-invalid @enderror"
                                    value="{{ old('jumlah_taruna_hadir', $pemesanan?->jumlah_taruna_hadir) }}"
                                    min="0" required>
                                @error('jumlah_taruna_hadir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Harga per Porsi (Rp) <span class="text-danger">*</span></label>
                                @if ($pemesanan)
                                    {{-- Edit mode: tampil saja, tidak bisa diubah --}}
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="text" class="form-control bg-light"
                                               value="{{ number_format($pemesanan->harga_porsi_snapshot, 0, ',', '.') }}"
                                               readonly tabindex="-1">
                                    </div>
                                @else
                                    {{-- Create mode: readonly display + hidden value --}}
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">Rp</span>
                                        <input type="text" id="hargaPorsiDisplay" class="form-control bg-light"
                                               placeholder="Otomatis dari kontrak"
                                               readonly tabindex="-1">
                                    </div>
                                    <input type="hidden" name="harga_porsi_snapshot" id="hargaPorsi"
                                           value="{{ old('harga_porsi_snapshot') }}">
                                @endif
                                <div class="form-text text-muted">
                                    <i class="bi bi-lock-fill me-1"></i>Harga diambil otomatis dari kontrak — tidak dapat diubah.
                                </div>
                                @error('harga_porsi_snapshot')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>

                            {{-- Kalkulasi real-time --}}
                            <div class="col-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <div class="row text-center">
                                            <div class="col">
                                                <div class="small text-muted">Total Porsi</div>
                                                <div class="fw-semibold" id="previewPorsi">0</div>
                                                <div class="small text-muted">(taruna × {{ config('simantap.porsi_per_hari', 3) }} porsi/hari)</div>
                                            </div>
                                            <div class="col border-start">
                                                <div class="small text-muted">Estimasi Nilai</div>
                                                <div class="fw-semibold text-primary" id="previewNilai">Rp 0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan Menu (konfirmasi/perubahan dari jadwal)</label>
                                <textarea name="catatan_menu" class="form-control" rows="2"
                                    placeholder="Isi jika menu berbeda dari jadwal rencana kontrak...">{{ old('catatan_menu', $pemesanan?->catatan_menu) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="menu_sesuai_jadwal" id="menuSesuai" value="1"
                                        @checked(old('menu_sesuai_jadwal', $pemesanan?->menu_sesuai_jadwal ?? true))>
                                    <label class="form-check-label" for="menuSesuai">Menu sesuai jadwal kontrak</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan Internal</label>
                                <textarea name="catatan" class="form-control" rows="1">{{ old('catatan', $pemesanan?->catatan) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $pemesanan ? 'Perbarui' : 'Simpan Pemesanan' }}
                            </button>
                            <a href="{{ route('pemesanan.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-info">
                <div class="card-header bg-info bg-opacity-10 text-info small">
                    <i class="bi bi-info-circle me-1"></i>Alur Pemesanan Harian
                </div>
                <div class="card-body small">
                    <ol class="ps-3 mb-0">
                        <li class="mb-1">Senat membuat pemesanan (H-1)</li>
                        <li class="mb-1">Senat tanda tangan digital</li>
                        <li class="mb-1">Pembina Karakter verifikasi &amp; tanda tangan</li>
                        <li class="mb-1">Senat kirim ke penyedia</li>
                        <li class="mb-1">Penyedia sajikan makanan</li>
                        <li>Penerimaan dicatat + foto monitoring</li>
                    </ol>
                    <hr>
                    <p class="mb-0 text-muted">Perubahan setelah kirim ke penyedia memerlukan <strong>Berita Acara Perubahan</strong>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const porsiPerHari = {{ config('simantap.porsi_per_hari', 3) }};

function updatePreview() {
    const jumlah = parseInt($('#jumlahTaruna').val()) || 0;
    const harga  = parseFloat($('#hargaPorsi').val()) || 0;
    const totalPorsi = jumlah * porsiPerHari;
    const totalNilai = totalPorsi * harga;

    $('#previewPorsi').text(totalPorsi.toLocaleString('id-ID'));
    $('#previewNilai').text('Rp ' + totalNilai.toLocaleString('id-ID'));
}

$('#jumlahTaruna').on('input', updatePreview);

// Auto-fill harga porsi dari kontrak yang dipilih (readonly display + hidden value)
$('#kontrakSelect').on('change', function () {
    const selected = $(this).find(':selected');
    const harga = selected.data('harga-porsi') || selected.data('harga') || 0;
    $('#hargaPorsi').val(harga);
    $('#hargaPorsiDisplay').val(harga ? new Intl.NumberFormat('id-ID').format(harga) : '');
    updatePreview();
});

// Auto-select jika hanya ada satu kontrak aktif
$(document).ready(function () {
    const select = document.getElementById('kontrakSelect');
    if (select && select.options.length === 2) {
        select.selectedIndex = 1;
        $('#kontrakSelect').trigger('change');
    } else if ($('#hargaPorsi').val()) {
        // Restore display on validation error
        const harga = parseFloat($('#hargaPorsi').val()) || 0;
        if (harga) $('#hargaPorsiDisplay').val(new Intl.NumberFormat('id-ID').format(harga));
    }
    updatePreview();
});
</script>
@endpush
