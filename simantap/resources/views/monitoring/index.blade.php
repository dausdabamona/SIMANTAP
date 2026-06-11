@extends('layouts.app')
@section('title', 'Monitoring Foto')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Monitoring Foto</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Monitoring</li>
            </ol></nav>
        </div>
        @can('monitoring.create')
        <a href="{{ route('monitoring.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-camera me-1"></i>Upload Foto
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header d-flex align-items-center gap-3">
            <i class="bi bi-images me-1"></i>Daftar Foto Monitoring
            <form method="GET" action="{{ route('monitoring.index') }}" class="d-flex gap-2 ms-auto">
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control form-control-sm" style="width:160px">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('monitoring.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="monitoringTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Tanggal</th>
                            <th>Taruna</th>
                            <th>Foto</th>
                            <th>Waktu Ambil</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<script>
$(function () {
    $('#monitoringTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('monitoring.index') }}',
            data: { tanggal: '{{ $tanggal }}' }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'tanggal', name: 'penerimaan.tanggal' },
            { data: 'taruna', name: 'penerimaan.taruna.nama' },
            { data: 'foto_preview', orderable: false, searchable: false },
            { data: 'captured_at', name: 'captured_at' },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' }
    });
});
</script>
@endpush
