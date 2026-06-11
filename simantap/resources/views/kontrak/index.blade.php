@extends('layouts.app')
@section('title', 'Kontrak Makan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Kontrak Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kontrak Makan</li>
            </ol></nav>
        </div>
        @can('kontrak.create')
        <a href="{{ route('kontrak.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Buat Kontrak
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="kontrakTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nomor Kontrak</th>
                            <th>Penyedia</th>
                            <th>Tgl Kontrak</th>
                            <th>Nilai Kontrak</th>
                            <th>Harga Porsi</th>
                            <th>Status</th>
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
$('#kontrakTable').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('kontrak.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'nomor_kontrak' },
        { data: 'penyedia_nama' },
        { data: 'tanggal_kontrak' },
        { data: 'nilai_fmt', orderable: false, searchable: false },
        { data: 'harga_porsi' },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
    order: [[3, 'desc']],
});
</script>
@endpush
