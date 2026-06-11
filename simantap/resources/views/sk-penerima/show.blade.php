@extends('layouts.app')
@section('title', 'Detail SK Penerima')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Detail SK Penerima</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sk-penerima.index') }}">SK Penerima</a></li>
                <li class="breadcrumb-item active">{{ $sk->nomor_sk }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @can('sk-penerima.edit')
            <a href="{{ route('sk-penerima.edit', $sk) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ route('sk-penerima.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-earmark-ruled me-2"></i>Informasi SK</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nomor SK</dt><dd class="col-sm-8 fw-semibold font-monospace">{{ $sk->nomor_sk }}</dd>
                    <dt class="col-sm-4">Judul</dt><dd class="col-sm-8">{{ $sk->judul }}</dd>
                    <dt class="col-sm-4">Penerbit</dt><dd class="col-sm-8">{{ $sk->penerbit }}</dd>
                    <dt class="col-sm-4">Jenis SK</dt><dd class="col-sm-8">{{ $sk->jenis_sk }}</dd>
                    <dt class="col-sm-4">Tanggal SK</dt><dd class="col-sm-8">{{ $sk->tanggal_sk->format('d F Y') }}</dd>
                    <dt class="col-sm-4">Periode</dt>
                    <dd class="col-sm-8">{{ $sk->periode_mulai->format('d F Y') }} s/d {{ $sk->periode_selesai->format('d F Y') }}</dd>
                    @if ($sk->keterangan)
                    <dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8">{{ $sk->keterangan }}</dd>
                    @endif
                    <dt class="col-sm-4">File SK</dt>
                    <dd class="col-sm-8">
                        @if ($sk->file_sk)
                            <a href="{{ Storage::url($sk->file_sk) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-pdf me-1"></i>Buka File SK
                            </a>
                        @else
                            <span class="text-muted">Tidak ada</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
