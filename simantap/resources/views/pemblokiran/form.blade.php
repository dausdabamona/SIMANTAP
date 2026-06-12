@extends('layouts.app')
@section('title', $pemblokiran ? 'Edit Pemblokiran' : 'Usulkan Pemblokiran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $pemblokiran ? 'Edit Usulan Pemblokiran' : 'Usulkan Pemblokiran Uang Makan' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemblokiran.index') }}">Pemblokiran</a></li>
            <li class="breadcrumb-item active">{{ $pemblokiran ? 'Edit' : 'Usulkan' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-lock me-2"></i>
                    @if ($pemblokiran)
                        Edit Pemblokiran — <span class="badge bg-info text-dark">{{ $pemblokiran->bank_group }}</span> {{ $pemblokiran->periode_label }}
                    @else
                        Pilih Periode Pemblokiran
                    @endif
                </div>
                <div class="card-body">
                    <form method="POST"
                        action="{{ $pemblokiran ? route('pemblokiran.update', $pemblokiran) : route('pemblokiran.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($pemblokiran) @method('PATCH') @endif

                        @unless ($pemblokiran)
                        {{-- Create mode: periode picker, auto-generate BSI + BNI --}}
                        <div class="alert alert-info small mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            Sistem akan otomatis membuat <strong>2 surat pemblokiran</strong>:
                            satu untuk rekening <strong>BSI</strong> (Tingkat I) dan satu untuk <strong>BNI</strong> (Tingkat II &amp; III).
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                            @if (isset($periodeTersedia) && $periodeTersedia->isNotEmpty())
                            <select name="periode_bulan" class="form-select @error('periode_bulan') is-invalid @enderror" id="periodeSelect" required>
                                <option value="">-- Pilih Periode --</option>
                                @foreach ($periodeTersedia as $p)
                                    <option value="{{ $p->periode_bulan }}" data-tahun="{{ $p->periode_tahun }}"
                                        @selected(old('periode_bulan') == $p->periode_bulan)>
                                        {{ \App\Helpers\DateHelper::namaBulan($p->periode_bulan) }} {{ $p->periode_tahun }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="periode_tahun" id="periodeTahun" value="{{ old('periode_tahun') }}">
                            @else
                            <div class="alert alert-warning">Tidak ada rekap final tersedia.</div>
                            @endif
                            @error('periode_bulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @else
                        {{-- Edit mode: periode read-only --}}
                        <input type="hidden" name="periode_bulan" value="{{ $pemblokiran->periode_bulan }}">
                        <input type="hidden" name="periode_tahun" value="{{ $pemblokiran->periode_tahun }}">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Periode</label>
                            <p class="form-control-plaintext">{{ $pemblokiran->periode_label }}</p>
                        </div>
                        @endunless

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Surat Pemblokiran</label>
                                <input type="text" name="nomor_surat_pemblokiran" class="form-control"
                                    value="{{ old('nomor_surat_pemblokiran', $pemblokiran?->nomor_surat_pemblokiran) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Surat</label>
                                <input type="date" name="tanggal_surat" class="form-control"
                                    value="{{ old('tanggal_surat', $pemblokiran?->tanggal_surat?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">File Surat Pemblokiran (PDF)</label>
                                <input type="file" name="file_surat_pemblokiran" class="form-control" accept=".pdf">
                                @if ($pemblokiran?->file_surat_pemblokiran)
                                    <div class="mt-1">
                                        <a href="{{ Storage::url($pemblokiran->file_surat_pemblokiran) }}" target="_blank" class="small text-primary">
                                            <i class="bi bi-file-pdf me-1"></i>Lihat file saat ini
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $pemblokiran?->catatan) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $pemblokiran ? 'Perbarui' : 'Usulkan Pemblokiran' }}
                            </button>
                            <a href="{{ route('pemblokiran.index') }}" class="btn btn-outline-secondary">Batal</a>
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
const ps = document.getElementById('periodeSelect');
if (ps) {
    ps.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        document.getElementById('periodeTahun').value = opt.dataset.tahun || '';
    });
}
</script>
@endpush
