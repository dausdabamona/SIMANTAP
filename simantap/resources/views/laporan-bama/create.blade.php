@extends('layouts.app')
@section('title', $laporan ? 'Edit Laporan BAMA' : 'Buat Laporan BAMA')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $laporan ? 'Edit Laporan BAMA — ' . $laporan->periode_label : 'Buat Laporan BAMA Baru' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('laporan-bama.index') }}">Laporan BAMA</a></li>
            <li class="breadcrumb-item active">{{ $laporan ? 'Edit' : 'Buat' }}</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ $laporan ? route('laporan-bama.update', $laporan) : route('laporan-bama.store') }}">
        @csrf
        @if ($laporan) @method('PUT') @endif

        {{-- Periode (hanya saat create) --}}
        @unless ($laporan)
        <div class="card mb-3">
            <div class="card-header fw-semibold">Periode Laporan</div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bulan <span class="text-danger">*</span></label>
                    <select name="periode_bulan" class="form-select @error('periode_bulan') is-invalid @enderror" required>
                        <option value="">-- Pilih --</option>
                        @php $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']; @endphp
                        @foreach (range(1, 12) as $b)
                        <option value="{{ $b }}" @selected(old('periode_bulan') == $b)>{{ $namaBulan[$b] }}</option>
                        @endforeach
                    </select>
                    @error('periode_bulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun <span class="text-danger">*</span></label>
                    <select name="periode_tahun" class="form-select @error('periode_tahun') is-invalid @enderror" required>
                        @foreach (range(now()->year, 2020) as $y)
                        <option value="{{ $y }}" @selected(old('periode_tahun', now()->year) == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                    @error('periode_tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
        @endunless

        {{-- BAB II: Ringkasan Eksekutif --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold">BAB II — Ringkasan Eksekutif</div>
            <div class="card-body">
                <label class="form-label">Ringkasan Eksekutif</label>
                <textarea name="ringkasan_eksekutif" class="form-control" rows="6"
                    placeholder="Uraian singkat kondisi pelaksanaan bantuan biaya makan, capaian utama, dan kendala periode ini...">{{ old('ringkasan_eksekutif', $laporan?->ringkasan_eksekutif) }}</textarea>
            </div>
        </div>

        {{-- BAB VIII: Rekomendasi (3 poin) --}}
        <div class="card mb-3">
            <div class="card-header fw-semibold">BAB VIII — Rekomendasi (maks. 3 poin)</div>
            <div class="card-body">
                @for ($i = 0; $i < 3; $i++)
                <div class="mb-2">
                    <label class="form-label small mb-1">Rekomendasi {{ $i + 1 }}</label>
                    <input type="text" name="rekomendasi[{{ $i }}]" class="form-control form-control-sm"
                        value="{{ old('rekomendasi.' . $i, $laporan?->rekomendasi[$i] ?? '') }}"
                        placeholder="Rekomendasi poin {{ $i + 1 }}...">
                </div>
                @endfor
            </div>
        </div>

        {{-- BAB VI: Permasalahan --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                BAB VI — Permasalahan dan Tindak Lanjut
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTambahMasalah">
                    <i class="bi bi-plus-lg me-1"></i>Tambah Baris
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0" id="tblMasalah">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis</th>
                                <th>Uraian Permasalahan</th>
                                <th>Dampak</th>
                                <th>Frekuensi</th>
                                <th>Tindak Lanjut</th>
                                <th>Status TL</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="bodyMasalah">
                            @php $permasalahan = old('permasalahan', $laporan?->permasalahan ?? []); @endphp
                            @forelse ($permasalahan as $idx => $p)
                            <tr data-idx="{{ $idx }}">
                                <td><input type="text" name="permasalahan[{{ $idx }}][jenis]" class="form-control form-control-sm" value="{{ $p['jenis'] ?? '' }}" placeholder="Teknis/Adminstrasi..."></td>
                                <td><input type="text" name="permasalahan[{{ $idx }}][uraian]" class="form-control form-control-sm" value="{{ $p['uraian'] ?? '' }}" placeholder="Uraian masalah"></td>
                                <td><input type="text" name="permasalahan[{{ $idx }}][dampak]" class="form-control form-control-sm" value="{{ $p['dampak'] ?? '' }}"></td>
                                <td><input type="text" name="permasalahan[{{ $idx }}][frekuensi]" class="form-control form-control-sm" value="{{ $p['frekuensi'] ?? '' }}" placeholder="Sekali/Berulang"></td>
                                <td><input type="text" name="permasalahan[{{ $idx }}][tindak_lanjut]" class="form-control form-control-sm" value="{{ $p['tindak_lanjut'] ?? '' }}"></td>
                                <td>
                                    <select name="permasalahan[{{ $idx }}][status_tindak_lanjut]" class="form-select form-select-sm">
                                        <option value="dalam_proses" @selected(($p['status_tindak_lanjut'] ?? '') === 'dalam_proses')>Dalam Proses</option>
                                        <option value="selesai" @selected(($p['status_tindak_lanjut'] ?? '') === 'selesai')>Selesai</option>
                                        <option value="belum_ditangani" @selected(($p['status_tindak_lanjut'] ?? '') === 'belum_ditangani')>Belum Ditangani</option>
                                    </select>
                                </td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-masalah py-0"><i class="bi bi-trash"></i></button></td>
                            </tr>
                            @empty
                            <tr id="emptyRow"><td colspan="7" class="text-center text-muted py-3 small">Klik "Tambah Baris" untuk input permasalahan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Simpan Draft
            </button>
            <a href="{{ route('laporan-bama.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let idxMasalah = {{ count(old('permasalahan', $laporan?->permasalahan ?? [])) }};

function rowTemplate(idx) {
    return `<tr data-idx="${idx}">
        <td><input type="text" name="permasalahan[${idx}][jenis]" class="form-control form-control-sm" placeholder="Teknis/Administrasi..."></td>
        <td><input type="text" name="permasalahan[${idx}][uraian]" class="form-control form-control-sm" placeholder="Uraian masalah"></td>
        <td><input type="text" name="permasalahan[${idx}][dampak]" class="form-control form-control-sm"></td>
        <td><input type="text" name="permasalahan[${idx}][frekuensi]" class="form-control form-control-sm" placeholder="Sekali/Berulang"></td>
        <td><input type="text" name="permasalahan[${idx}][tindak_lanjut]" class="form-control form-control-sm"></td>
        <td><select name="permasalahan[${idx}][status_tindak_lanjut]" class="form-select form-select-sm">
            <option value="dalam_proses">Dalam Proses</option>
            <option value="selesai">Selesai</option>
            <option value="belum_ditangani">Belum Ditangani</option>
        </select></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-masalah py-0"><i class="bi bi-trash"></i></button></td>
    </tr>`;
}

$('#btnTambahMasalah').on('click', function () {
    $('#emptyRow').remove();
    $('#bodyMasalah').append(rowTemplate(idxMasalah++));
});

$(document).on('click', '.btn-hapus-masalah', function () {
    $(this).closest('tr').remove();
    if ($('#bodyMasalah tr').length === 0) {
        $('#bodyMasalah').append('<tr id="emptyRow"><td colspan="7" class="text-center text-muted py-3 small">Klik "Tambah Baris" untuk input permasalahan</td></tr>');
    }
});
</script>
@endpush
