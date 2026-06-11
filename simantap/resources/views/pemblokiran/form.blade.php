@extends('layouts.app')
@section('title', $pemblokiran ? 'Edit Pemblokiran' : 'Usulkan Pemblokiran')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $pemblokiran ? 'Edit Usulan Pemblokiran' : 'Usulkan Pemblokiran Uang Makan' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pemblokiran.index') }}">Pemblokiran</a></li>
            <li class="breadcrumb-item active">{{ $pemblokiran ? 'Edit' : 'Usulkan' }}</li>
        </ol></nav>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-lock me-2"></i>Data Pemblokiran</div>
                <div class="card-body">
                    <form method="POST"
                        action="{{ $pemblokiran ? route('pemblokiran.update', $pemblokiran) : route('pemblokiran.store') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if ($pemblokiran) @method('PATCH') @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Taruna <span class="text-danger">*</span></label>
                                <select name="taruna_id" class="form-select @error('taruna_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Taruna --</option>
                                    @foreach ($tarunaList as $t)
                                        <option value="{{ $t->id }}" @selected(old('taruna_id', $pemblokiran?->taruna_id) == $t->id)>
                                            {{ $t->nit }} — {{ $t->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('taruna_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Rekening Senat (target debit) <span class="text-danger">*</span></label>
                                <select name="senat_account_id" class="form-select @error('senat_account_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Rekening Senat --</option>
                                    @foreach ($senatAccounts as $sa)
                                        <option value="{{ $sa->id }}" @selected(old('senat_account_id', $pemblokiran?->senat_account_id) == $sa->id)>
                                            {{ $sa->nama_rekening }} — {{ $sa->bank }} {{ $sa->nomor_rekening }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('senat_account_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Bulan <span class="text-danger">*</span></label>
                                <select name="periode_bulan" class="form-select @error('periode_bulan') is-invalid @enderror" required>
                                    @foreach (range(1,12) as $b)
                                        <option value="{{ $b }}" @selected(old('periode_bulan', $pemblokiran?->periode_bulan) == $b)>
                                            {{ \App\Helpers\DateHelper::namaBulan($b) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('periode_bulan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tahun <span class="text-danger">*</span></label>
                                <input type="number" name="periode_tahun" class="form-control @error('periode_tahun') is-invalid @enderror"
                                    value="{{ old('periode_tahun', $pemblokiran?->periode_tahun ?? now()->year) }}" required>
                                @error('periode_tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nilai Bantuan (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="nilai_bantuan" class="form-control @error('nilai_bantuan') is-invalid @enderror"
                                    value="{{ old('nilai_bantuan', $pemblokiran?->nilai_bantuan) }}" min="0" step="0.01" required>
                                @error('nilai_bantuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nomor Surat Pemblokiran</label>
                                <input type="text" name="nomor_surat_pemblokiran" class="form-control"
                                    value="{{ old('nomor_surat_pemblokiran', $pemblokiran?->nomor_surat_pemblokiran) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Surat</label>
                                <input type="date" name="tanggal_surat" class="form-control"
                                    value="{{ old('tanggal_surat', $pemblokiran?->tanggal_surat?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">File Surat Pemblokiran (PDF)</label>
                                <input type="file" name="file_surat_pemblokiran" class="form-control" accept=".pdf">
                                @if ($pemblokiran?->file_surat_pemblokiran)
                                    <div class="mt-1">
                                        <a href="{{ Storage::url($pemblokiran->file_surat_pemblokiran) }}" target="_blank" class="small text-primary">
                                            <i class="bi bi-file-pdf me-1"></i>Lihat file saat ini
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $pemblokiran?->catatan) }}</textarea>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ $pemblokiran ? 'Perbarui' : 'Usulkan' }}
                            </button>
                            <a href="{{ route('pemblokiran.index') }}" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
