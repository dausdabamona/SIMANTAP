@extends('layouts.app')
@section('title', 'Detail Rekap Bulanan')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Rekap Bulanan — {{ $rekap->taruna?->nama }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('rekap.index') }}">Rekap Bulanan</a></li>
                <li class="breadcrumb-item active">{{ $rekap->periode_label }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="bi bi-table me-2"></i>Data Rekap</span>
                    @php
                        $colors = ['draft'=>'secondary','dihitung_ppk'=>'info','ditandatangani_pembina'=>'primary','ditandatangani_ppk'=>'warning','ditandatangani_kpa'=>'success','final'=>'dark'];
                        $labels = ['draft'=>'Draft','dihitung_ppk'=>'Dihitung PPK','ditandatangani_pembina'=>'TTD Pembina','ditandatangani_ppk'=>'TTD PPK','ditandatangani_kpa'=>'TTD KPA','final'=>'Final'];
                    @endphp
                    <span class="badge bg-{{ $colors[$rekap->status] ?? 'secondary' }}">{{ $labels[$rekap->status] ?? $rekap->status }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Taruna</dt><dd class="col-7 fw-semibold">{{ $rekap->taruna?->nama }}</dd>
                        <dt class="col-5">NIT</dt><dd class="col-7 font-monospace">{{ $rekap->taruna?->nit }}</dd>
                        <dt class="col-5">Periode</dt><dd class="col-7">{{ $rekap->periode_label }}</dd>
                        <dt class="col-5">Total Porsi</dt><dd class="col-7">{{ number_format($rekap->total_porsi) }} porsi</dd>
                        <dt class="col-5">Nilai Bantuan</dt>
                        <dd class="col-7 fw-semibold text-primary fs-6">Rp {{ number_format($rekap->nilai_bantuan, 0, ',', '.') }}</dd>
                        <dt class="col-5">Kontrak Ref.</dt><dd class="col-7">{{ $rekap->kontrak?->nomor_kontrak }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Approval trail --}}
            <div class="card">
                <div class="card-header"><i class="bi bi-check2-all me-2"></i>Riwayat Tanda Tangan</div>
                <div class="card-body">
                    @if ($rekap->approvals->isEmpty())
                        <p class="text-muted small mb-0">Belum ada tanda tangan.</p>
                    @else
                        <div class="timeline">
                            @foreach ($rekap->approvals as $apv)
                            <div class="d-flex mb-3">
                                <div class="me-3 text-success"><i class="bi bi-check-circle-fill"></i></div>
                                <div>
                                    <div class="fw-semibold small">{{ ucfirst(str_replace('_', ' ', $apv->role)) }}</div>
                                    <div class="small">{{ $apv->user?->name }}</div>
                                    <div class="small text-muted">{{ $apv->signed_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($rekap->status !== 'final')
            <div class="card">
                <div class="card-header"><i class="bi bi-pen me-2"></i>Aksi Tanda Tangan</div>
                <div class="card-body d-grid gap-2">
                    @can('rekap.tandatangani_pembina')
                    @if ($rekap->status === 'dihitung_ppk')
                    <form method="POST" action="{{ route('rekap.tandatangan', $rekap) }}">
                        @csrf
                        <input type="hidden" name="role" value="pembina_karakter">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-pen me-1"></i>TTD Pembina Karakter
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('rekap.tandatangani_ppk')
                    @if ($rekap->status === 'ditandatangani_pembina')
                    <form method="POST" action="{{ route('rekap.tandatangan', $rekap) }}">
                        @csrf
                        <input type="hidden" name="role" value="ppk">
                        <button type="submit" class="btn btn-outline-warning w-100">
                            <i class="bi bi-pen me-1"></i>TTD PPK
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('rekap.tandatangani_kpa')
                    @if ($rekap->status === 'ditandatangani_ppk')
                    <form method="POST" action="{{ route('rekap.tandatangan', $rekap) }}">
                        @csrf
                        <input type="hidden" name="role" value="kpa">
                        <button type="submit" class="btn btn-outline-success w-100">
                            <i class="bi bi-pen me-1"></i>TTD KPA
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('rekap.finalize')
                    @if ($rekap->status === 'ditandatangani_kpa')
                    <form method="POST" action="{{ route('rekap.finalize', $rekap) }}">
                        @csrf
                        <button type="submit" class="btn btn-dark w-100">
                            <i class="bi bi-lock me-1"></i>Finalisasi Rekap
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
