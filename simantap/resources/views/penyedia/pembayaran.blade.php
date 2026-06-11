@extends('layouts.app')
@section('title', 'Status Pembayaran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Status Pembayaran — {{ $penyedia->nama }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.penyedia') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pembayaran</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Periode</th><th>Total Nilai</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($pembayaranList as $i => $p)
                    <tr>
                        <td>{{ $pembayaranList->firstItem() + $i }}</td>
                        <td>{{ $p->periode_bulan }}/{{ $p->periode_tahun }}</td>
                        <td>Rp {{ number_format($p->total_nilai, 0, ',', '.') }}</td>
                        <td>
                            @php $c = match($p->status) {
                                'sp2d_terbit'=>'warning','transfer_selesai'=>'info',
                                'selesai'=>'success', default=>'secondary'
                            }; @endphp
                            <span class="badge bg-{{ $c }}">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</span>
                        </td>
                        <td>
                            @if ($p->status === 'transfer_selesai')
                            <form method="POST" action="{{ route('penyedia.pembayaran.konfirmasi', $p->id) }}"
                                  onsubmit="return confirm('Konfirmasi transfer sudah diterima?')">
                                @csrf
                                <button class="btn btn-sm btn-outline-success py-0 px-1">
                                    <i class="bi bi-check2-all"></i> Konfirmasi Terima
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Belum ada data pembayaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pembayaranList->hasPages())
        <div class="card-footer">{{ $pembayaranList->links() }}</div>
        @endif
    </div>
</div>
@endsection
