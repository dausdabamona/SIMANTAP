@extends('layouts.app')
@section('title', 'Buat Pengajuan Pembayaran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Buat Pengajuan Pembayaran LS</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
            <li class="breadcrumb-item active">Buat Pengajuan</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-file-earmark-medical me-2"></i>Pilih Periode</div>
                <div class="card-body">
                    @if ($periodeTersedia->isEmpty())
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Tidak ada rekap bulanan final yang siap untuk diajukan.
                            Pastikan rekap sudah difinalisasi terlebih dahulu.
                        </div>
                        <a href="{{ route('rekap.index') }}" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-1"></i>Ke Rekap Bulanan
                        </a>
                    @else
                    <form method="POST" action="{{ route('pembayaran.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
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
                            @error('periode_bulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-2"></i>
                            Total nilai akan dihitung otomatis dari rekap bulanan yang sudah final.
                            Alur: PPK → KPA → KPPN → SP2D → Transfer → LPJ.
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i>Buat Pengajuan
                            </button>
                            <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#periodeSelect').on('change', function () {
    $('#periodeTahun').val($(this).find(':selected').data('tahun'));
});
</script>
@endpush
