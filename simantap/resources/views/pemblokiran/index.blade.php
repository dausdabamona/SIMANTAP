@extends('layouts.app')
@section('title', 'Pemblokiran Uang Makan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Pemblokiran Uang Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Pemblokiran</li>
            </ol></nav>
        </div>
        @can('pemblokiran.create')
        <a href="{{ route('pemblokiran.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Usulkan Pemblokiran
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="pemblokiranTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>NIT</th>
                            <th>Nama Taruna</th>
                            <th>Periode</th>
                            <th>Nilai Bantuan</th>
                            <th>Nilai Didebit</th>
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
$('#pemblokiranTable').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('pemblokiran.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'nit' },
        { data: 'nama_taruna' },
        { data: 'periode_bulan' },
        { data: 'nilai_fmt', orderable: false },
        { data: 'nilai_didebit' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    order: [[0, 'desc']],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
});
</script>
@endpush
