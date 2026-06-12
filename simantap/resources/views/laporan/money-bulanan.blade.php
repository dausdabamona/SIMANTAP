@extends('layouts.app')
@section('title', 'Ringkasan Keuangan Bulanan')
@section('page_title', 'Ringkasan Keuangan Bulanan')

@section('content')
<div class="container-fluid">

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-header"><strong>Filter</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.money-bulanan') }}" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2020" max="2099">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                    <a href="{{ route('laporan.money-bulanan') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Pagu --}}
    @if ($pagu)
    <div class="alert alert-info">
        Pagu Anggaran {{ $tahun }}: <strong>Rp {{ number_format($pagu->nilai_pagu, 0, ',', '.') }}</strong>
        (Akun: {{ $pagu->akun_belanja }})
    </div>
    @endif

    {{-- Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Ringkasan Keuangan Tahun {{ $tahun }}</strong>
            <a href="{{ route('laporan.money-bulanan.pdf', request()->query()) }}" class="btn btn-danger btn-sm">PDF</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Bulan</th><th>Pengajuan</th><th>Nilai LS</th>
                            <th>Transfer Penyedia</th><th>Invoice Penyedia</th><th>Status BAMA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['bulan'] }}</td>
                            <td>{{ $row['jumlah_pengajuan'] }}</td>
                            <td>Rp {{ number_format($row['total_nilai_pengajuan'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row['total_transfer_penyedia'], 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($row['total_invoice_penyedia'], 0, ',', '.') }}</td>
                            <td>{{ $row['status_laporan_bama'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary fw-bold">
                        <tr>
                            <td>Grand Total</td>
                            <td>{{ collect($rows)->sum('jumlah_pengajuan') }}</td>
                            <td>Rp {{ number_format(collect($rows)->sum('total_nilai_pengajuan'), 0, ',', '.') }}</td>
                            <td>Rp {{ number_format(collect($rows)->sum('total_transfer_penyedia'), 0, ',', '.') }}</td>
                            <td>Rp {{ number_format(collect($rows)->sum('total_invoice_penyedia'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
