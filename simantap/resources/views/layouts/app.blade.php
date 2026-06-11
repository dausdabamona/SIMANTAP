<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — SIMANTAP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script>
        // Terapkan tema sebelum render untuk mencegah flash
        const t = localStorage.getItem('simantap-theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', t);
    </script>
</head>
<body>

{{-- Flash data untuk SweetAlert2 --}}
@if (session('success') || session('error') || session('warning') || session('info'))
    <div id="flash-data" hidden
         data-type="{{ session('success') ? 'success' : (session('error') ? 'error' : (session('warning') ? 'warning' : 'info')) }}"
         data-message="{{ session('success') ?? session('error') ?? session('warning') ?? session('info') }}">
    </div>
@endif

<div class="wrapper">
    {{-- ── SIDEBAR ───────────────────────────────────────── --}}
    <nav class="sidebar" id="sidebar">
        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="bi bi-shield-check-fill"></i>
            </div>
            <div class="ms-2">
                <span class="brand-name">SIMANTAP</span>
                <span class="brand-sub">Poltek KP Sorong</span>
            </div>
        </a>

        <div class="pt-2 pb-3">

            {{-- Dashboard --}}
            <span class="sidebar-section-title">Utama</span>
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="sidebar-link @activeRoute('dashboard*')">
                <i class="bi bi-speedometer2 nav-icon"></i> Dashboard
            </a>

            {{-- Master Data --}}
            @canany(['taruna.view','penyedia.view','kontrak.view','sk_penerima.view','jadwal_menu.view'])
            <span class="sidebar-section-title">Master Data</span>

            @can('taruna.view')
            <a href="{{ route('taruna.index') }}" class="sidebar-link @activeRoute('taruna.*')">
                <i class="bi bi-people-fill nav-icon"></i> Master Taruna
            </a>
            @endcan

            @can('rekening.view')
            <a href="{{ route('rekening-taruna.index') }}" class="sidebar-link @activeRoute('rekening-taruna.*')">
                <i class="bi bi-credit-card-2-front nav-icon"></i> Rekening Taruna
            </a>
            @endcan

            @can('penyedia.view')
            <a href="{{ route('penyedia.index') }}" class="sidebar-link @activeRoute('penyedia.*')">
                <i class="bi bi-shop nav-icon"></i> Penyedia Makan
            </a>
            @endcan

            @can('kontrak.view')
            <a href="{{ route('kontrak.index') }}" class="sidebar-link @activeRoute('kontrak.*')">
                <i class="bi bi-file-earmark-text nav-icon"></i> Kontrak Makan
            </a>
            @endcan

            @can('sk_penerima.view')
            <a href="{{ route('sk-penerima.index') }}" class="sidebar-link @activeRoute('sk-penerima.*')">
                <i class="bi bi-patch-check nav-icon"></i> SK Penerima
            </a>
            @endcan

            @can('jadwal_menu.view')
            <a href="{{ route('jadwal-menu.index') }}" class="sidebar-link @activeRoute('jadwal-menu.*')">
                <i class="bi bi-calendar3 nav-icon"></i> Jadwal Menu
            </a>
            @endcan
            @endcanany

            {{-- Operasional Harian --}}
            @canany(['pemesanan.view','penerimaan.view','monitoring_foto.view'])
            <span class="sidebar-section-title">Operasional Harian</span>

            @can('pemesanan.view')
            <a href="{{ route('pemesanan.index') }}" class="sidebar-link @activeRoute('pemesanan.*')">
                <i class="bi bi-cart3 nav-icon"></i> Pemesanan Harian
            </a>
            @endcan

            @can('penerimaan.view')
            <a href="{{ route('penerimaan.index') }}" class="sidebar-link @activeRoute('penerimaan.*')">
                <i class="bi bi-check2-square nav-icon"></i> Penerimaan Makan
            </a>
            @endcan

            @can('monitoring_foto.view')
            <a href="{{ route('monitoring.index') }}" class="sidebar-link @activeRoute('monitoring.*')">
                <i class="bi bi-camera nav-icon"></i> Monitoring & Foto
            </a>
            @endcan
            @endcanany

            {{-- Rekap & Pembayaran --}}
            @canany(['rekap.view','pemblokiran.view','pembayaran.view'])
            <span class="sidebar-section-title">Rekap & Pembayaran</span>

            @can('rekap.view')
            <a href="{{ route('rekap.index') }}" class="sidebar-link @activeRoute('rekap.*')">
                <i class="bi bi-table nav-icon"></i> Rekap Bulanan
            </a>
            @endcan

            @can('pemblokiran.view')
            <a href="{{ route('pemblokiran.index') }}" class="sidebar-link @activeRoute('pemblokiran.*')">
                <i class="bi bi-lock-fill nav-icon"></i> Pemblokiran Dana
            </a>
            @endcan

            @can('pembayaran.view')
            <a href="{{ route('pembayaran.index') }}" class="sidebar-link @activeRoute('pembayaran.*')">
                <i class="bi bi-send-fill nav-icon"></i> Pengajuan Pembayaran
            </a>
            @endcan
            @endcanany

            {{-- Kegiatan Luar Kampus --}}
            @canany(['kegiatan_luar.view','pembayaran_luar.view'])
            <span class="sidebar-section-title">Luar Kampus</span>

            @can('kegiatan_luar.view')
            <a href="{{ route('kegiatan-luar.index') }}" class="sidebar-link @activeRoute('kegiatan-luar.*')">
                <i class="bi bi-geo-alt-fill nav-icon"></i> Kegiatan Luar Kampus
            </a>
            @endcan

            @can('pembayaran_luar.view')
            <a href="{{ route('pembayaran-luar.index') }}" class="sidebar-link @activeRoute('pembayaran-luar.*')">
                <i class="bi bi-cash-stack nav-icon"></i> Pembayaran Luar Kampus
            </a>
            @endcan
            @endcanany

            {{-- Laporan --}}
            @canany(['laporan.view','montev.view','auditlog.view'])
            <span class="sidebar-section-title">Laporan</span>

            @can('laporan.view')
            <a class="sidebar-link" data-bs-toggle="collapse" href="#menuLaporan" aria-expanded="false">
                <i class="bi bi-file-earmark-bar-graph nav-icon"></i>
                Laporan
                <i class="bi bi-chevron-right chevron"></i>
            </a>
            <div class="collapse" id="menuLaporan">
                <a href="{{ route('laporan.taruna') }}"       class="sidebar-link">Data Taruna</a>
                <a href="{{ route('laporan.rekap') }}"        class="sidebar-link">Rekap Bulanan</a>
                <a href="{{ route('laporan.pembayaran') }}"   class="sidebar-link">Pembayaran LS</a>
                <a href="{{ route('laporan.monitoring') }}"   class="sidebar-link">Monitoring</a>
            </div>
            @endcan

            @can('montev.view')
            <a href="{{ route('montev.index') }}" class="sidebar-link @activeRoute('montev.*')">
                <i class="bi bi-graph-up nav-icon"></i> Monev Bulanan
            </a>
            @endcan

            @can('auditlog.view')
            <a href="{{ route('audit-log.index') }}" class="sidebar-link @activeRoute('audit-log.*')">
                <i class="bi bi-journal-text nav-icon"></i> Audit Log
            </a>
            @endcan
            @endcanany

            {{-- Pengaturan --}}
            @can('user.manage')
            <span class="sidebar-section-title">Pengaturan</span>
            <a href="{{ route('users.index') }}" class="sidebar-link @activeRoute('users.*')">
                <i class="bi bi-person-gear nav-icon"></i> Manajemen User
            </a>
            @can('pagu.manage')
            <a href="{{ route('pagu.index') }}" class="sidebar-link @activeRoute('pagu.*')">
                <i class="bi bi-wallet2 nav-icon"></i> Pagu Anggaran
            </a>
            @endcan
            @endcan

        </div>
    </nav>

    {{-- Overlay mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    {{-- ── MAIN CONTENT ──────────────────────────────────── --}}
    <div class="main-content">

        {{-- Topbar --}}
        <header class="topbar">
            {{-- Toggle sidebar (mobile) --}}
            <button class="btn btn-link text-body p-1 d-lg-none" onclick="toggleSidebar()" title="Menu">
                <i class="bi bi-list fs-5"></i>
            </button>

            {{-- Page title --}}
            <span class="topbar-title d-none d-sm-block">@yield('page_title', 'Dashboard')</span>

            {{-- Breadcrumb desktop --}}
            <nav aria-label="breadcrumb" class="d-none d-md-flex flex-fill">
                <ol class="breadcrumb mb-0 ms-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Home</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>

            {{-- Dark mode toggle --}}
            <button class="btn btn-link text-body p-1" onclick="toggleDarkMode()" title="Ganti tema">
                <i class="bi bi-moon-fill" id="darkModeIcon"></i>
            </button>

            {{-- Notifikasi --}}
            <div class="dropdown">
                <button class="btn btn-link text-body p-1 position-relative" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5"></i>
                    {{-- Badge --}}
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.55rem">
                        3<span class="visually-hidden">notifikasi</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:300px">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    <li><a class="dropdown-item py-2" href="#">
                        <div class="d-flex gap-2 align-items-start">
                            <div class="flex-shrink-0"><span class="badge bg-warning">Baru</span></div>
                            <div><div class="fw-semibold" style="font-size:.82rem">Pemesanan menunggu verifikasi</div>
                            <small class="text-muted">2 menit lalu</small></div>
                        </div>
                    </a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item text-center small" href="#">Lihat semua</a></li>
                </ul>
            </div>

            {{-- User dropdown --}}
            <div class="dropdown">
                <button class="btn btn-link text-body p-1 d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=2563eb&color=fff&size=32' }}"
                         class="rounded-circle" width="32" height="32" alt="avatar">
                    <span class="d-none d-md-block" style="font-size:.82rem; max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap">
                        {{ auth()->user()->name }}
                    </span>
                    <i class="bi bi-chevron-down" style="font-size:.65rem"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="px-3 py-2">
                        <div class="fw-semibold" style="font-size:.85rem">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ auth()->user()->email }}</div>
                        <span class="badge bg-primary mt-1" style="font-size:.65rem">
                            {{ auth()->user()->getRoleNames()->first() ?? 'viewer' }}
                        </span>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person me-2"></i>Profil Saya
                    </a></li>
                    <li><a class="dropdown-item" href="{{ route('password.change') }}">
                        <i class="bi bi-key me-2"></i>Ganti Password
                    </a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="text-center text-muted py-3 border-top" style="font-size:.72rem">
            SIMANTAP &copy; {{ date('Y') }} Politeknik KP Sorong &nbsp;·&nbsp; SOP PR/PKU/KU-001/2025
        </footer>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
}
// Sync dark mode icon on load
document.addEventListener('DOMContentLoaded', () => {
    const theme = document.documentElement.getAttribute('data-bs-theme');
    const icon = document.getElementById('darkModeIcon');
    if (icon) icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
});
</script>

@stack('scripts')
</body>
</html>
