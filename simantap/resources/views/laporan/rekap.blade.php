@extends('layouts.app')
@section('title', 'Laporan Rekap Bulanan')
@section('page_title', 'Laporan Rekap Bulanan')

@section('content')
<div class="container-fluid">

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-header"><strong>Filter</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.rekap') }}" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        @foreach (['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $num => $nama)
                            <option value="{{ $num }}" @selected($filter['bulan'] == $num)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="{{ $filter['tahun'] }}" min="2020" max="2099">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kontrak</label>
                    <select name="kontrak_id" class="form-select">
                        <option value="">-- Semua Kontrak --</option>
                        @foreach ($kontrakList as $k)
                            <option value="{{ $k->id }}" @selected($filter['kontrak_id'] == $k->id)>{{ $k->nomor_kontrak }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('laporan.rekap') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Rekap Bulanan ({{ $rekaps->count() }} taruna)</strong>
            <div class="d-flex gap-2">
                <a href="{{ route('laporan.rekap.pdf', request()->query()) }}" class="btn btn-danger btn-sm">PDF</a>
                <a href="{{ route('laporan.rekap.excel', request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th><th>NIT</th><th>Nama</th><th>Kelas</th>
                            <th>Total Sesi</th><th>Hari Hadir</th><th>Nilai Bantuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekaps as $i => $r)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r->taruna?->nit ?? '-' }}</td>
                            <td>{{ $r->taruna?->nama ?? '-' }}</td>
                            <td>{{ $r->taruna?->kelas ?? '-' }}</td>
                            <td>{{ $r->total_sesi }}</td>
                            <td>{{ $r->hari_hadir }}</td>
                            <td>Rp {{ number_format($r->nilai_bantuan, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($rekaps->count() > 0)
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Total Nilai Bantuan:</td>
                            <td>Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
