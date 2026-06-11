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

    {{-- Panel 1: KPPN → Rekening Taruna --}}
    <div class="card mb-4">
        <div class="card-header fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-bank text-primary"></i>
            Panel 1 — Transfer KPPN → Rekening Taruna (via Senat)
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Periode</th>
                        <th>Nomor SP2D</th>
                        <th>Tanggal SP2D</th>
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
                        <td><small>{{ $p->nomor_sp2d ?? '-' }}</small></td>
                        <td>{{ $p->tanggal_sp2d?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $p->total_taruna }}</td>
                        <td>Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            @php
                            $c = match($p->status) {
                                'sp2d_terbit' => 'warning', 'transfer_selesai' => 'success',
                                'selesai' => 'dark', default => 'secondary'
                            };
                            @endphp
                            <span class="badge bg-{{ $c }}">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</span>
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
                    <tr><td colspan="8" class="text-center text-muted py-3">Belum ada data transfer KPPN</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transferKppn->hasPages())
        <div class="card-footer">
            {{ $transferKppn->links() }}
        </div>
        @endif
    </div>

    {{-- Panel 2: Transfer Senat → Penyedia --}}
    <div class="card">
        <div class="card-header fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-shop text-success"></i>
            Panel 2 — Transfer Senat → Penyedia Makan
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Periode</th>
                        <th>Nomor SP2D</th>
                        <th>Total Nilai</th>
                        <th>Bukti Transfer</th>
                        <th>Status</th>
                        @role('wadir_iii')<th>Aksi</th>@endrole
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transferPenyedia as $i => $p)
                    <tr>
                        <td>{{ $transferPenyedia->firstItem() + $i }}</td>
                        <td>{{ $p->periode_bulan }}/{{ $p->periode_tahun }}</td>
                        <td><small>{{ $p->nomor_sp2d ?? '-' }}</small></td>
                        <td>Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            @if ($p->bukti_transfer_penyedia)
                            <a href="{{ asset('storage/' . $p->bukti_transfer_penyedia) }}" target="_blank" class="btn btn-xs btn-outline-secondary btn-sm py-0">
                                <i class="bi bi-file-earmark"></i>
                            </a>
                            @else
                            <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td><span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</span></td>
                        @role('wadir_iii')
                        <td>
                            <form method="POST" action="{{ route('transfer-monitor.setujui-penyedia', $p) }}"
                                  onsubmit="return confirm('Setujui transfer ini ke penyedia?')">
                                @csrf
                                <button class="btn btn-xs btn-outline-success btn-sm py-0">
                                    <i class="bi bi-check2"></i> Setujui
                                </button>
                            </form>
                        </td>
                        @endrole
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">Belum ada data transfer ke penyedia</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($transferPenyedia->hasPages())
        <div class="card-footer">
            {{ $transferPenyedia->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
