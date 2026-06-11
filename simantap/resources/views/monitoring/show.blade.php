@extends('layouts.app')
@section('title', 'Detail Foto Monitoring')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">Detail Foto Monitoring</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('monitoring.index') }}">Monitoring</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol></nav>
        </div>
        <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-image me-2"></i>Foto #{{ $monitoring->urutan }}</div>
                <div class="card-body text-center">
                    <img src="{{ Storage::url($monitoring->file_path) }}"
                        class="img-fluid rounded shadow-sm"
                        style="max-height:450px;object-fit:contain"
                        alt="Foto monitoring"
                        onerror="this.src=''; this.alt='Foto tidak tersedia'">
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-info-circle me-2"></i>Informasi</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-6">Taruna</dt>
                        <dd class="col-6">{{ $monitoring->penerimaan?->taruna?->nama ?? '–' }}</dd>

                        <dt class="col-6">Tanggal Penerimaan</dt>
                        <dd class="col-6">{{ $monitoring->penerimaan?->tanggal?->format('d/m/Y') ?? '–' }}</dd>

                        <dt class="col-6">Urutan Foto</dt>
                        <dd class="col-6">{{ $monitoring->urutan }}</dd>

                        <dt class="col-6">Diambil Pada</dt>
                        <dd class="col-6">{{ $monitoring->captured_at?->format('d/m/Y H:i') ?? '–' }}</dd>

                        @if ($monitoring->lat && $monitoring->long)
                        <dt class="col-6">Koordinat</dt>
                        <dd class="col-6 font-monospace small">
                            {{ number_format($monitoring->lat, 6) }},
                            {{ number_format($monitoring->long, 6) }}
                        </dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if ($monitoring->lat && $monitoring->long)
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-map me-2"></i>Peta Lokasi</div>
                <div class="card-body p-0" style="height:200px">
                    <iframe
                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $monitoring->long - 0.005 }},{{ $monitoring->lat - 0.005 }},{{ $monitoring->long + 0.005 }},{{ $monitoring->lat + 0.005 }}&layer=mapnik&marker={{ $monitoring->lat }},{{ $monitoring->long }}"
                        width="100%" height="200" style="border:0;border-radius:0 0 .375rem .375rem"
                        loading="lazy" allowfullscreen>
                    </iframe>
                </div>
            </div>
            @endif

            @can('monitoring.delete')
            <div class="card border-danger">
                <div class="card-header text-danger"><i class="bi bi-trash me-2"></i>Hapus Foto</div>
                <div class="card-body">
                    <form id="del-monitoring" method="POST" action="{{ route('monitoring.destroy', $monitoring) }}">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-outline-danger w-100"
                            onclick="konfirmasiHapus('del-monitoring', 'foto monitoring ini')">
                            <i class="bi bi-trash me-1"></i>Hapus Foto Ini
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection
