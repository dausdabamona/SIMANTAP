@extends('layouts.app')
@section('title', 'Detail Pagu Anggaran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Pagu Anggaran {{ $pagu->tahun }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pagu.index') }}">Pagu</a></li>
                <li class="breadcrumb-item active">{{ $pagu->akun_belanja }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pagu.edit', $pagu) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route('pagu.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-wallet2 me-2"></i>Informasi Pagu</div>
            <div class="card-body">
                <dl class="row mb-4">
                    <dt class="col-sm-5">Tahun Anggaran</dt><dd class="col-sm-7 fw-semibold">{{ $pagu->tahun }}</dd>
                    <dt class="col-sm-5">Akun Belanja</dt><dd class="col-sm-7">{{ $pagu->akun_belanja }}</dd>
                    <dt class="col-sm-5">Nilai Pagu</dt><dd class="col-sm-7 fw-semibold">Rp {{ number_format($pagu->nilai_pagu, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Realisasi</dt><dd class="col-sm-7">Rp {{ number_format($realisasi, 0, ',', '.') }}</dd>
                    <dt class="col-sm-5">Sisa Pagu</dt>
                    <dd class="col-sm-7 {{ ($pagu->nilai_pagu - $realisasi) < 0 ? 'text-danger fw-bold' : '' }}">
                        Rp {{ number_format($pagu->nilai_pagu - $realisasi, 0, ',', '.') }}
                    </dd>
                </dl>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span>Realisasi</span><span>{{ $persentase }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar {{ $persentase > 90 ? 'bg-danger' : ($persentase > 70 ? 'bg-warning' : 'bg-success') }}"
                            style="width: {{ min($persentase, 100) }}%"></div>
                    </div>
                </div>
                @if ($pagu->keterangan)
                <p class="text-muted small mb-0">{{ $pagu->keterangan }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
