@extends('layouts.app')
@section('title', 'Rekap Bulanan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Rekap Bulanan Bantuan Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Rekap Bulanan</li>
            </ol></nav>
        </div>
        @can('rekap.hitung')
        <a href="{{ route('rekap.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-calculator me-1"></i>Hitung Rekap
        </a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Bulan</label>
                    <select id="filterBulan" class="form-select form-select-sm">
                        @foreach (range(1,12) as $b)
                            <option value="{{ $b }}" @selected($b == $bulan)>{{ \App\Helpers\DateHelper::namaBulan($b) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Tahun</label>
                    <input type="number" id="filterTahun" class="form-control form-control-sm" value="{{ $tahun }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="rekapTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Periode</th>
                            <th>NIT</th>
                            <th>Nama Taruna</th>
                            <th>Total Porsi</th>
                            <th>Nilai Bantuan</th>
                            <th>Status</th>
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
let table = $('#rekapTable').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('rekap.index') }}',
        data: d => { d.bulan = $('#filterBulan').val(); d.tahun = $('#filterTahun').val(); }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'periode_label' },
        { data: 'nit' },
        { data: 'nama_taruna' },
        { data: 'total_porsi' },
        { data: 'nilai_fmt', orderable: false },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
});
$('#filterBulan, #filterTahun').on('change', () => table.ajax.reload());
</script>
@endpush
