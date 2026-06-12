@extends('layouts.app')
@section('title', 'Monitor Transfer')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Monitor Transfer Dana</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Monitor Transfer</li>
        </ol></nav>
    </div>

    @include('components.alert')

    {{-- Panel 1: KPPN → Rekening Senat (per SPM/kelas) --}}
    <div class="card mb-4">
        <div class="card-header fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-bank text-primary"></i>
            Panel 1 — Transfer KPPN → Rekening Senat (per SPM per Kelas)
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Periode</th>
                        <th>Kelas</th>
                        <th>Bank</th>
                        <th>No. SP2D</th>
                        <th>Total Taruna</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        @role('wadir_iii')<th>Aksi</th>@endrole
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transferKppn as $i => $p)
                    <tr>
                        <td>{{ $transferKppn->firstItem() + $i }}</td>
                        <td>{{ $p->periode_bulan }}/{{ $p->periode_tahun }}</td>
                        <td>{{ $p->kelas ?? '-' }}</td>
                        <td>
                            @if ($p->bank_group)
                            <span class="badge bg-{{ $p->bank_group === 'BSI' ? 'success' : 'primary' }} small">{{ $p->bank_group }}</span>
                            @else
                            -
                            @endif
                        </td>
                        <td><small class="font-monospace">{{ $p->nomor_sp2d ?? '-' }}</small></td>
                        <td>{{ $p->total_taruna }}</td>
                        <td>Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ match($p->status) {
                                'sp2d' => 'info', 'transfer_kppn' => 'primary',
                                'debit_bank' => 'warning', 'debit_selesai' => 'success',
                                'selesai' => 'dark', default => 'secondary'
                            } }} small">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</span>
                        </td>
                        @role('wadir_iii')
                        <td>
                            <form method="POST" action="{{ route('transfer-monitor.mengetahui', $p) }}">
                                @csrf
                                <button class="btn btn-xs btn-outline-info btn-sm py-0">
                                    <i class="bi bi-eye-fill"></i> Mengetahui
                                </button>
                            </form>
                        </td>
                        @endrole
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-3">Belum ada data transfer KPPN</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transferKppn->hasPages())
        <div class="card-footer">{{ $transferKppn->links() }}</div>
        @endif
    </div>

    {{-- Panel 2: Transfer Senat → Penyedia (per bank_group) --}}
    <div class="card mb-4">
        <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
            <span><i class="bi bi-shop text-success me-2"></i>Panel 2 — Transfer Senat → Penyedia Makan ({{ $tahun }})</span>
            <a href="{{ route('transfer-penyedia.index') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-arrow-right me-1"></i>Kelola Transfer
            </a>
        </div>
        <div class="card-body p-0">
            @if ($transferPenyediaSummary->isEmpty())
            <p class="text-muted small p-3 mb-0">Belum ada transfer penyedia tahun {{ $tahun }}.</p>
            @else
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>Bank</th>
                        <th>Rekening Senat</th>
                        <th>Kelas</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        @role('wadir_iii')<th>Aksi</th>@endrole
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transferPenyediaSummary as $bulan => $transfers)
                    @foreach ($transfers as $t)
                    <tr>
                        <td>{{ $bulan }}/{{ $tahun }}</td>
                        <td><span class="badge bg-{{ $t->bank_group === 'BSI' ? 'success' : 'primary' }} small">{{ $t->bank_group }}</span></td>
                        <td><small>{{ $t->senatAccount?->nama_akun ?? '-' }}</small></td>
                        <td>{{ $t->jumlah_kelas }} kelas</td>
                        <td>Rp {{ number_format($t->total_nilai, 0, ',', '.') }}</td>
                        <td><span class="badge bg-{{ $t->status_badge_color }} small">{{ $t->status_label }}</span></td>
                        @role('wadir_iii')
                        <td>
                            @if ($t->status === \App\Models\TransferPenyedia::STATUS_MENUNGGU)
                            <form method="POST" action="{{ route('transfer-penyedia.setujui-wadir', $t) }}"
                                  onsubmit="return confirm('Setujui transfer {{ $t->bank_group }} {{ $bulan }}/{{ $tahun }}?')">
                                @csrf
                                <button class="btn btn-xs btn-warning btn-sm py-0">
                                    <i class="bi bi-check2"></i> Setujui
                                </button>
                            </form>
                            @else
                            <a href="{{ route('transfer-penyedia.show', $t) }}" class="btn btn-xs btn-outline-info btn-sm py-0">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </td>
                        @endrole
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Panel 3: Invoice Penyedia --}}
    <div class="card">
        <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
            <span><i class="bi bi-file-earmark-check text-warning me-2"></i>Panel 3 — Invoice Penyedia ({{ $tahun }})</span>
            <a href="{{ route('invoice-penyedia.index') }}" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-arrow-right me-1"></i>Kelola Invoice
            </a>
        </div>
        <div class="card-body p-0">
            @if ($invoices->isEmpty())
            <p class="text-muted small p-3 mb-0">Belum ada invoice penyedia tahun {{ $tahun }}.</p>
            @else
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>Penyedia</th>
                        <th>No. Invoice</th>
                        <th>Total Nilai</th>
                        <th>Status</th>
                        @can('pembayaran.lpj')<th>Aksi</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $inv)
                    <tr>
                        <td>{{ $inv->periode_bulan }}/{{ $inv->periode_tahun }}</td>
                        <td>{{ $inv->penyedia?->nama ?? '-' }}</td>
                        <td class="font-monospace small">{{ $inv->nomor_invoice ?? '-' }}</td>
                        <td>Rp {{ number_format($inv->total_nilai, 0, ',', '.') }}</td>
                        <td><span class="badge bg-{{ $inv->status_badge_color }} small">{{ $inv->status_label }}</span></td>
                        @can('pembayaran.lpj')
                        <td>
                            @if ($inv->status === \App\Models\InvoicePenyedia::STATUS_DITERIMA)
                            <form method="POST" action="{{ route('invoice-penyedia.verifikasi', $inv) }}"
                                  onsubmit="return confirm('Verifikasi invoice ini?')">
                                @csrf
                                <button class="btn btn-xs btn-success btn-sm py-0">
                                    <i class="bi bi-check2-circle"></i> Verifikasi
                                </button>
                            </form>
                            @else
                            <a href="{{ route('invoice-penyedia.show', $inv) }}" class="btn btn-xs btn-outline-info btn-sm py-0">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endif
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection
