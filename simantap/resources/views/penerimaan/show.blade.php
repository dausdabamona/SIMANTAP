@extends('layouts.app')
@section('title', 'Detail Penerimaan Makan')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Detail Penerimaan Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penerimaan.index') }}">Penerimaan</a></li>
                <li class="breadcrumb-item active">{{ $penerimaan->tanggal->format('d/m/Y') }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('penerimaan.edit', $penerimaan) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="{{ route('penerimaan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-bowl-hot me-2"></i>Data Penerimaan</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Tanggal</dt><dd class="col-sm-7">{{ $penerimaan->tanggal->format('l, d F Y') }}</dd>
                        <dt class="col-sm-5">Taruna</dt><dd class="col-sm-7 fw-semibold">{{ $penerimaan->taruna?->nama }} ({{ $penerimaan->taruna?->nit }})</dd>
                        <dt class="col-sm-5">Jenis Makan</dt><dd class="col-sm-7">{{ ucfirst(str_replace('_', ' ', $penerimaan->jenis_makan)) }}</dd>
                        <dt class="col-sm-5">Porsi Diterima</dt><dd class="col-sm-7">{{ $penerimaan->jumlah_porsi_diterima }} porsi</dd>
                        <dt class="col-sm-5">Eligibilitas</dt>
                        <dd class="col-sm-7">
                            @if ($penerimaan->status_eligibilitas === 'eligible')
                                <span class="badge bg-success">Eligible</span>
                            @else
                                <span class="badge bg-danger">Tidak Eligible</span>
                                @if ($penerimaan->alasan_pengecualian)
                                <div class="small text-muted mt-1">{{ $penerimaan->alasan_pengecualian }}</div>
                                @endif
                            @endif
                        </dd>
                        @if ($penerimaan->lat)
                        <dt class="col-sm-5">Koordinat GPS</dt>
                        <dd class="col-sm-7 small font-monospace">{{ $penerimaan->lat }}, {{ $penerimaan->long }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
        @if ($penerimaan->foto->isNotEmpty())
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><i class="bi bi-images me-2"></i>Foto Monitoring ({{ $penerimaan->foto->count() }})</div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($penerimaan->foto as $foto)
                        <div class="col-6">
                            <img src="{{ Storage::url($foto->file_foto) }}" class="img-thumbnail w-100" style="height:120px;object-fit:cover" alt="">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
