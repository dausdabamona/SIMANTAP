<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLuarKampus;
use App\Models\PembayaranLuarKampus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PembayaranLuarKampusController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('pembayaran_luar.view'), 403);

        if ($request->ajax()) {
            $query = PembayaranLuarKampus::with('kegiatan')
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->when($request->tahap,  fn ($q, $t) => $q->where('tahap', $t));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('kode_kegiatan', fn ($p) => $p->kegiatan?->kode_kegiatan)
                ->addColumn('nama_kegiatan', fn ($p) => $p->kegiatan?->nama_kegiatan)
                ->addColumn('nilai_fmt', fn ($p) => 'Rp ' . number_format($p->nilai_diajukan, 0, ',', '.'))
                ->addColumn('status_badge', fn ($p) => $this->statusBadge($p))
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('pembayaran-luar.index');
    }

    public function show(PembayaranLuarKampus $pembayaranLuar): View
    {
        abort_unless(auth()->user()->can('pembayaran_luar.view'), 403);
        $pembayaranLuar->load('kegiatan.peserta.taruna');
        return view('pembayaran-luar.show', ['pembayaran' => $pembayaranLuar]);
    }

    public function buat(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran_luar.usulkan'), 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DISETUJUI_PUSDIK
            || $kegiatanLuar->status === KegiatanLuarKampus::STATUS_PROSES_PEMBAYARAN, 403);

        $data = $request->validate([
            'tahap'          => 'required|in:I,II,III',
            'nilai_diajukan' => 'required|numeric|min:1',
            'catatan'        => 'nullable|string',
        ]);

        // Guard: total paid + nilai_diajukan must not exceed total_nilai_disetujui
        $sudahDiajukan = $kegiatanLuar->pembayaran()
            ->whereNotIn('status', [PembayaranLuarKampus::STATUS_DRAFT])
            ->sum('nilai_diajukan');

        if (($sudahDiajukan + $data['nilai_diajukan']) > (float) $kegiatanLuar->total_nilai_disetujui) {
            return back()->withErrors(['nilai_diajukan' => 'Total pengajuan melebihi nilai disetujui Pusdik.']);
        }

        $data['kegiatan_id'] = $kegiatanLuar->id;
        $data['status']      = PembayaranLuarKampus::STATUS_DRAFT;

        PembayaranLuarKampus::create($data);

        $kegiatanLuar->update(['status' => KegiatanLuarKampus::STATUS_PROSES_PEMBAYARAN]);

        return redirect()->route('pembayaran-luar.index')
            ->with('success', 'Pengajuan pembayaran Tahap ' . $data['tahap'] . ' berhasil dibuat.');
    }

    public function verifikasiPpk(PembayaranLuarKampus $pembayaranLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran_luar.proses'), 403);
        abort_unless($pembayaranLuar->status === PembayaranLuarKampus::STATUS_DRAFT, 403);

        $pembayaranLuar->update(['status' => PembayaranLuarKampus::STATUS_DIVERIFIKASI_PPK]);
        return back()->with('success', 'Pembayaran diverifikasi PPK.');
    }

    public function ajukanKppn(PembayaranLuarKampus $pembayaranLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran_luar.proses'), 403);
        abort_unless($pembayaranLuar->status === PembayaranLuarKampus::STATUS_DIVERIFIKASI_PPK, 403);

        $pembayaranLuar->update(['status' => PembayaranLuarKampus::STATUS_DIAJUKAN_KPPN]);
        return back()->with('success', 'Pembayaran telah diajukan ke KPPN.');
    }

    public function inputSp2d(Request $request, PembayaranLuarKampus $pembayaranLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran_luar.input_sp2d'), 403);
        abort_unless($pembayaranLuar->status === PembayaranLuarKampus::STATUS_DIAJUKAN_KPPN, 403);

        $data = $request->validate([
            'nomor_sp2d'     => 'required|string|max:100',
            'tanggal_sp2d'   => 'required|date',
            'nilai_disetujui'=> 'required|numeric|min:0',
            'file_sp2d'      => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($request->hasFile('file_sp2d')) {
            $data['file_sp2d'] = $request->file('file_sp2d')->store('sp2d-luar', 'public');
        }

        $data['status'] = PembayaranLuarKampus::STATUS_SP2D_TERBIT;
        $pembayaranLuar->update($data);

        return back()->with('success', 'SP2D berhasil diinput.');
    }

    public function konfirmasiTransfer(PembayaranLuarKampus $pembayaranLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran_luar.proses'), 403);
        abort_unless($pembayaranLuar->status === PembayaranLuarKampus::STATUS_SP2D_TERBIT, 403);

        $pembayaranLuar->update(['status' => PembayaranLuarKampus::STATUS_TRANSFER_SELESAI]);
        return back()->with('success', 'Transfer selesai dicatat.');
    }

    public function konfirmasiTaruna(PembayaranLuarKampus $pembayaranLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran_luar.proses'), 403);
        abort_unless($pembayaranLuar->status === PembayaranLuarKampus::STATUS_TRANSFER_SELESAI, 403);

        $pembayaranLuar->update(['status' => PembayaranLuarKampus::STATUS_DIKONFIRMASI_TARUNA]);

        // If all tahap are confirmed, mark kegiatan as selesai
        $kegiatan = $pembayaranLuar->kegiatan;
        $allDone  = $kegiatan->pembayaran()
            ->where('status', '!=', PembayaranLuarKampus::STATUS_DIKONFIRMASI_TARUNA)
            ->doesntExist();

        if ($allDone && $kegiatan->pembayaran()->count() > 0) {
            $kegiatan->update(['status' => KegiatanLuarKampus::STATUS_SELESAI]);
        }

        return back()->with('success', 'Konfirmasi taruna berhasil dicatat.');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function statusBadge(PembayaranLuarKampus $p): string
    {
        $map = [
            PembayaranLuarKampus::STATUS_DRAFT               => ['secondary', 'Draft'],
            PembayaranLuarKampus::STATUS_DIVERIFIKASI_PPK    => ['info',      'Diverifikasi PPK'],
            PembayaranLuarKampus::STATUS_DIAJUKAN_KPPN       => ['primary',   'Diajukan KPPN'],
            PembayaranLuarKampus::STATUS_SP2D_TERBIT         => ['warning',   'SP2D Terbit'],
            PembayaranLuarKampus::STATUS_TRANSFER_SELESAI    => ['success',   'Transfer Selesai'],
            PembayaranLuarKampus::STATUS_DIKONFIRMASI_TARUNA => ['dark',      'Dikonfirmasi Taruna'],
        ];
        [$color, $label] = $map[$p->status] ?? ['secondary', $p->status];
        return '<span class="badge bg-' . $color . '">' . $label . '</span>';
    }

    private function actionButtons(PembayaranLuarKampus $p): string
    {
        return '<a href="' . route('pembayaran-luar.show', $p) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
    }
}
