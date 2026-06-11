<?php

namespace App\Http\Controllers;

use App\Models\KontrakMakan;
use App\Models\PemesananHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PemesananHarianController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = PemesananHarian::with(['kontrak', 'ttdSenat', 'ttdPembina'])
                ->when($request->bulan, fn ($q, $b) => $q->whereMonth('tanggal', $b))
                ->when($request->tahun, fn ($q, $y) => $q->whereYear('tanggal', $y));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('kontrak_nomor', fn ($p) => $p->kontrak?->nomor_kontrak)
                ->addColumn('nilai_fmt', fn ($p) => 'Rp ' . number_format($p->nilai_total, 0, ',', '.'))
                ->addColumn('status_badge', fn ($p) => $this->statusBadge($p))
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        $kontrakAktif = KontrakMakan::where('status', 'aktif')->first();

        return view('pemesanan.index', [
            'bulan'        => request('bulan', now()->month),
            'tahun'        => request('tahun', now()->year),
            'kontrakAktif' => $kontrakAktif,
        ]);
    }

    public function create(): View
    {
        $kontrakList = KontrakMakan::where('status', 'aktif')->with('penyedia')->get();
        $tanggal     = request('tanggal', now()->addDay()->format('Y-m-d'));

        // Default harga porsi from active contract
        $kontrakAktif = $kontrakList->first();

        return view('pemesanan.form', [
            'pemesanan'   => null,
            'kontrakList' => $kontrakList,
            'tanggal'     => $tanggal,
            'hargaDefault'=> $kontrakAktif?->harga_porsi,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal'              => 'required|date',
            'kontrak_id'           => 'required|exists:kontrak_makan,id',
            'jumlah_taruna_hadir'  => 'required|integer|min:0',
            'harga_porsi_snapshot' => 'required|numeric|min:0',
            'catatan_menu'         => 'nullable|string|max:500',
            'menu_sesuai_jadwal'   => 'boolean',
            'catatan'              => 'nullable|string|max:500',
        ]);

        // Cegah duplikasi tanggal + kontrak
        if (PemesananHarian::where('tanggal', $data['tanggal'])->where('kontrak_id', $data['kontrak_id'])->exists()) {
            return back()->withErrors(['tanggal' => 'Pemesanan untuk tanggal ini sudah ada.'])->withInput();
        }

        $data['menu_sesuai_jadwal'] = $request->boolean('menu_sesuai_jadwal', true);
        $data['status']    = PemesananHarian::STATUS_DRAFT;
        $data['created_by']= auth()->id();

        $pemesanan = PemesananHarian::make($data);
        $pemesanan->hitungNilai();
        $pemesanan->save();

        return redirect()->route('pemesanan.show', $pemesanan)->with('success', 'Pemesanan harian berhasil dibuat.');
    }

    public function show(PemesananHarian $pemesanan): View
    {
        $pemesanan->load(['kontrak.penyedia', 'ttdSenat', 'ttdPembina', 'monitoringFoto', 'beritaAcaraPerubahan']);
        return view('pemesanan.show', compact('pemesanan'));
    }

    public function edit(PemesananHarian $pemesanan): View
    {
        if ($pemesanan->wajib_berita_acara) {
            return redirect()->route('pemesanan.show', $pemesanan)
                ->with('error', 'Pemesanan sudah melewati batas waktu. Perubahan memerlukan Berita Acara.');
        }

        $kontrakList = KontrakMakan::where('status', 'aktif')->with('penyedia')->get();

        return view('pemesanan.form', [
            'pemesanan'   => $pemesanan,
            'kontrakList' => $kontrakList,
            'tanggal'     => $pemesanan->tanggal->format('Y-m-d'),
            'hargaDefault'=> $pemesanan->harga_porsi_snapshot,
        ]);
    }

    public function update(Request $request, PemesananHarian $pemesanan): RedirectResponse
    {
        if (! in_array($pemesanan->status, [PemesananHarian::STATUS_DRAFT, PemesananHarian::STATUS_PERUBAHAN])) {
            return back()->with('error', 'Pemesanan dengan status ini tidak dapat diedit.');
        }

        $data = $request->validate([
            'jumlah_taruna_hadir'  => 'required|integer|min:0',
            'harga_porsi_snapshot' => 'required|numeric|min:0',
            'catatan_menu'         => 'nullable|string|max:500',
            'menu_sesuai_jadwal'   => 'boolean',
            'catatan'              => 'nullable|string|max:500',
        ]);

        $data['menu_sesuai_jadwal'] = $request->boolean('menu_sesuai_jadwal', true);
        $data['updated_by'] = auth()->id();

        $pemesanan->fill($data);
        $pemesanan->hitungNilai();
        $pemesanan->save();

        return redirect()->route('pemesanan.show', $pemesanan)->with('success', 'Pemesanan berhasil diperbarui.');
    }

    public function destroy(PemesananHarian $pemesanan): RedirectResponse
    {
        if ($pemesanan->status !== PemesananHarian::STATUS_DRAFT) {
            return back()->with('error', 'Hanya pemesanan berstatus Draft yang dapat dihapus.');
        }
        $pemesanan->delete();
        return redirect()->route('pemesanan.index')->with('success', 'Pemesanan berhasil dihapus.');
    }

    // ── State Machine Actions ────────────────────────────────────────

    public function tandatanganiSenat(PemesananHarian $pemesanan): RedirectResponse
    {
        $pemesanan->update([
            'ttd_senat_id' => auth()->id(),
            'ttd_senat_at' => now(),
        ]);
        return back()->with('success', 'Tanda tangan Senat berhasil dicatat.');
    }

    public function verifikasiPembina(Request $request, PemesananHarian $pemesanan): RedirectResponse
    {
        $request->validate(['catatan_pembina' => 'nullable|string|max:500']);
        $pemesanan->update([
            'status'        => PemesananHarian::STATUS_DIVERIFIKASI_PEMBINA,
            'ttd_pembina_id'=> auth()->id(),
            'ttd_pembina_at'=> now(),
            'catatan_pembina'=> $request->catatan_pembina,
        ]);
        return back()->with('success', 'Pemesanan berhasil diverifikasi Pembina.');
    }

    public function kirimPenyedia(PemesananHarian $pemesanan): RedirectResponse
    {
        $pemesanan->update([
            'status'        => PemesananHarian::STATUS_DIKIRIM_PENYEDIA,
            'dikirim_at'    => now(),
            'dikirim_oleh'  => auth()->id(),
        ]);
        return back()->with('success', 'Pemesanan berhasil dikirim ke penyedia.');
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function statusBadge(PemesananHarian $p): string
    {
        $map = [
            PemesananHarian::STATUS_DRAFT               => 'secondary',
            PemesananHarian::STATUS_DIVERIFIKASI_PEMBINA => 'info',
            PemesananHarian::STATUS_DIKIRIM_PENYEDIA    => 'primary',
            PemesananHarian::STATUS_PERUBAHAN           => 'warning',
            PemesananHarian::STATUS_DISAJIKAN           => 'success',
            PemesananHarian::STATUS_SELESAI             => 'dark',
        ];
        $label = [
            PemesananHarian::STATUS_DRAFT               => 'Draft',
            PemesananHarian::STATUS_DIVERIFIKASI_PEMBINA => 'Diverifikasi',
            PemesananHarian::STATUS_DIKIRIM_PENYEDIA    => 'Dikirim',
            PemesananHarian::STATUS_PERUBAHAN           => 'Perubahan',
            PemesananHarian::STATUS_DISAJIKAN           => 'Disajikan',
            PemesananHarian::STATUS_SELESAI             => 'Selesai',
        ];
        $color = $map[$p->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ($label[$p->status] ?? ucfirst($p->status)) . '</span>';
    }

    private function actionButtons(PemesananHarian $p): string
    {
        $btn = '<a href="' . route('pemesanan.show', $p) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>';

        if (in_array($p->status, [PemesananHarian::STATUS_DRAFT, PemesananHarian::STATUS_PERUBAHAN])) {
            $btn .= '<a href="' . route('pemesanan.edit', $p) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
        }

        return $btn;
    }
}
