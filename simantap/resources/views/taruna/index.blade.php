@extends('layouts.app')

@section('title', 'Data Taruna')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Data Taruna</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Taruna</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('taruna.import')
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="bi bi-upload me-1"></i>Import
            </button>
            @endcan
            @can('taruna.export')
            <a href="{{ route('taruna.export') }}" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Export
            </a>
            @endcan
            @can('taruna.create')
            <a href="{{ route('taruna.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Tambah Taruna
            </a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showDeleted">
                    <label class="form-check-label small" for="showDeleted">Tampilkan data terhapus</label>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tarunaTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>NIT</th>
                            <th>Nama</th>
                            <th>Angkatan</th>
                            <th>Prodi</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Penerima</th>
                            <th>Eligible</th>
                            <th width="130">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('taruna.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import Data Taruna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Unduh <a href="{{ route('taruna.template') }}" class="text-primary">template Excel</a> terlebih dahulu,
                        isi data, kemudian upload.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel/CSV</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Maks. 5 MB. Format: .xlsx, .xls, .csv</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
let table;

function initTable(showDeleted) {
    if (table) table.destroy();

    table = $('#tarunaTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('taruna.index') }}',
            data: { show_deleted: showDeleted ? 1 : 0 },
        },
        columns: [
            { data: 'DT_RowIndex',    orderable: false, searchable: false },
            { data: 'nit' },
            { data: 'nama' },
            { data: 'angkatan' },
            { data: 'prodi' },
            { data: 'kelas' },
            { data: 'status_badge',   orderable: false, searchable: false },
            { data: 'penerima_badge', orderable: false, searchable: false },
            { data: 'eligible_badge', orderable: false, searchable: false },
            { data: 'action',         orderable: false, searchable: false },
        ],
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary"></div>',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            paginate: { previous: '‹', next: '›' },
            emptyTable: 'Tidak ada data',
            zeroRecords: 'Data tidak ditemukan',
        },
        pageLength: 25,
    });
}

initTable(false);

$('#showDeleted').on('change', function () {
    initTable(this.checked);
});
</script>
@endpush
