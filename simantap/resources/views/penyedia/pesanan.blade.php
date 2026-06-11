@extends('layouts.app')
@section('title', 'Pesanan Masuk')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">Pesanan Masuk — {{ $penyedia->nama }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.penyedia') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pesanan</li>
        </ol></nav>
    </div>

    @include('components.alert')

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($pesanan as $i => $p)
                    <tr>
                        <td>{{ $pesanan->firstItem() + $i }}</td>
                        <td>{{ $p->tanggal->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge bg-{{ $p->status === 'selesai' ? 'success' : 'warning' }}">
                                {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                            </span>
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('pemesanan.show', $p) }}" class="btn btn-sm btn-outline-info py-0 px-1">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if ($p->status === 'dikirim_penyedia')
                            <form method="POST" action="{{ route('penyedia.pesanan.konfirmasi', $p->id) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-success py-0 px-1">
                                    <i class="bi bi-check2"></i> Konfirmasi
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pesanan masuk</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pesanan->hasPages())
        <div class="card-footer">{{ $pesanan->links() }}</div>
        @endif
    </div>
</div>
@endsection
