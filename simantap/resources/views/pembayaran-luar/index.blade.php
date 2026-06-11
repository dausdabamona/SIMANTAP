@extends('layouts.app')
@section('title', 'Pembayaran Luar Kampus')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Pembayaran Luar Kampus</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pembayaran Luar Kampus</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="diverifikasi_ppk">Diverifikasi PPK</option>
                        <option value="diajukan_kppn">Diajukan KPPN</option>
                        <option value="sp2d_terbit">SP2D Terbit</option>
                        <option value="transfer_selesai">Transfer Selesai</option>
                        <option value="dikonfirmasi_taruna">Dikonfirmasi Taruna</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Tahap</label>
                    <select id="filterTahap" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="I">I</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="tblPembayaran" class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Kode Kegiatan</th>
                        <th>Nama Kegiatan</th>
                        <th>Tahap</th>
                        <th>Nilai Diajukan</th>
                        <th>Status</th>
                        <th width="80">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
const tbl = $('#tblPembayaran').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('pembayaran-luar.index') }}',
        data: d => {
            d.status = $('#filterStatus').val();
            d.tahap  = $('#filterTahap').val();
        }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'kode_kegiatan' },
        { data: 'nama_kegiatan' },
        { data: 'tahap' },
        { data: 'nilai_fmt', orderable: false },
        { data: 'status_badge', orderable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { url: '/vendor/datatables/id.json' },
    pageLength: 25,
});
$('#filterStatus, #filterTahap').on('change', () => tbl.draw());
</script>
@endpush
