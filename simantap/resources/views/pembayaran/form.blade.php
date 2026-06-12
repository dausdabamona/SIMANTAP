@extends('layouts.app')
@section('title', 'Buat SPM per Kelas')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Buat Surat Perintah Membayar (SPM) per Kelas</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
            <li class="breadcrumb-item active">Buat SPM</li>
        </ol></nav>
    </div>

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
    <form method="POST" action="{{ route('pembayaran.store') }}" id="formSpm">
        @csrf
        <div class="row g-4">
            {{-- Step 1: Pilih Periode --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-calendar3 me-2"></i>1. Pilih Periode</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Periode <span class="text-danger">*</span></label>
                            <select name="periode_bulan" id="periodeSelect" class="form-select @error('periode_bulan') is-invalid @enderror" required>
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

                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Setiap kelas menghasilkan 1 SPM tersendiri.
                            Tingkat I → Bank BSI, Tingkat II &amp; III → Bank BNI.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: Pilih Kelas --}}
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header fw-semibold"><i class="bi bi-people me-2"></i>2. Pilih Kelas</div>
                    <div class="card-body">
                        <div id="kelasLoading" class="text-muted small d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Memuat data kelas…
                        </div>
                        <div id="kelasPilihInfo" class="text-muted small">
                            Pilih periode terlebih dahulu untuk melihat daftar kelas.
                        </div>

                        <div id="kelasTable" class="d-none">
                            <div class="mb-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnPilihSemua">Pilih Semua</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBatalSemua">Batal Semua</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                            <th>Kelas</th>
                                            <th>Tingkat</th>
                                            <th>Bank</th>
                                            <th>Taruna</th>
                                            <th>Total Nilai</th>
                                            <th>Status SPM</th>
                                        </tr>
                                    </thead>
                                    <tbody id="kelasBody"></tbody>
                                </table>
                            </div>
                            <div id="kelasEmpty" class="text-muted small d-none">Tidak ada kelas dengan rekap final untuk periode ini.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary" id="btnBuat">
                <i class="bi bi-send me-1"></i>Buat SPM
            </button>
            <a href="{{ route('pembayaran.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
const periodeSelect = document.getElementById('periodeSelect');
const periodeTahun  = document.getElementById('periodeTahun');
const kelasBody     = document.getElementById('kelasBody');
const kelasTable    = document.getElementById('kelasTable');
const kelasEmpty    = document.getElementById('kelasEmpty');
const kelasLoading  = document.getElementById('kelasLoading');
const kelasPilihInfo= document.getElementById('kelasPilihInfo');

periodeSelect.addEventListener('change', function () {
    const opt   = this.options[this.selectedIndex];
    const bulan = this.value;
    const tahun = opt.dataset.tahun || '';
    periodeTahun.value = tahun;

    if (!bulan || !tahun) {
        kelasTable.classList.add('d-none');
        kelasPilihInfo.classList.remove('d-none');
        return;
    }

    kelasPilihInfo.classList.add('d-none');
    kelasLoading.classList.remove('d-none');
    kelasTable.classList.add('d-none');

    fetch(`{{ route('pembayaran.kelas-tersedia') }}?periode_bulan=${bulan}&periode_tahun=${tahun}`)
        .then(r => r.json())
        .then(data => {
            kelasLoading.classList.add('d-none');
            kelasBody.innerHTML = '';

            if (data.length === 0) {
                kelasTable.classList.remove('d-none');
                kelasEmpty.classList.remove('d-none');
                return;
            }
            kelasEmpty.classList.add('d-none');
            kelasTable.classList.remove('d-none');

            data.forEach(k => {
                const disabled = k.sudah_ada_spm ? 'disabled' : '';
                const checked  = k.sudah_ada_spm ? '' : 'checked';
                const statusBadge = k.sudah_ada_spm
                    ? '<span class="badge bg-secondary">SPM sudah ada</span>'
                    : '<span class="badge bg-success">Siap dibuat</span>';
                const bankBadge = k.bank_group === 'BSI'
                    ? '<span class="badge bg-success">BSI</span>'
                    : '<span class="badge bg-primary">BNI</span>';

                kelasBody.insertAdjacentHTML('beforeend', `
                    <tr class="${k.sudah_ada_spm ? 'table-secondary text-muted' : ''}">
                        <td><input type="checkbox" name="kelas[]" value="${k.kelas}" class="form-check-input kelas-check" ${checked} ${disabled}></td>
                        <td class="fw-semibold">${k.kelas}</td>
                        <td>Tingkat ${k.tingkat}</td>
                        <td>${bankBadge}</td>
                        <td>${k.jumlah_taruna}</td>
                        <td>Rp ${Number(k.total_nilai).toLocaleString('id-ID')}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `);
            });
        })
        .catch(() => {
            kelasLoading.classList.add('d-none');
            kelasPilihInfo.classList.remove('d-none');
            kelasPilihInfo.textContent = 'Gagal memuat data kelas. Coba lagi.';
        });
});

document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('.kelas-check:not(:disabled)').forEach(c => c.checked = this.checked);
});
document.getElementById('btnPilihSemua').addEventListener('click', () => {
    document.querySelectorAll('.kelas-check:not(:disabled)').forEach(c => c.checked = true);
});
document.getElementById('btnBatalSemua').addEventListener('click', () => {
    document.querySelectorAll('.kelas-check:not(:disabled)').forEach(c => c.checked = false);
});
</script>
@endpush
