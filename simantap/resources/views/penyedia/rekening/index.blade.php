@extends('layouts.app')
@section('title', 'Rekening Penyedia — '.$penyedia->nama)

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Rekening Bank — {{ $penyedia->nama }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('penyedia.index') }}">Penyedia</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penyedia.show', $penyedia) }}">{{ $penyedia->nama }}</a></li>
                <li class="breadcrumb-item active">Rekening</li>
            </ol></nav>
        </div>
        @can('penyedia.edit')
        <a href="{{ route('penyedia.rekening.create', $penyedia) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Tambah Rekening
        </a>
        @endcan
    </div>

    @include('components.alert')

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Label</th>
                        <th>Bank</th>
                        <th>No. Rekening</th>
                        <th>Atas Nama</th>
                        <th>Berlaku</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        @can('penyedia.edit')<th width="100">Aksi</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($penyedia->rekening as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $r->label ?? '-' }}</td>
                        <td>{{ $r->bank }}</td>
                        <td class="font-monospace">{{ $r->nomor_rekening }}</td>
                        <td>{{ $r->nama_pemilik }}</td>
                        <td class="small text-muted">
                            {{ $r->berlaku_mulai?->format('d/m/Y') ?? '-' }}
                            @if ($r->berlaku_sampai) s/d {{ $r->berlaku_sampai->format('d/m/Y') }} @endif
                        </td>
                        <td>
                            @if ($r->is_default)<span class="badge bg-success me-1">Default</span>@endif
                            @if ($r->is_active)
                                <span class="badge bg-primary">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Non-aktif</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $r->dibuatOleh?->name ?? '-' }}</td>
                        @can('penyedia.edit')
                        <td>
                            <a href="{{ route('penyedia.rekening.edit', [$penyedia, $r]) }}" class="btn btn-xs btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @unless ($r->is_default)
                            <form method="POST" action="{{ route('penyedia.rekening.destroy', [$penyedia, $r]) }}" class="d-inline"
                                onsubmit="return confirm('Hapus rekening ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endunless
                        </td>
                        @endcan
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-3">Belum ada rekening terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
