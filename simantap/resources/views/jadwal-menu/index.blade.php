@extends('layouts.app')
@section('title', 'Jadwal Menu Makan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Jadwal Menu Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Jadwal Menu</li>
            </ol></nav>
        </div>
        @can('jadwal-menu.create')
        <a href="{{ route('jadwal-menu.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Jadwal
        </a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Filter Kontrak</label>
                    <select id="filterKontrak" class="form-select form-select-sm">
                        <option value="">Semua Kontrak</option>
                        @foreach ($kontrakList as $k)
                            <option value="{{ $k->id }}">{{ $k->nomor_kontrak }} — {{ $k->penyedia?->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Filter Tanggal</label>
                    <input type="date" id="filterTanggal" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-outline-secondary btn-sm" id="clearFilter">Reset Filter</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="jadwalTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Kontrak</th>
                            <th>Tanggal</th>
                            <th>Jenis Makan</th>
                            <th>Menu</th>
                            <th>Porsi/Taruna</th>
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
let table = $('#jadwalTable').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('jadwal-menu.index') }}',
        data: d => {
            d.kontrak_id = $('#filterKontrak').val();
            d.tanggal    = $('#filterTanggal').val();
        }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'kontrak_nomor' },
        { data: 'tanggal' },
        { data: 'jenis_label', orderable: false },
        { data: 'menu' },
        { data: 'porsi_per_taruna' },
        { data: 'action', orderable: false, searchable: false },
    ],
    order: [[2, 'desc'], [3, 'asc']],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
});

$('#filterKontrak, #filterTanggal').on('change', () => table.ajax.reload());
$('#clearFilter').on('click', () => { $('#filterKontrak').val(''); $('#filterTanggal').val(''); table.ajax.reload(); });
</script>
@endpush
