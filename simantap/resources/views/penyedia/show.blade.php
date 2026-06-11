@extends('layouts.app')
@section('title', 'Detail Penyedia')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Detail Penyedia Makan</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penyedia.index') }}">Penyedia</a></li>
                <li class="breadcrumb-item active">{{ $penyedia->nama }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            @can('penyedia.edit')
            <a href="{{ route('penyedia.edit', $penyedia) }}" class="btn btn-primary btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
            @endcan
            <a href="{{ route('penyedia.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-shop me-2"></i>Profil Penyedia</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Nama</dt><dd class="col-7 fw-semibold">{{ $penyedia->nama }}</dd>
                        <dt class="col-5">NPWP</dt><dd class="col-7">{{ $penyedia->npwp ?? '-' }}</dd>
                        <dt class="col-5">Telepon</dt><dd class="col-7">{{ $penyedia->telp ?? '-' }}</dd>
                        <dt class="col-5">Email</dt><dd class="col-7">{{ $penyedia->email ?? '-' }}</dd>
                        <dt class="col-5">Alamat</dt><dd class="col-7">{{ $penyedia->alamat ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-bank me-2"></i>Rekening Bank</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Bank</dt><dd class="col-7">{{ $penyedia->bank }}</dd>
                        <dt class="col-5">No. Rekening</dt><dd class="col-7 fw-semibold font-monospace">{{ $penyedia->nomor_rekening }}</dd>
                        <dt class="col-5">Atas Nama</dt><dd class="col-7">{{ $penyedia->nama_pemilik_rekening }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="bi bi-file-earmark-text me-2"></i>Riwayat Kontrak</div>
                <div class="card-body">
                    @if ($penyedia->kontrak->isEmpty())
                        <p class="text-muted mb-0">Belum ada kontrak.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>No. Kontrak</th><th>Tgl Kontrak</th><th>Nilai Kontrak</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($penyedia->kontrak as $k)
                                    <tr>
                                        <td class="font-monospace">{{ $k->nomor_kontrak }}</td>
                                        <td>{{ $k->tanggal_kontrak->format('d/m/Y') }}</td>
                                        <td>@money($k->nilai_kontrak)</td>
                                        <td><span class="badge bg-{{ $k->status === 'aktif' ? 'success' : 'secondary' }}">{{ ucfirst($k->status) }}</span></td>
                                        <td><a href="{{ route('kontrak.show', $k) }}" class="btn btn-xs btn-outline-info"><i class="bi bi-eye"></i></a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
