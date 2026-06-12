@extends('layouts.app')
@section('title', 'Laporan Monitoring Sesi')
@section('page_title', 'Laporan Monitoring Sesi Penerimaan Makan')

@section('content')
<div class="container-fluid">

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-header"><strong>Filter</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.monitoring') }}" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Dari</label>
                    <input type="date" name="tanggal_dari" class="form-control" value="{{ $filter['tanggal_dari'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control" value="{{ $filter['tanggal_sampai'] }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sesi</label>
                    <select name="sesi" class="form-select">
                        <option value="semua" @selected(!$filter['sesi'] || $filter['sesi'] === 'semua')>Semua</option>
                        <option value="sarapan" @selected($filter['sesi'] === 'sarapan')>Sarapan</option>
                        <option value="siang" @selected($filter['sesi'] === 'siang')>Siang</option>
                        <option value="malam" @selected($filter['sesi'] === 'malam')>Malam</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('laporan.monitoring') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card text-bg-primary">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_sesi'] }}</div>
                    <div>Total Sesi</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_diterima'] }}</div>
                    <div>Diterima</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-danger">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['total_masalah'] }}</div>
                    <div>Ada Masalah</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info">
                <div class="card-body">
                    <div class="fs-4 fw-bold">{{ $stats['pct_rekonsiliasi_valid'] }}%</div>
                    <div>Rekonsiliasi Valid</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Data Sesi Penerimaan Makan</strong>
            <div class="d-flex gap-2">
                <a href="{{ route('laporan.monitoring.pdf', request()->query()) }}" class="btn btn-danger btn-sm">PDF</a>
                <a href="{{ route('laporan.monitoring.excel', request()->query()) }}" class="btn btn-success btn-sm">Excel</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Tanggal</th><th>Sesi</th><th>Dipesan</th><th>Diterima</th>
                            <th>Taruna</th><th>Redistribusi</th><th>Sisa</th><th>Rekonsiliasi</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sesis as $s)
                        <tr>
                            <td>{{ $s->tanggal?->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($s->sesi) }}</td>
                            <td>{{ $s->porsi_dipesan }}</td>
                            <td>{{ $s->porsi_diterima }}</td>
                            <td>{{ $s->porsi_dimakan_taruna }}</td>
                            <td>{{ $s->porsi_redistribusi }}</td>
                            <td>{{ $s->porsi_sisa }}</td>
                            <td class="text-center">
                                @if ($s->rekonsiliasiValid())
                                    <span class="text-success fw-bold">&#10003;</span>
                                @else
                                    <span class="text-danger">&#10007;</span>
                                @endif
                            </td>
                            <td><span class="badge bg-{{ $s->status_badge_color }}">{{ $s->status_label }}</span></td>
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
