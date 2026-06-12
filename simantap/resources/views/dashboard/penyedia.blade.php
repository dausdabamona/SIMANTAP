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

    {{-- Section Pembayaran Bulan Berjalan --}}
    @php
        $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                      7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $tBsi = $transferBulanIni['BSI'] ?? null;
        $tBni = $transferBulanIni['BNI'] ?? null;
        $totalTransfer = ($tBsi?->total_nilai ?? 0) + ($tBni?->total_nilai ?? 0);
        $keduaDikonfirmasi = $tBsi?->status === \App\Models\TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA
                          && $tBni?->status === \App\Models\TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA;
    @endphp
    @if ($tBsi || $tBni || $invoiceBulanIni)
    <div class="card mt-3 mb-2">
        <div class="card-header fw-semibold">
            <i class="bi bi-cash-stack me-2 text-success"></i>
            Pembayaran {{ $namaBulan[$bulan] ?? '' }} {{ $tahun }}
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <tbody>
                    <tr>
                        <td class="text-muted ps-3">Transfer BSI</td>
                        <td>@if ($tBsi) Rp {{ number_format($tBsi->total_nilai, 0, ',', '.') }} @else <span class="text-muted">-</span> @endif</td>
                        <td>@if ($tBsi) <span class="badge bg-{{ $tBsi->status_badge_color }} small">{{ $tBsi->status_label }}</span> @endif</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Transfer BNI</td>
                        <td>@if ($tBni) Rp {{ number_format($tBni->total_nilai, 0, ',', '.') }} @else <span class="text-muted">-</span> @endif</td>
                        <td>@if ($tBni) <span class="badge bg-{{ $tBni->status_badge_color }} small">{{ $tBni->status_label }}</span> @endif</td>
                    </tr>
                    <tr class="table-light fw-semibold">
                        <td class="ps-3">Total</td>
                        <td>Rp {{ number_format($totalTransfer, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                    @if ($invoiceBulanIni || $keduaDikonfirmasi)
                    <tr>
                        <td class="ps-3">Invoice</td>
                        <td colspan="2">
                            @if ($invoiceBulanIni)
                                <span class="badge bg-{{ $invoiceBulanIni->status_badge_color }} small">{{ $invoiceBulanIni->status_label }}</span>
                                @if ($invoiceBulanIni->status === \App\Models\InvoicePenyedia::STATUS_MENUNGGU && $keduaDikonfirmasi)
                                <a href="{{ route('invoice-penyedia.show', $invoiceBulanIni) }}" class="btn btn-sm btn-warning ms-2 py-0">
                                    <i class="bi bi-upload me-1"></i>Upload Invoice
                                </a>
                                @endif
                            @elseif ($keduaDikonfirmasi)
                                <span class="text-muted small">Invoice belum dibuat — hubungi administrator.</span>
                            @else
                                <span class="text-muted small">Menunggu kedua transfer dikonfirmasi.</span>
                            @endif
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

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
