@extends('layouts.app')
@section('title', 'Laporan Bulanan BAMA')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Laporan Bulanan BAMA</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Laporan BAMA</li>
            </ol></nav>
        </div>
        @can('laporan_bama.buat')
        <a href="{{ route('laporan-bama.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Buat Laporan
        </a>
        @endcan
    </div>

    @include('components.alert')

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small mb-1">Tahun</label>
                    <select id="filterTahun" class="form-select form-select-sm">
                        @foreach (range(now()->year, 2020) as $y)
                        <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="tblLaporan" class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Dibuat Oleh</th>
                        <th>Dikirim Pusdik</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const tbl = $('#tblLaporan').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('laporan-bama.index') }}',
        data: d => { d.tahun = $('#filterTahun').val(); }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'periode' },
        { data: 'status_badge', orderable: false },
        { data: 'dibuat_oleh' },
        { data: 'dikirim_pusdik_at', render: v => v ? new Date(v).toLocaleDateString('id-ID') : '-' },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { url: '/vendor/datatables/id.json' },
    pageLength: 25,
    order: [[1, 'desc']],
});
$('#filterTahun').on('change', () => tbl.draw());
</script>
@endpush
