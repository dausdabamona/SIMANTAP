@extends('layouts.app')
@section('title', 'Laporan Monitoring & Evaluasi')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Monitoring & Evaluasi</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Monev</li>
            </ol></nav>
        </div>
        @can('montev.create')
        <a href="{{ route('montev.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Laporan
        </a>
        @endcan
    </div>

    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-header d-flex align-items-center gap-3">
            <i class="bi bi-clipboard-data me-1"></i>Daftar Laporan Monev
            <form method="GET" action="{{ route('montev.index') }}" class="d-flex gap-2 ms-auto">
                <select name="tahun" class="form-select form-select-sm" style="width:120px">
                    @foreach (range(now()->year, 2020) as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                <a href="{{ route('montev.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="montevTable" class="table table-hover align-middle mb-0" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Periode</th>
                            <th>Menu Dievaluasi</th>
                            <th>Nilai Gizi Rata-rata</th>
                            <th>Dibuat Oleh</th>
                            <th width="130">Aksi</th>
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
    $('#montevTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('montev.index') }}',
            data: { tahun: '{{ $tahun }}' }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'periode', name: 'periode_bulan' },
            { data: 'menu_dievaluasi' },
            { data: 'nilai_gizi_rata', render: d => d ? d + ' kcal' : '–' },
            { data: 'oleh', name: 'user.name' },
            { data: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' }
    });
});
</script>
@endpush
