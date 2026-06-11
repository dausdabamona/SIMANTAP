@extends('layouts.app')
@section('title', 'Audit Log')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Audit Log</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Audit Log</li>
        </ol></nav>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info small">
                <i class="bi bi-info-circle me-2"></i>
                Audit trail mencatat semua aktivitas penting dalam sistem.
                Data workflow pembayaran tersimpan di tabel <code>workflow_pembayaran</code>
                dan dapat ditelusuri dari halaman detail setiap pengajuan.
            </div>

            <div class="table-responsive">
                <table id="auditTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Model</th>
                            <th>ID</th>
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
$('#auditTable').DataTable({
    processing: true, serverSide: true,
    ajax: '{{ route('audit-log.index') }}',
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'created_at' },
        { data: 'causer_name' },
        { data: 'description' },
        { data: 'subject_type_short' },
        { data: 'subject_id' },
    ],
    language: { search: 'Cari:', info: 'Data _START_–_END_ dari _TOTAL_', emptyTable: 'Tidak ada log aktivitas.', paginate: { previous: '‹', next: '›' } },
    order: [[1, 'desc']],
});
</script>
@endpush
