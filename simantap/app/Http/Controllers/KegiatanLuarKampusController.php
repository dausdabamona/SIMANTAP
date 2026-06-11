<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLuarKampus;
use App\Models\PembayaranLuarKampus;
use App\Models\Taruna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class KegiatanLuarKampusController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('kegiatan_luar.view'), 403);

        if ($request->ajax()) {
            $query = KegiatanLuarKampus::with('kaprodi')
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->when($request->tahun,  fn ($q, $y) => $q->whereYear('tanggal_mulai', $y));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('periode', fn ($k) => $k->tanggal_mulai->format('d/m/Y') . ' – ' . $k->tanggal_selesai->format('d/m/Y'))
                ->addColumn('status_badge', fn ($k) => $this->statusBadge($k))
                ->addColumn('action', fn ($k) => $this->actionButtons($k))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('kegiatan-luar.index', ['tahun' => request('tahun', now()->year)]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->can('kegiatan_luar.buat'), 403);
        return view('kegiatan-luar.form', ['kegiatan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.buat'), 403);

        $data = $request->validate([
            'nama_kegiatan'         => 'required|string|max:255',
            'deskripsi'             => 'nullable|string',
            'tanggal_mulai'         => 'required|date',
            'tanggal_selesai'       => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi'                => 'required|string|max:255',
            'jenis_kegiatan'        => 'required|in:pkl,praktek_lapangan,seminar,kunjungan,lainnya',
            'standar_biaya_per_hari'=> 'required|numeric|min:0',
        ]);

        $data['kaprodi_id']      = auth()->id();
        $data['kode_kegiatan']   = KegiatanLuarKampus::generateKode(now()->year);
        $data['status']          = KegiatanLuarKampus::STATUS_DRAFT;

        $kegiatan = KegiatanLuarKampus::create($data);

        return redirect()->route('kegiatan-luar.show', $kegiatan)
            ->with('success', 'Kegiatan luar kampus berhasil dibuat: ' . $kegiatan->kode_kegiatan);
    }

    public function show(KegiatanLuarKampus $kegiatanLuar): View
    {
        abort_unless(auth()->user()->can('kegiatan_luar.view'), 403);
        $kegiatanLuar->load(['kaprodi', 'peserta.taruna', 'pembayaran']);
        return view('kegiatan-luar.show', ['kegiatan' => $kegiatanLuar]);
    }

    public function edit(KegiatanLuarKampus $kegiatanLuar): View
    {
        abort_unless(auth()->user()->can('kegiatan_luar.buat'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DRAFT, 403);
        return view('kegiatan-luar.form', ['kegiatan' => $kegiatanLuar]);
    }

    public function update(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.buat'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DRAFT, 403);

        $data = $request->validate([
            'nama_kegiatan'         => 'required|string|max:255',
            'deskripsi'             => 'nullable|string',
            'tanggal_mulai'         => 'required|date',
            'tanggal_selesai'       => 'required|date|after_or_equal:tanggal_mulai',
            'lokasi'                => 'required|string|max:255',
            'jenis_kegiatan'        => 'required|in:pkl,praktek_lapangan,seminar,kunjungan,lainnya',
            'standar_biaya_per_hari'=> 'required|numeric|min:0',
        ]);

        $kegiatanLuar->update($data);
        return redirect()->route('kegiatan-luar.show', $kegiatanLuar)->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.buat'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DRAFT, 403);
        $kegiatanLuar->delete();
        return redirect()->route('kegiatan-luar.index')->with('success', 'Kegiatan dihapus.');
    }

    // ── Workflow actions ──────────────────────────────────────────────

    public function usulkan(KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.usulkan'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DRAFT, 403);

        $nilaiDiusulkan = $kegiatanLuar->peserta()->count() > 0
            ? $kegiatanLuar->peserta()->sum(DB::raw('hari_hadir')) * (float) $kegiatanLuar->standar_biaya_per_hari
            : 0;

        $kegiatanLuar->update([
            'status'               => KegiatanLuarKampus::STATUS_DIUSULKAN_KAPRODI,
            'total_nilai_diusulkan'=> $nilaiDiusulkan,
        ]);

        return back()->with('success', 'Kegiatan berhasil diusulkan.');
    }

    public function setujuiDirektur(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.setujui'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DIUSULKAN_KAPRODI, 403);

        $kegiatanLuar->update(['status' => KegiatanLuarKampus::STATUS_DISETUJUI_DIREKTUR]);
        return back()->with('success', 'Kegiatan disetujui Direktur. Silakan proses persetujuan Pusdik KP.');
    }

    public function ajukanPusdik(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.verifikasi'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DISETUJUI_DIREKTUR, 403);

        $kegiatanLuar->update(['status' => KegiatanLuarKampus::STATUS_MENUNGGU_PERSETUJUAN_PUSDIK]);
        return back()->with('success', 'Pengajuan ke Pusdik KP telah dikirim.');
    }

    public function inputPersetujuanPusdik(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('persetujuan_pusdik.input'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_MENUNGGU_PERSETUJUAN_PUSDIK, 403);

        $data = $request->validate([
            'nomor_surat_pusdik'   => 'required|string|max:100',
            'tanggal_surat_pusdik' => 'required|date',
            'total_nilai_disetujui'=> 'required|numeric|min:0',
            'file_surat_pusdik'    => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('file_surat_pusdik')) {
            $data['file_surat_pusdik'] = $request->file('file_surat_pusdik')
                ->store('surat-pusdik', 'public');
        }

        $data['status'] = KegiatanLuarKampus::STATUS_DISETUJUI_PUSDIK;
        $kegiatanLuar->update($data);

        return back()->with('success', 'Persetujuan Pusdik KP berhasil dicatat. Kegiatan siap proses pembayaran.');
    }

    public function batalkan(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('kegiatan_luar.setujui'), 403);

        $allowedStatuses = [
            KegiatanLuarKampus::STATUS_DIUSULKAN_KAPRODI,
            KegiatanLuarKampus::STATUS_DISETUJUI_DIREKTUR,
            KegiatanLuarKampus::STATUS_MENUNGGU_PERSETUJUAN_PUSDIK,
        ];

        abort_unless(in_array($kegiatanLuar->status, $allowedStatuses), 403);

        $data = $request->validate(['catatan_penolakan' => 'nullable|string']);
        $kegiatanLuar->update([
            'status'             => KegiatanLuarKampus::STATUS_DIBATALKAN,
            'catatan_penolakan'  => $data['catatan_penolakan'] ?? null,
        ]);

        return back()->with('success', 'Kegiatan dibatalkan.');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function statusBadge(KegiatanLuarKampus $k): string
    {
        $map = [
            KegiatanLuarKampus::STATUS_DRAFT                       => ['secondary', 'Draft'],
            KegiatanLuarKampus::STATUS_DIUSULKAN_KAPRODI           => ['info',      'Diusulkan Kaprodi'],
            KegiatanLuarKampus::STATUS_DISETUJUI_DIREKTUR          => ['primary',   'Disetujui Direktur'],
            KegiatanLuarKampus::STATUS_MENUNGGU_PERSETUJUAN_PUSDIK => ['warning',   'Menunggu Pusdik'],
            KegiatanLuarKampus::STATUS_DISETUJUI_PUSDIK            => ['success',   'Disetujui Pusdik'],
            KegiatanLuarKampus::STATUS_PROSES_PEMBAYARAN           => ['teal',      'Proses Pembayaran'],
            KegiatanLuarKampus::STATUS_SELESAI                     => ['dark',      'Selesai'],
            KegiatanLuarKampus::STATUS_DIBATALKAN                  => ['danger',    'Dibatalkan'],
        ];
        [$color, $label] = $map[$k->status] ?? ['secondary', $k->status];
        return '<span class="badge bg-' . $color . '">' . $label . '</span>';
    }

    private function actionButtons(KegiatanLuarKampus $k): string
    {
        return '<a href="' . route('kegiatan-luar.show', $k) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
    }
}
