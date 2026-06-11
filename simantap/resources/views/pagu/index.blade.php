@extends('layouts.app')
@section('title', 'Pagu Anggaran DIPA')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Pagu Anggaran DIPA</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pagu Anggaran</li>
            </ol></nav>
        </div>
        @can('pagu.create')
        <a href="{{ route('pagu.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Pagu
        </a>
        @endcan
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-muted">Pagu {{ $tahunSekarang }}</div>
                            <div class="fw-bold fs-5 mt-1">Rp {{ number_format($totalPagu, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-muted">Realisasi {{ $tahunSekarang }}</div>
                            <div class="fw-bold fs-5 mt-1">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-muted">Sisa Pagu</div>
                            <div class="fw-bold fs-5 mt-1 {{ $totalPagu - $totalRealisasi < 0 ? 'text-danger' : '' }}">
                                Rp {{ number_format($totalPagu - $totalRealisasi, 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-graph-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="paguTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tahun</th>
                            <th>Akun Belanja</th>
                            <th>Nilai Pagu</th>
                            <th>Realisasi</th>
                            <th>%</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
$('#paguTable').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('pagu.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'tahun' },
        { data: 'akun_belanja' },
        { data: 'nilai_fmt', orderable: false },
        { data: 'realisasi_fmt', orderable: false },
        { data: 'persentase', orderable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    order: [[1, 'desc']],
    language: { search: 'Cari:', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
});
</script>
@endpush
