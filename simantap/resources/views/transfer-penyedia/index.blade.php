@extends('layouts.app')
@section('title', 'Transfer Senat → Penyedia')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Transfer Senat → Penyedia</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Transfer Penyedia</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <select id="filterTahun" class="form-select form-select-sm" style="width:auto">
                @for ($y = now()->year; $y >= 2024; $y--)
                    <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    @include('components.alert')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="transferTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Periode</th>
                            <th>Bank</th>
                            <th>Rekening Senat</th>
                            <th>Rekening Penyedia</th>
                            <th>Kelas</th>
                            <th>Total Nilai</th>
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
const table = $('#transferTable').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('transfer-penyedia.index') }}',
        data: d => { d.tahun = $('#filterTahun').val(); }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'periode_label' },
        { data: 'bank_badge', orderable: false, searchable: false },
        { data: 'rekening_senat', orderable: false },
        { data: 'rekening_penyedia', orderable: false },
        { data: 'jumlah_kelas' },
        { data: 'nilai_fmt', orderable: false },
        { data: 'status_badge', orderable: false, searchable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    order: [[0, 'desc']],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
});
$('#filterTahun').on('change', () => table.ajax.reload());
</script>
@endpush
