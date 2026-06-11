@extends('layouts.app')
@section('title', 'Detail Laporan Monev')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Laporan Monev — {{ $montev->nama_bulan }} {{ $montev->periode_tahun }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('montev.index') }}">Monev</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @can('montev.update')
            <a href="{{ route('montev.edit', $montev) }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            @endcan
            <a href="{{ route('montev.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-clipboard-data me-2"></i>Informasi Laporan</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Periode</dt>
                        <dd class="col-sm-8 fw-semibold">{{ $montev->nama_bulan }} {{ $montev->periode_tahun }}</dd>

                        <dt class="col-sm-4">Dibuat Oleh</dt>
                        <dd class="col-sm-8">{{ $montev->user?->name ?? '–' }}</dd>

                        <dt class="col-sm-4">Tanggal Input</dt>
                        <dd class="col-sm-8">{{ $montev->created_at?->format('d/m/Y H:i') }}</dd>

                        @if ($montev->nilai_gizi_rata)
                        <dt class="col-sm-4">Nilai Gizi Rata-rata</dt>
                        <dd class="col-sm-8">{{ number_format($montev->nilai_gizi_rata, 2) }} kcal/hari</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-list-ul me-2"></i>Menu yang Dievaluasi</div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-line">{{ $montev->menu_dievaluasi }}</p>
                </div>
            </div>

            @if ($montev->catatan_prosedur)
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-journal-text me-2"></i>Catatan Prosedur</div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-line">{{ $montev->catatan_prosedur }}</p>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header"><i class="bi bi-check2-square me-2"></i>Hasil Evaluasi</div>
                <div class="card-body">
                    <p class="mb-0" style="white-space:pre-line">{{ $montev->hasil_evaluasi }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @can('montev.delete')
            <div class="card border-danger">
                <div class="card-header text-danger"><i class="bi bi-trash me-2"></i>Hapus Laporan</div>
                <div class="card-body">
                    <form id="del-montev" method="POST" action="{{ route('montev.destroy', $montev) }}">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-outline-danger w-100"
                            onclick="konfirmasiHapus('del-montev', 'laporan monev ini')">
                            <i class="bi bi-trash me-1"></i>Hapus Laporan Ini
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
