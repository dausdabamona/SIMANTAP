@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── STAT CARDS ROW ──────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Taruna Aktif --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card border-0">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label text-muted mb-2">Taruna Aktif</div>
                    <div class="stat-value text-success">{{ number_format($totalTarunaAktif) }}</div>
                    <small class="text-muted">dari {{ $totalTarunaAktif + $totalTarunaTidakAktif }} taruna terdaftar</small>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-person-check"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Taruna Tidak Aktif --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card border-0">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label text-muted mb-2">Tidak Aktif / Cuti</div>
                    <div class="stat-value text-warning">{{ number_format($totalTarunaTidakAktif) }}</div>
                    <small class="text-muted">cuti, pesiar, sakit, penundaan</small>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-person-dash"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Penerima Bantuan --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card border-0">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label text-muted mb-2">Penerima Bantuan</div>
                    <div class="stat-value text-primary">{{ number_format($totalPenerimaBantuan) }}</div>
                    <small class="text-muted">eligible hari ini: <strong>{{ $totalEligible }}</strong></small>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Realisasi Bulan Ini --}}
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card card border-0">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label text-muted mb-2">Realisasi {{ \App\Helpers\DateHelper::namaBulan($bulan) }}</div>
                    <div class="stat-value text-info" style="font-size:1.3rem">
                        Rp {{ number_format($realisasiBulanIni, 0, ',', '.') }}
                    </div>
                    <small class="text-muted">{{ $tahun }}</small>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── ROW 2 ─────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    {{-- Grafik Realisasi --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between bg-transparent">
                <span><i class="bi bi-bar-chart-line me-2 text-primary"></i>Realisasi Bantuan Makan</span>
                <span class="badge bg-primary bg-opacity-10 text-primary">6 Bulan Terakhir</span>
            </div>
            <div class="card-body">
                <canvas id="chartRealisasi" height="100"></canvas>
            </div>
        </div>
    </div>

    {{-- Status Taruna Donut --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-transparent">
                <i class="bi bi-pie-chart me-2 text-info"></i>Status Taruna
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="chartStatus" width="200" height="200"></canvas>
                <div class="mt-3 w-100">
                    @php
                        $statusLabels = [
                            'aktif'                   => ['Aktif', '#198754'],
                            'cuti'                    => ['Cuti', '#ffc107'],
                            'pesiar'                  => ['Pesiar', '#0dcaf0'],
                            'sakit_di_kampus'         => ['Sakit (Kampus)', '#0d6efd'],
                            'sakit_di_rumah_keluarga' => ['Sakit (Rumah)', '#dc3545'],
                            'penundaan_studi'         => ['Penundaan', '#6c757d'],
                        ];
                    @endphp
                    @foreach ($statusLabels as $key => [$label, $color])
                        @if (isset($statusBreakdown[$key]) && $statusBreakdown[$key] > 0)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:10px;height:10px;background:{{ $color }};border-radius:2px"></div>
                                <small>{{ $label }}</small>
                            </div>
                            <small class="fw-semibold">{{ $statusBreakdown[$key] }}</small>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── ROW 3 ─────────────────────────────────────────────── --}}
<div class="row g-3">

    {{-- Status Pembayaran --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span><i class="bi bi-send me-2 text-success"></i>Status Pembayaran Terkini</span>
                @can('pembayaran.view')
                <a href="{{ route('pembayaran.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                @endcan
            </div>
            <div class="card-body">
                @if ($riwayatPengajuan->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                        Belum ada pengajuan pembayaran
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Periode</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($riwayatPengajuan as $pgj)
                                <tr>
                                    <td><small class="font-monospace">{{ $pgj->nomor_pengajuan }}</small></td>
                                    <td>
                                        <small>
                                            {{ \App\Helpers\DateHelper::namaBulan($pgj->periode_bulan) }}
                                            {{ $pgj->periode_tahun }}
                                        </small>
                                    </td>
                                    <td><small>Rp {{ number_format($pgj->total_nilai, 0, ',', '.') }}</small></td>
                                    <td>
                                        @php
                                            $statusBadge = [
                                                'draft'             => 'secondary',
                                                'diproses_ppk'      => 'info',
                                                'disetujui_kpa'     => 'primary',
                                                'permohonan_kppn'   => 'warning',
                                                'sp2d'              => 'primary',
                                                'transfer_kppn'     => 'info',
                                                'debit_bank'        => 'warning',
                                                'transfer_penyedia' => 'warning',
                                                'selesai'           => 'success',
                                            ];
                                            $badge = $statusBadge[$pgj->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $badge }} bg-opacity-15 text-{{ $badge }} border border-{{ $badge }} border-opacity-25" style="font-size:.7rem">
                                            {{ $pgj->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Pemesanan & Alert Hari Ini --}}
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-transparent">
                <i class="bi bi-calendar-check me-2 text-warning"></i>Aktivitas Hari Ini
                <small class="text-muted ms-2">{{ now()->translatedFormat('d F Y') }}</small>
            </div>
            <div class="card-body">

                {{-- Pemesanan hari ini --}}
                @if ($pemesananHariIni)
                <div class="d-flex align-items-start gap-3 mb-3 p-3 rounded-3 bg-success bg-opacity-10">
                    <div class="flex-shrink-0 text-success fs-4"><i class="bi bi-check-circle-fill"></i></div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem">Pemesanan Hari Ini</div>
                        <small class="text-muted">
                            {{ $pemesananHariIni->jumlah_taruna_hadir }} taruna ·
                            {{ $pemesananHariIni->jumlah_porsi }} porsi ·
                            Rp {{ number_format($pemesananHariIni->nilai_total, 0, ',', '.') }}
                        </small>
                        <div class="mt-1">
                            @php
                                $s = $pemesananHariIni->status;
                                $sb = ['draft'=>'secondary','diverifikasi_pembina'=>'info','dikirim_penyedia'=>'success'];
                                $sl = ['draft'=>'Draft','diverifikasi_pembina'=>'Terverifikasi','dikirim_penyedia'=>'Dikirim ke Penyedia'];
                            @endphp
                            <span class="badge bg-{{ $sb[$s] ?? 'secondary' }}" style="font-size:.7rem">
                                {{ $sl[$s] ?? $s }}
                            </span>
                        </div>
                    </div>
                </div>
                @else
                <div class="d-flex align-items-start gap-3 mb-3 p-3 rounded-3 bg-warning bg-opacity-10">
                    <div class="flex-shrink-0 text-warning fs-4"><i class="bi bi-exclamation-circle-fill"></i></div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem">Belum Ada Pemesanan</div>
                        <small class="text-muted">Pemesanan untuk hari ini belum dibuat</small>
                        @can('pemesanan.create')
                        <div class="mt-1">
                            <a href="{{ route('pemesanan.create') }}" class="btn btn-sm btn-warning">
                                Buat Sekarang
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
                @endif

                {{-- Pending verifikasi --}}
                @if ($pemesananPendingVerifikasi > 0)
                <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-danger bg-opacity-10">
                    <div class="flex-shrink-0 text-danger fs-4"><i class="bi bi-clock-fill"></i></div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem">
                            {{ $pemesananPendingVerifikasi }} Pemesanan Perlu Verifikasi
                        </div>
                        <small class="text-muted">Menunggu verifikasi Pembina Karakter</small>
                        @can('pemesanan.verifikasi')
                        <div class="mt-1">
                            <a href="{{ route('pemesanan.index', ['status'=>'draft']) }}" class="btn btn-sm btn-danger">
                                Verifikasi Sekarang
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
                @else
                <div class="d-flex align-items-start gap-3 p-3 rounded-3 bg-success bg-opacity-10">
                    <div class="flex-shrink-0 text-success fs-4"><i class="bi bi-check2-all"></i></div>
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem">Semua Terverifikasi</div>
                        <small class="text-muted">Tidak ada pemesanan yang menunggu verifikasi</small>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
    const textColor = isDark ? '#94a3b8' : '#6c757d';

    // ── Grafik Realisasi ──────────────────────────────────
    const realisasiData = @json($grafikRealisasi);
    const bulanNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    new Chart(document.getElementById('chartRealisasi'), {
        type: 'bar',
        data: {
            labels: realisasiData.map(d => bulanNames[d.periode_bulan - 1] + ' ' + d.periode_tahun),
            datasets: [{
                label: 'Realisasi (Rp)',
                data: realisasiData.map(d => d.total),
                backgroundColor: 'rgba(37,99,235,.7)',
                borderColor: 'rgba(37,99,235,1)',
                borderWidth: 1,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw)
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
                y: {
                    grid: { color: gridColor },
                    ticks: {
                        color: textColor, font: { size: 11 },
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v)
                    }
                }
            }
        }
    });

    // ── Donut Status Taruna ────────────────────────────────
    const statusData = @json($statusBreakdown);
    const statusMap = {
        'aktif': ['Aktif', '#198754'],
        'cuti': ['Cuti', '#ffc107'],
        'pesiar': ['Pesiar', '#0dcaf0'],
        'sakit_di_kampus': ['Sakit (Kampus)', '#0d6efd'],
        'sakit_di_rumah_keluarga': ['Sakit (Rumah)', '#dc3545'],
        'penundaan_studi': ['Penundaan', '#6c757d'],
    };
    const statusKeys = Object.keys(statusData);

    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: statusKeys.map(k => statusMap[k]?.[0] ?? k),
            datasets: [{
                data: statusKeys.map(k => statusData[k]),
                backgroundColor: statusKeys.map(k => statusMap[k]?.[1] ?? '#999'),
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' taruna' } }
            }
        }
    });
});
</script>
@endpush
