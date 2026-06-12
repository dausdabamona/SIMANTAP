@extends('layouts.app')
@section('title', 'Dashboard Penyedia')
@section('page_title', 'Portal Penyedia Makan')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mb-4 gap-2">
        <span class="badge bg-info fs-6">Penyedia</span>
        <h4 class="mb-0">{{ $penyedia?->nama ?? 'Portal Penyedia' }}</h4>
    </div>

    @unless ($penyedia)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Akun Anda belum terhubung ke data penyedia. Hubungi administrator.
    </div>
    @endunless

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-semibold">Pesanan Hari Ini — {{ now()->format('d F Y') }}</div>
                <div class="card-body">
                    @if ($pesananHariIni)
                    <dl class="row small mb-2">
                        <dt class="col-5">Tanggal</dt><dd class="col-7">{{ $pesananHariIni->tanggal->format('d/m/Y') }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            <span class="badge bg-{{ in_array($pesananHariIni->status, ['selesai']) ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $pesananHariIni->status)) }}
                            </span>
                        </dd>
                    </dl>
                    <a href="{{ route('pemesanan.show', $pesananHariIni) }}" class="btn btn-sm btn-outline-info">
                        <i class="bi bi-eye me-1"></i>Lihat Detail
                    </a>
                    @else
                    <p class="text-muted small mb-0">Belum ada pesanan masuk hari ini.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header fw-semibold">Informasi Penyedia</div>
                <div class="card-body small">
                    @if ($penyedia)
                    <dl class="row mb-0">
                        <dt class="col-5">Nama</dt><dd class="col-7">{{ $penyedia->nama }}</dd>
                        <dt class="col-5">NPWP</dt><dd class="col-7">{{ $penyedia->npwp }}</dd>
                        <dt class="col-5">Bank</dt><dd class="col-7">{{ $penyedia->rekeningDefault?->bank ?? '-' }}</dd>
                        <dt class="col-5">No. Rekening</dt><dd class="col-7">{{ $penyedia->rekeningDefault?->nomor_rekening ?? '-' }}</dd>
                    </dl>
                    @else
                    <p class="text-muted mb-0">Data penyedia tidak ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-4"><a href="{{ route('penyedia.pesanan') }}" class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-cart3 me-1"></i>Pesanan</a></div>
        <div class="col-4"><a href="{{ route('penyedia.invoice') }}" class="btn btn-outline-success btn-sm w-100"><i class="bi bi-file-earmark-check me-1"></i>Invoice</a></div>
        <div class="col-4"><a href="{{ route('penyedia.pembayaran') }}" class="btn btn-outline-info btn-sm w-100"><i class="bi bi-cash-stack me-1"></i>Pembayaran</a></div>
    </div>

    <div class="card mt-2">
        <div class="card-header fw-semibold">Riwayat Pesanan Terbaru</div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($riwayatPesanan as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $p->status === 'selesai' ? 'success' : 'secondary' }}" style="font-size:.7rem">
                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('pemesanan.show', $p) }}" class="btn btn-sm btn-outline-info py-0 px-1">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada riwayat pesanan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
