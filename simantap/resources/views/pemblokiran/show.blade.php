@extends('layouts.app')
@section('title', 'Detail Pemblokiran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Detail Pemblokiran Uang Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pemblokiran.index') }}">Pemblokiran</a></li>
                <li class="breadcrumb-item active">{{ $pemblokiran->taruna?->nama }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('pemblokiran.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="bi bi-lock me-2"></i>Data Pemblokiran</span>
                    @php
                        $colors = ['diusulkan'=>'warning','diblokir'=>'info','didebit'=>'success'];
                        $labels = ['diusulkan'=>'Diusulkan','diblokir'=>'Diblokir','didebit'=>'Didebit'];
                    @endphp
                    <span class="badge bg-{{ $colors[$pemblokiran->status] ?? 'secondary' }}">{{ $labels[$pemblokiran->status] ?? $pemblokiran->status }}</span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Taruna</dt><dd class="col-sm-7 fw-semibold">{{ $pemblokiran->taruna?->nama }}</dd>
                        <dt class="col-sm-5">NIT</dt><dd class="col-sm-7 font-monospace">{{ $pemblokiran->taruna?->nit }}</dd>
                        <dt class="col-sm-5">Periode</dt>
                        <dd class="col-sm-7">{{ \App\Helpers\DateHelper::namaBulan($pemblokiran->periode_bulan) }} {{ $pemblokiran->periode_tahun }}</dd>
                        <dt class="col-sm-5">Nilai Bantuan</dt>
                        <dd class="col-sm-7 fw-semibold">Rp {{ number_format($pemblokiran->nilai_bantuan, 0, ',', '.') }}</dd>
                        <dt class="col-sm-5">Rekening Senat</dt>
                        <dd class="col-sm-7">{{ $pemblokiran->senatAccount?->nama_rekening }} — {{ $pemblokiran->senatAccount?->nomor_rekening }}</dd>
                        <dt class="col-sm-5">No. Surat</dt><dd class="col-sm-7">{{ $pemblokiran->nomor_surat_pemblokiran ?? '-' }}</dd>
                        <dt class="col-sm-5">Tanggal Surat</dt><dd class="col-sm-7">{{ $pemblokiran->tanggal_surat?->format('d F Y') ?? '-' }}</dd>
                        @if ($pemblokiran->tanggal_debit)
                        <dt class="col-sm-5">Tanggal Debit</dt><dd class="col-sm-7">{{ $pemblokiran->tanggal_debit?->format('d F Y H:i') }}</dd>
                        <dt class="col-sm-5">Nilai Didebit</dt>
                        <dd class="col-sm-7 fw-semibold text-success">Rp {{ number_format($pemblokiran->nilai_didebit, 0, ',', '.') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        @if ($pemblokiran->status === 'diblokir')
        <div class="col-lg-4">
            @can('pemblokiran.proses')
            <div class="card">
                <div class="card-header"><i class="bi bi-bank me-2"></i>Catat Debit Bank</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pemblokiran.proses', $pemblokiran) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Tanggal Debit <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="tanggal_debit" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold">Nilai Didebit (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_didebit" class="form-control form-control-sm"
                                value="{{ $pemblokiran->nilai_bantuan }}" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Bukti Debit Bank <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_debit_bank" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i>Simpan Bukti Debit
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
