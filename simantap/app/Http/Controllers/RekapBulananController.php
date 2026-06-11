<?php

namespace App\Http\Controllers;

use App\Models\KontrakMakan;
use App\Models\PenerimaanMakan;
use App\Models\RekapBulanan;
use App\Models\RekapBulananApproval;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RekapBulananController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = RekapBulanan::with('taruna')
                ->when($request->bulan, fn ($q, $b) => $q->where('periode_bulan', $b))
                ->when($request->tahun, fn ($q, $y) => $q->where('periode_tahun', $y));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('nit', fn ($r) => $r->taruna?->nit)
                ->addColumn('nama_taruna', fn ($r) => $r->taruna?->nama)
                ->addColumn('nilai_fmt', fn ($r) => 'Rp ' . number_format($r->nilai_bantuan, 0, ',', '.'))
                ->addColumn('status_badge', fn ($r) => $this->statusBadge($r))
                ->addColumn('action', fn ($r) => $this->actionButtons($r))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('rekap.index', [
            'bulan'  => request('bulan', now()->month),
            'tahun'  => request('tahun', now()->year),
        ]);
    }

    /**
     * Hitung rekap bulanan dari penerimaan makan berdasarkan periode.
     * PPK-only action.
     */
    public function hitungPeriode(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bulan'      => 'required|integer|min:1|max:12',
            'tahun'      => 'required|integer|min:2020|max:2100',
            'kontrak_id' => 'required|exists:kontrak_makan,id',
        ]);

        $bulan      = $data['bulan'];
        $tahun      = $data['tahun'];
        $kontrakId  = $data['kontrak_id'];

        // Hitung total porsi per taruna dari penerimaan_makan
        $rekapData = PenerimaanMakan::query()
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->where('status_eligibilitas', 'dapat')
            ->select('taruna_id', DB::raw('SUM(jumlah_porsi_diterima) as total_porsi'))
            ->groupBy('taruna_id')
            ->get();

        $kontrak = KontrakMakan::findOrFail($kontrakId);

        DB::transaction(function () use ($rekapData, $bulan, $tahun, $kontrak) {
            foreach ($rekapData as $row) {
                $nilaiPerPorsi = $kontrak->harga_porsi;

                // Only advance status if currently at disetujui_wadir (or draft for initial create)
                $existing = RekapBulanan::where([
                    'taruna_id'    => $row->taruna_id,
                    'periode_bulan'=> $bulan,
                    'periode_tahun'=> $tahun,
                ])->first();

                $newStatus = match(true) {
                    $existing === null => RekapBulanan::STATUS_DRAFT,
                    $existing->status === RekapBulanan::STATUS_DISETUJUI_WADIR => RekapBulanan::STATUS_DIHITUNG_PPK,
                    default => $existing->status,
                };

                RekapBulanan::updateOrCreate(
                    [
                        'taruna_id'    => $row->taruna_id,
                        'periode_bulan'=> $bulan,
                        'periode_tahun'=> $tahun,
                    ],
                    [
                        'total_porsi'  => $row->total_porsi,
                        'nilai_bantuan'=> $row->total_porsi * $nilaiPerPorsi,
                        'kontrak_id'   => $kontrak->id,
                        'status'       => $newStatus,
                    ]
                );
            }
        });

        return redirect()->route('rekap.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Rekap bulanan berhasil dihitung untuk periode ' . $bulan . '/' . $tahun . '.');
    }

    public function show(RekapBulanan $rekap): View
    {
        $rekap->load(['taruna', 'kontrak', 'approvals.user']);
        return view('rekap.show', compact('rekap'));
    }

    public function create(): View
    {
        $kontrakList = KontrakMakan::where('status', 'aktif')->get();
        return view('rekap.hitung', compact('kontrakList'));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->hitungPeriode($request);
    }

    public function edit(RekapBulanan $rekap): View
    {
        return view('rekap.show', compact('rekap'));
    }

    public function update(Request $request, RekapBulanan $rekap): RedirectResponse
    {
        return back();
    }

    public function destroy(RekapBulanan $rekap): RedirectResponse
    {
        if ($rekap->status !== RekapBulanan::STATUS_DRAFT) {
            return back()->with('error', 'Hanya rekap berstatus Draft yang dapat dihapus.');
        }
        $rekap->delete();
        return redirect()->route('rekap.index')->with('success', 'Rekap dihapus.');
    }

    public function setujuiWadir(RekapBulanan $rekap): RedirectResponse
    {
        abort_unless(auth()->user()->can('rekap.setujui'), 403);

        if ($rekap->status !== RekapBulanan::STATUS_DRAFT) {
            return back()->with('error', 'Rekap harus berstatus Draft untuk disetujui Wadir III.');
        }

        $rekap->update(['status' => RekapBulanan::STATUS_DISETUJUI_WADIR]);

        return back()->with('success', 'Rekap bulanan disetujui oleh Wadir III.');
    }

    public function tandatangan(Request $request, RekapBulanan $rekap): RedirectResponse
    {
        $request->validate(['role' => 'required|in:pembina_karakter,ppk,kpa']);
        $role = $request->role;

        $statusMap = [
            'pembina_karakter' => RekapBulanan::STATUS_DITANDATANGANI_PEMBINA,
            'ppk'              => RekapBulanan::STATUS_DITANDATANGANI_PPK,
            'kpa'              => RekapBulanan::STATUS_DITANDATANGANI_KPA,
        ];

        RekapBulananApproval::create([
            'rekap_bulanan_id' => $rekap->id,
            'role'             => $role,
            'user_id'          => auth()->id(),
            'signed_at'        => now(),
        ]);

        $rekap->update(['status' => $statusMap[$role]]);

        return back()->with('success', 'Tanda tangan berhasil dicatat.');
    }

    public function finalize(RekapBulanan $rekap): RedirectResponse
    {
        if ($rekap->status !== RekapBulanan::STATUS_DITANDATANGANI_KPA) {
            return back()->with('error', 'Rekap belum lengkap tanda tangan untuk difinalisasi.');
        }
        $rekap->update(['status' => RekapBulanan::STATUS_FINAL]);
        return back()->with('success', 'Rekap bulanan berhasil difinalisasi.');
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function statusBadge(RekapBulanan $r): string
    {
        $map = [
            RekapBulanan::STATUS_DRAFT                   => 'secondary',
            RekapBulanan::STATUS_DISETUJUI_WADIR         => 'teal',
            RekapBulanan::STATUS_DIHITUNG_PPK            => 'info',
            RekapBulanan::STATUS_DITANDATANGANI_PEMBINA  => 'primary',
            RekapBulanan::STATUS_DITANDATANGANI_PPK      => 'warning',
            RekapBulanan::STATUS_DITANDATANGANI_KPA      => 'success',
            RekapBulanan::STATUS_FINAL                   => 'dark',
        ];
        $label = [
            RekapBulanan::STATUS_DRAFT                   => 'Draft',
            RekapBulanan::STATUS_DISETUJUI_WADIR         => 'Disetujui Wadir III',
            RekapBulanan::STATUS_DIHITUNG_PPK            => 'Dihitung PPK',
            RekapBulanan::STATUS_DITANDATANGANI_PEMBINA  => 'TTD Pembina',
            RekapBulanan::STATUS_DITANDATANGANI_PPK      => 'TTD PPK',
            RekapBulanan::STATUS_DITANDATANGANI_KPA      => 'TTD KPA',
            RekapBulanan::STATUS_FINAL                   => 'Final',
        ];
        $color = $map[$r->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ($label[$r->status] ?? $r->status) . '</span>';
    }

    private function actionButtons(RekapBulanan $r): string
    {
        return '<a href="' . route('rekap.show', $r) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
    }
}
