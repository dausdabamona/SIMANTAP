@extends('layouts.app')
@section('title', 'Detail Kegiatan Luar Kampus')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1">{{ $kegiatan->kode_kegiatan }} — {{ $kegiatan->nama_kegiatan }}</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('kegiatan-luar.index') }}">Kegiatan Luar Kampus</a></li>
                <li class="breadcrumb-item active">{{ $kegiatan->kode_kegiatan }}</li>
            </ol></nav>
        </div>
        <a href="{{ route('kegiatan-luar.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    @include('components.alert')

    <div class="row g-4">
        {{-- Detail & Workflow --}}
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header fw-semibold">Informasi Kegiatan</div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-5">Kode</dt><dd class="col-7">{{ $kegiatan->kode_kegiatan }}</dd>
                        <dt class="col-5">Jenis</dt><dd class="col-7">{{ strtoupper(str_replace('_', ' ', $kegiatan->jenis_kegiatan)) }}</dd>
                        <dt class="col-5">Lokasi</dt><dd class="col-7">{{ $kegiatan->lokasi }}</dd>
                        <dt class="col-5">Mulai</dt><dd class="col-7">{{ $kegiatan->tanggal_mulai->format('d/m/Y') }}</dd>
                        <dt class="col-5">Selesai</dt><dd class="col-7">{{ $kegiatan->tanggal_selesai->format('d/m/Y') }}</dd>
                        <dt class="col-5">Standar Biaya</dt><dd class="col-7">Rp {{ number_format($kegiatan->standar_biaya_per_hari, 0, ',', '.') }}/hari</dd>
                        <dt class="col-5">Nilai Diusulkan</dt><dd class="col-7">Rp {{ number_format($kegiatan->total_nilai_diusulkan, 0, ',', '.') }}</dd>
                        <dt class="col-5">Nilai Disetujui</dt><dd class="col-7 fw-bold">Rp {{ number_format($kegiatan->total_nilai_disetujui, 0, ',', '.') }}</dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7">
                            @php
                            $badgeMap = [
                                'draft' => 'secondary', 'diusulkan_kaprodi' => 'info',
                                'disetujui_direktur' => 'primary', 'menunggu_persetujuan_pusdik' => 'warning',
                                'disetujui_pusdik' => 'success', 'proses_pembayaran' => 'teal',
                                'selesai' => 'dark', 'dibatalkan' => 'danger',
                            ];
                            $color = $badgeMap[$kegiatan->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucwords(str_replace('_', ' ', $kegiatan->status)) }}</span>
                        </dd>
                    </dl>
                </div>
            </div>

            {{-- Workflow buttons --}}
            <div class="card">
                <div class="card-header fw-semibold">Aksi</div>
                <div class="card-body d-grid gap-2">
                    @can('kegiatan_luar.buat')
                    @if ($kegiatan->status === 'draft')
                    <a href="{{ route('kegiatan-luar.edit', $kegiatan) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil me-1"></i>Edit Kegiatan
                    </a>
                    @endif
                    @endcan

                    @can('kegiatan_luar.usulkan')
                    @if ($kegiatan->status === 'draft')
                    <form method="POST" action="{{ route('kegiatan-luar.usulkan', $kegiatan) }}">
                        @csrf
                        <button class="btn btn-outline-info btn-sm w-100">
                            <i class="bi bi-send me-1"></i>Usulkan ke Direktur
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('kegiatan_luar.setujui')
                    @if ($kegiatan->status === 'diusulkan_kaprodi')
                    <form method="POST" action="{{ route('kegiatan-luar.setujui-direktur', $kegiatan) }}">
                        @csrf
                        <button class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-check2-circle me-1"></i>Setujui (Direktur/KPA)
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('kegiatan_luar.verifikasi')
                    @if ($kegiatan->status === 'disetujui_direktur')
                    <form method="POST" action="{{ route('kegiatan-luar.ajukan-pusdik', $kegiatan) }}">
                        @csrf
                        <button class="btn btn-outline-warning btn-sm w-100">
                            <i class="bi bi-send-check me-1"></i>Ajukan ke Pusdik KP
                        </button>
                    </form>
                    @endif
                    @endcan

                    @can('persetujuan_pusdik.input')
                    @if ($kegiatan->status === 'menunggu_persetujuan_pusdik')
                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalPusdik">
                        <i class="bi bi-file-earmark-check me-1"></i>Input Persetujuan Pusdik
                    </button>
                    @endif
                    @endcan

                    @can('pembayaran_luar.usulkan')
                    @if (in_array($kegiatan->status, ['disetujui_pusdik', 'proses_pembayaran']))
                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalBayar">
                        <i class="bi bi-cash me-1"></i>Buat Pengajuan Pembayaran
                    </button>
                    @endif
                    @endcan
                </div>
            </div>
        </div>

        {{-- Peserta --}}
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center fw-semibold">
                    Daftar Peserta ({{ $kegiatan->peserta->count() }} taruna)
                    @can('peserta_luar.input')
                    @if (in_array($kegiatan->status, ['draft', 'diusulkan_kaprodi']))
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalPeserta">
                        <i class="bi bi-person-plus me-1"></i>Tambah
                    </button>
                    @endif
                    @endcan
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light"><tr>
                            <th>#</th><th>NIT</th><th>Nama Taruna</th><th>Hari Hadir</th><th>Nilai Bantuan</th>
                            @can('daftar_hadir_luar.upload')<th>Aksi</th>@endcan
                        </tr></thead>
                        <tbody>
                            @forelse ($kegiatan->peserta as $i => $p)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $p->taruna?->nit }}</td>
                                <td>{{ $p->taruna?->nama }}</td>
                                <td>{{ $p->hari_hadir }}</td>
                                <td>Rp {{ number_format($p->nilai_bantuan, 0, ',', '.') }}</td>
                                @can('daftar_hadir_luar.upload')
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#modalHadir{{ $p->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                                @endcan
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">Belum ada peserta</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pembayaran --}}
            <div class="card">
                <div class="card-header fw-semibold">Pembayaran Luar Kampus</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light"><tr>
                            <th>Tahap</th><th>Nilai Diajukan</th><th>Nilai Disetujui</th><th>Status</th><th>Aksi</th>
                        </tr></thead>
                        <tbody>
                            @forelse ($kegiatan->pembayaran as $pay)
                            <tr>
                                <td>Tahap {{ $pay->tahap }}</td>
                                <td>Rp {{ number_format($pay->nilai_diajukan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($pay->nilai_disetujui, 0, ',', '.') }}</td>
                                <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $pay->status) }}</span></td>
                                <td><a href="{{ route('pembayaran-luar.show', $pay) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Belum ada pembayaran</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Persetujuan Pusdik --}}
