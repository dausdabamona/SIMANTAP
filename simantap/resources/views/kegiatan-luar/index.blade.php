@extends('layouts.app')
@section('title', 'Kegiatan Luar Kampus')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Kegiatan Luar Kampus</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Kegiatan Luar Kampus</li>
            </ol></nav>
        </div>
        @can('kegiatan_luar.buat')
        <a href="{{ route('kegiatan-luar.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Kegiatan
        </a>
        @endcan
    </div>

    @include('components.alert')

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Tahun</label>
                    <select id="filterTahun" class="form-select form-select-sm">
                        @foreach (range(now()->year, 2020) as $y)
                        <option value="{{ $y }}" @selected($y == $tahun)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Status</label>
                    <select id="filterStatus" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="diusulkan_kaprodi">Diusulkan Kaprodi</option>
                        <option value="disetujui_direktur">Disetujui Direktur</option>
                        <option value="menunggu_persetujuan_pusdik">Menunggu Pusdik</option>
                        <option value="disetujui_pusdik">Disetujui Pusdik</option>
                        <option value="proses_pembayaran">Proses Pembayaran</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table id="tblKegiatan" class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Kode</th>
                        <th>Nama Kegiatan</th>
                        <th>Jenis</th>
                        <th>Periode</th>
                        <th>Lokasi</th>
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
const tbl = $('#tblKegiatan').DataTable({
    processing: true, serverSide: true,
    ajax: {
        url: '{{ route('kegiatan-luar.index') }}',
        data: d => {
            d.tahun  = $('#filterTahun').val();
            d.status = $('#filterStatus').val();
        }
    },
    columns: [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'kode_kegiatan' },
        { data: 'nama_kegiatan' },
        { data: 'jenis_kegiatan' },
        { data: 'periode', orderable: false },
        { data: 'lokasi' },
        { data: 'status_badge', orderable: false },
        { data: 'action', orderable: false, searchable: false },
    ],
    language: { url: '/vendor/datatables/id.json' },
    pageLength: 25,
});
$('#filterTahun, #filterStatus').on('change', () => tbl.draw());
</script>
@endpush
