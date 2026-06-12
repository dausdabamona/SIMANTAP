@extends('layouts.app')
@section('title', 'Laporan Taruna')
@section('page_title', 'Laporan Taruna')

@section('content')
<div class="container-fluid">

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-header"><strong>Filter Laporan</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.taruna') }}" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Angkatan</label>
                    <select name="angkatan" class="form-select">
                        <option value="">-- Semua Angkatan --</option>
                        @foreach ($angkatanList as $a)
                            <option value="{{ $a }}" @selected($filter['angkatan'] == $a)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Prodi</label>
                    <select name="prodi" class="form-select">
                        <option value="">-- Semua Prodi --</option>
                        @foreach ($prodiList as $p)
                            <option value="{{ $p }}" @selected($filter['prodi'] == $p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status Taruna</label>
                    <select name="status_taruna" class="form-select">
                        <option value="">-- Semua Status --</option>
                        @foreach (['aktif','cuti','pesiar','sakit_di_kampus','sakit_di_rumah_keluarga','penundaan_studi'] as $s)
                            <option value="{{ $s }}" @selected($filter['status_taruna'] == $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('laporan.taruna') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-bg-success">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_aktif'] }}</div>
                    <div>Total Taruna Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_penerima'] }}</div>
                    <div>Penerima Bantuan</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_tidak_eligible'] }}</div>
                    <div>Tidak Eligible</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_angkatan'] }}</div>
                    <div>Jumlah Angkatan</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Data Taruna ({{ $taruna->count() }} record)</strong>
            <div class="d-flex gap-2">
                <a href="{{ route('laporan.taruna.pdf', request()->query()) }}" class="btn btn-danger btn-sm">PDF</a>
                <a href="{{ route('laporan.taruna.excel', request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th><th>NIT</th><th>Nama</th><th>Angkatan</th>
                            <th>Prodi</th><th>Kelas</th><th>Status</th>
                            <th>Penerima Bantuan</th><th>Eligible</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($taruna as $i => $t)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $t->nit }}</td>
                            <td>{{ $t->nama }}</td>
                            <td>{{ $t->angkatan }}</td>
                            <td>{{ $t->prodi }}</td>
                            <td>{{ $t->kelas }}</td>
                            <td><span class="badge bg-{{ $t->status_taruna === 'aktif' ? 'success' : 'secondary' }}">{{ $t->status_taruna_label }}</span></td>
                            <td class="text-center">{{ $t->penerima_bantuan ? 'Ya' : 'Tidak' }}</td>
                            <td class="text-center">{{ $t->is_eligible_bantuan ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
