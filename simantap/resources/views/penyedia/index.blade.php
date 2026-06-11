@extends('layouts.app')
@section('title', 'Penyedia Makan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Penyedia Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Penyedia Makan</li>
            </ol></nav>
        </div>
        @can('penyedia.create')
        <a href="{{ route('penyedia.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Penyedia
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="penyediaTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nama Penyedia</th>
                            <th>NPWP</th>
                            <th>Bank</th>
                            <th>No. Rekening</th>
                            <th>Kontrak Aktif</th>
                            <th width="120">Aksi</th>
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
$('#penyediaTable').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('penyedia.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'nama' },
        { data: 'npwp' },
        { data: 'bank' },
        { data: 'nomor_rekening' },
        { data: 'kontrak_aktif', orderable: false, searchable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
    pageLength: 25,
});
</script>
@endpush
