@extends('layouts.app')
@section('title', $kontrak ? 'Edit Kontrak' : 'Buat Kontrak')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header mb-4">
        <h4 class="mb-1">{{ $kontrak ? 'Edit Kontrak' : 'Buat Kontrak Makan' }}</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kontrak.index') }}">Kontrak</a></li>
            <li class="breadcrumb-item active">{{ $kontrak ? 'Edit' : 'Buat' }}</li>
        </ol></nav>
    </div>

    <form method="POST"
        action="{{ $kontrak ? route('kontrak.update', $kontrak) : route('kontrak.store') }}"
        enctype="multipart/form-data">
        @csrf
        @if ($kontrak) @method('PATCH') @endif

        <div class="row g-4">
            {{-- Informasi Dasar --}}
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-file-earmark-text me-2"></i>Informasi Kontrak</div>
                    <div class="card-body row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nomor Kontrak <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_kontrak" class="form-control @error('nomor_kontrak') is-invalid @enderror"
                                value="{{ old('nomor_kontrak', $kontrak?->nomor_kontrak) }}" required>
                            @error('nomor_kontrak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Kontrak <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_kontrak" class="form-control @error('tanggal_kontrak') is-invalid @enderror"
                                value="{{ old('tanggal_kontrak', $kontrak?->tanggal_kontrak?->format('Y-m-d')) }}" required>
                            @error('tanggal_kontrak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror"
                                value="{{ old('tanggal_mulai', $kontrak?->tanggal_mulai?->format('Y-m-d')) }}" required>
                            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror"
                                value="{{ old('tanggal_selesai', $kontrak?->tanggal_selesai?->format('Y-m-d')) }}" required>
                            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nilai Kontrak (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="nilai_kontrak" class="form-control @error('nilai_kontrak') is-invalid @enderror"
                                value="{{ old('nilai_kontrak', $kontrak?->nilai_kontrak) }}" min="0" step="0.01" required>
                            @error('nilai_kontrak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Harga per Porsi (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga_porsi" class="form-control @error('harga_porsi') is-invalid @enderror"
                                value="{{ old('harga_porsi', $kontrak?->harga_porsi) }}" min="0" step="0.01" required>
                            @error('harga_porsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Penyedia Makan <span class="text-danger">*</span></label>
                            <select name="penyedia_id" class="form-select @error('penyedia_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Penyedia --</option>
                                @foreach ($penyedia as $p)
                                    <option value="{{ $p->id }}" @selected(old('penyedia_id', $kontrak?->penyedia_id) == $p->id)>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('penyedia_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach (['draft'=>'Draft','aktif'=>'Aktif','berakhir'=>'Berakhir','dibatalkan'=>'Dibatalkan'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('status', $kontrak?->status ?? 'draft') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $kontrak?->catatan) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Persetujuan PPK --}}
                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-person-check me-2"></i>Persetujuan PPK</div>
                    <div class="card-body row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">PPK yang Menyetujui</label>
                            <select name="disetujui_ppk_id" class="form-select">
                                <option value="">-- Belum Disetujui --</option>
                                @foreach ($ppkList as $ppk)
                                    <option value="{{ $ppk->id }}" @selected(old('disetujui_ppk_id', $kontrak?->disetujui_ppk_id) == $ppk->id)>
                                        {{ $ppk->name }} ({{ $ppk->nip ?? 'NIP: -' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Persetujuan PPK</label>
                            <input type="date" name="tgl_persetujuan_ppk" class="form-control"
                                value="{{ old('tgl_persetujuan_ppk', $kontrak?->tgl_persetujuan_ppk?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><i class="bi bi-paperclip me-2"></i>Dokumen Kontrak</div>
                    <div class="card-body">
                        @foreach ([
                            'file_kontrak'                 => 'Dokumen Kontrak (PDF)',
                            'file_addendum'                => 'Addendum (PDF)',
                            'file_berita_acara_penunjukan' => 'Berita Acara Penunjukan',
                            'file_notulensi_rapat'         => 'Notulensi Rapat',
                        ] as $field => $label)
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">{{ $label }}</label>
                            <input type="file" name="{{ $field }}" class="form-control form-control-sm @error($field) is-invalid @enderror" accept=".pdf">
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if ($kontrak?->$field)
                                <div class="mt-1">
                                    <a href="{{ Storage::url($kontrak->$field) }}" target="_blank" class="small text-primary">
                                        <i class="bi bi-file-pdf me-1"></i>Lihat file saat ini
                                    </a>
                                </div>
                            @endif
                        </div>
                        @endforeach
                        <div class="form-text">Maks. 10 MB per file. Format: PDF.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>{{ $kontrak ? 'Perbarui' : 'Simpan Kontrak' }}
            </button>
            <a href="{{ route('kontrak.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
