@extends('layouts.app')
@section('title', 'Kehadiran Makan — ' . $sesiPenerimaan->sesi_label)

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h4>Kehadiran Makan — {{ $sesiPenerimaan->sesi_label }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item">
                    <a href="{{ route('sesi-penerimaan.index', ['tanggal' => $sesiPenerimaan->tanggal->toDateString()]) }}">Penerimaan Makan</a>
                </li>
                <li class="breadcrumb-item active">Kehadiran</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalFingerprint">
                <i class="bi bi-upload me-1"></i>Import Fingerprint
            </button>
        </div>
    </div>

    @include('components.alert')

    {{-- Panel rekonsiliasi --}}
    <div class="card border-{{ $sesiPenerimaan->rekonsiliasiValid() ? 'success' : 'warning' }} mb-3">
        <div class="card-body py-2">
            <div class="row text-center g-0">
                <div class="col">
                    <div class="fs-5 fw-bold">{{ $sesiPenerimaan->porsi_diterima ?? '—' }}</div>
                    <div class="small text-muted">Diterima</div>
                </div>
                <div class="col-1 d-flex align-items-center justify-content-center text-muted fs-4">=</div>
                <div class="col">
                    <div class="fs-5 fw-bold text-success" id="porsi-taruna">
                        {{ $sesiPenerimaan->porsi_dimakan_taruna ?? 0 }}
                    </div>
                    <div class="small text-muted">Taruna Makan</div>
                </div>
                <div class="col-1 d-flex align-items-center justify-content-center text-muted fs-4">+</div>
                <div class="col">
                    <div class="fs-5 fw-bold text-info">{{ $sesiPenerimaan->porsi_redistribusi }}</div>
                    <div class="small text-muted">Redistribusi</div>
                </div>
                <div class="col-1 d-flex align-items-center justify-content-center text-muted fs-4">+</div>
                <div class="col">
                    <div class="fs-5 fw-bold text-warning" id="porsi-sisa">
                        {{ $sesiPenerimaan->porsi_sisa }}
                    </div>
                    <div class="small text-muted">Sisa</div>
                </div>
                <div class="col-1 d-flex align-items-center justify-content-center">
                    @if ($sesiPenerimaan->rekonsiliasiValid())
                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    @else
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Form centang --}}
    <form method="POST" action="{{ route('kehadiran-makan.centang', $sesiPenerimaan) }}" id="formCentang">
        @csrf

        <div class="card mb-3">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Daftar Taruna ({{ $tarunaList->count() }} orang)</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-xs btn-outline-success btn-sm" onclick="centangSemua(true)">
                        Centang Semua
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger btn-sm" onclick="centangSemua(false)">
                        Hapus Semua
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="checkAll" class="form-check-input" onchange="toggleAll(this)">
                            </th>
                            <th>NIT</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th width="200">Keterangan (jika tidak hadir)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tarunaList->groupBy('kelas') as $kelas => $kelasGroup)
                        <tr class="table-secondary">
                            <td colspan="5" class="fw-semibold small py-1">Kelas {{ $kelas }}</td>
                        </tr>
                        @foreach ($kelasGroup as $t)
                        @php $isHadir = $kehadiranMap->get($t->id, true); @endphp
                        <tr class="{{ $isHadir ? '' : 'table-danger table-danger-subtle' }}">
                            <td>
                                <input type="checkbox"
                                       name="hadir[]"
                                       value="{{ $t->id }}"
                                       class="form-check-input taruna-check"
                                       {{ $isHadir ? 'checked' : '' }}
                                       onchange="updateCount()">
                            </td>
                            <td class="font-monospace small">{{ $t->nit }}</td>
                            <td>{{ $t->nama }}</td>
                            <td><span class="badge bg-secondary small">{{ $t->kelas }}</span></td>
                            <td>
                                <input type="text"
                                       name="keterangan[{{ $t->id }}]"
                                       class="form-control form-control-sm"
                                       placeholder="Alasan tidak hadir"
                                       value="{{ old("keterangan.{$t->id}") }}"
                                       {{ $isHadir ? 'disabled' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="small text-muted">
                    Hadir: <strong id="countHadir" class="text-success">{{ $kehadiranMap->filter()->count() ?: $tarunaList->count() }}</strong>
                    / {{ $tarunaList->count() }} taruna
                </span>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Simpan Kehadiran
                </button>
            </div>
        </div>
    </form>

    {{-- Form redistribusi jika ada sisa --}}
    @if ($sesiPenerimaan->porsi_sisa > 0 && $sesiPenerimaan->status === 'diterima')
    <div class="card border-info mt-3" id="card-redistribusi">
        <div class="card-header bg-info bg-opacity-10">
            <i class="bi bi-arrow-left-right me-1"></i>
            Ada <strong>{{ $sesiPenerimaan->porsi_sisa }} porsi sisa</strong>
            — Catat redistribusi (opsional)
        </div>
        <div class="card-body">
            <form action="{{ route('sesi-penerimaan.simpan-redistribusi', $sesiPenerimaan->id) }}"
                  method="POST" id="form-redistribusi">
                @csrf
                <div id="redistribusi-rows">
                    @foreach(\App\Models\SesiPenerimaanMakan::KATEGORI_REDISTRIBUSI as $key => $label)
                    @php
                        $existing = collect($sesiPenerimaan->redistribusi_detail ?? [])
                            ->firstWhere('kategori', $key);
                    @endphp
                    <div class="row g-2 mb-2 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label mb-0">{{ $label }}</label>
                        </div>
                        <div class="col-md-3">
                            <input type="number"
                                   name="redistribusi[{{ $key }}]"
                                   class="form-control redistribusi-input"
                                   min="0"
                                   max="{{ $sesiPenerimaan->porsi_sisa }}"
                                   value="{{ $existing['jumlah'] ?? 0 }}"
                                   onchange="updateSisaRedistribusi()">
                        </div>
                        <div class="col-md-3 text-muted small">porsi</div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-2 p-2 bg-light rounded small">
                    Total redistribusi: <strong id="total-redistribusi">0</strong> porsi |
                    Sisa akhir: <strong id="sisa-akhir">{{ $sesiPenerimaan->porsi_sisa }}</strong> porsi
                </div>
                <input type="hidden" name="porsi_sisa" id="hidden-porsi-sisa" value="{{ $sesiPenerimaan->porsi_sisa }}">
                <button type="submit" class="btn btn-info btn-sm mt-2">
                    <i class="bi bi-check2 me-1"></i>Simpan Redistribusi
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

{{-- Modal Import Fingerprint --}}
<div class="modal fade" id="modalFingerprint" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST"
                  action="{{ route('kehadiran-makan.fingerprint', $sesiPenerimaan) }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-fingerprint me-1"></i>Import Data Fingerprint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Upload file Excel/CSV dari mesin fingerprint.
                    Kolom pertama: NIT, Kolom kedua: Waktu Scan (opsional).</p>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateCount() {
    const checks = document.querySelectorAll('.taruna-check');
    let hadir = 0;
    checks.forEach(c => {
        const row = c.closest('tr');
        const ket = row.querySelector('input[type=text]');
        if (c.checked) {
            hadir++;
            if (ket) { ket.disabled = true; ket.value = ''; }
            row.classList.remove('table-danger');
        } else {
            if (ket) ket.disabled = false;
            row.classList.add('table-danger');
        }
    });
    document.getElementById('countHadir').textContent = hadir;
}

function toggleAll(master) {
    document.querySelectorAll('.taruna-check').forEach(c => { c.checked = master.checked; });
    updateCount();
}

function centangSemua(val) {
    document.querySelectorAll('.taruna-check').forEach(c => { c.checked = val; });
    document.getElementById('checkAll').checked = val;
    updateCount();
}

function updateSisaRedistribusi() {
    const inputs = document.querySelectorAll('.redistribusi-input');
    let total = 0;
    inputs.forEach(i => total += parseInt(i.value) || 0);
    const porsiSisa = {{ $sesiPenerimaan->porsi_sisa }};
    document.getElementById('total-redistribusi').textContent = total;
    document.getElementById('sisa-akhir').textContent = porsiSisa - total;
    document.getElementById('hidden-porsi-sisa').value = porsiSisa - total;
}

updateCount();
updateSisaRedistribusi();
</script>
@endpush