@can('persetujuan_pusdik.input')
@if ($kegiatan->status === 'menunggu_persetujuan_pusdik')
<div class="modal fade" id="modalPusdik" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('kegiatan-luar.pusdik', $kegiatan) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Input Persetujuan Pusdik KP</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Nomor Surat Pusdik</label>
                        <input type="text" name="nomor_surat_pusdik" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Tanggal Surat</label>
                        <input type="date" name="tanggal_surat_pusdik" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Total Nilai Disetujui (Rp)</label>
                        <input type="number" name="total_nilai_disetujui" class="form-control" min="0" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">File Surat (PDF, maks 5MB)</label>
                        <input type="file" name="file_surat_pusdik" class="form-control" accept=".pdf">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan

{{-- Modal: Buat Pembayaran --}}
@can('pembayaran_luar.usulkan')
@if (in_array($kegiatan->status, ['disetujui_pusdik', 'proses_pembayaran']))
<div class="modal fade" id="modalBayar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('pembayaran-luar.buat', $kegiatan) }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Buat Pengajuan Pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Tahap</label>
                        <select name="tahap" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            @foreach (['I','II','III'] as $t)
                            @unless($kegiatan->pembayaran->pluck('tahap')->contains($t))
                            <option value="{{ $t }}">Tahap {{ $t }}</option>
                            @endunless
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Nilai Diajukan (Rp)</label>
                        <input type="number" name="nilai_diajukan" class="form-control" min="1" required>
                        <small class="text-muted">Sisa anggaran: Rp {{ number_format($kegiatan->sisaAnggaranTersedia(), 0, ',', '.') }}</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Ajukan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan

{{-- Modals: Update Hadir --}}
@can('daftar_hadir_luar.upload')
@foreach ($kegiatan->peserta as $p)
<div class="modal fade" id="modalHadir{{ $p->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('peserta-kegiatan.hadir', [$kegiatan, $p]) }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="modal-header"><h5 class="modal-title">Kehadiran: {{ $p->taruna?->nama }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label">Hari Hadir</label>
                        <input type="number" name="hari_hadir" class="form-control" value="{{ $p->hari_hadir }}" min="0" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">File Daftar Hadir</label>
                        <input type="file" name="file_daftar_hadir" class="form-control" accept=".pdf,.jpg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endcan
@endsection
