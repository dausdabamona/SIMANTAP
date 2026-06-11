@extends('layouts.app')
@section('title', 'SK Penerima Bantuan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">SK Penerima Bantuan Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">SK Penerima</li>
            </ol></nav>
        </div>
        @can('sk-penerima.create')
        <a href="{{ route('sk-penerima.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah SK
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="skTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nomor SK</th>
                            <th>Judul</th>
                            <th>Penerbit</th>
                            <th>Tanggal SK</th>
                            <th>Periode</th>
                            <th>Jenis</th>
                            <th>File</th>
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
$('#skTable').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('sk-penerima.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'nomor_sk' },
        { data: 'judul' },
        { data: 'penerbit' },
        { data: 'tanggal_sk' },
        { data: 'periode', orderable: false, searchable: false },
        { data: 'jenis_sk' },
        { data: 'file_link', orderable: false, searchable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
    order: [[4, 'desc']],
});
</script>
@endpush
