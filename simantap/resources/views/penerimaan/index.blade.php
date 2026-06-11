@extends('layouts.app')
@section('title', 'Penerimaan Makan')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Penerimaan Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Penerimaan Makan</li>
            </ol></nav>
        </div>
        @can('penerimaan.create')
        <a href="{{ route('penerimaan.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Catat Penerimaan
        </a>
        @endcan
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tanggal</label>
                    <input type="date" id="filterTanggal" class="form-control form-control-sm" value="{{ $tanggal }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Jenis Makan</label>
                    <select id="filterJenis" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="sarapan">Sarapan</option>
                        <option value="makan_siang">Makan Siang</option>
                        <option value="makan_malam">Makan Malam</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="penerimaanTable" class="table table-sm table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>NIT</th>
                            <th>Nama Taruna</th>
                            <th>Jenis Makan</th>
                            <th>Porsi Diterima</th>
                            <th>Eligibilitas</th>
                            <th>GPS</th>
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
let table = $('#penerimaanTable').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('penerimaan.index') }}',
        data: d => {
            d.tanggal     = $('#filterTanggal').val();
            d.jenis_makan = $('#filterJenis').val();
        }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'tanggal' },
        { data: 'nit' },
        { data: 'nama_taruna' },
        { data: 'jenis_makan' },
        { data: 'jumlah_porsi_diterima' },
        { data: 'eligibilitas_badge', orderable: false, searchable: false },
        { data: 'lat', orderable: false, searchable: false,
          render: (d, t, row) => row.lat ? '<i class="bi bi-geo-alt-fill text-success" title="' + row.lat + ',' + row.long + '"></i>' : '–' },
        { data: 'action', orderable: false, searchable: false },
    ],
    order: [[1, 'desc']],
    language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Data _START_–_END_ dari _TOTAL_', paginate: { previous: '‹', next: '›' } },
});
$('#filterTanggal, #filterJenis').on('change', () => table.ajax.reload());
</script>
@endpush
