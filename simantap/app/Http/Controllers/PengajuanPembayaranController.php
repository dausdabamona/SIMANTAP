<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembayaran;
use App\Models\RekapBulanan;
use App\Models\WorkflowPembayaran;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PengajuanPembayaranController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(PengajuanPembayaran::query())
                ->addIndexColumn()
                ->addColumn('periode', fn ($p) => $p->nama_bulan . ' ' . $p->periode_tahun)
                ->addColumn('nilai_fmt', fn ($p) => 'Rp ' . number_format($p->total_nilai, 0, ',', '.'))
                ->addColumn('status_badge', fn ($p) => $this->statusBadge($p))
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('pembayaran.index');
    }

    public function create(): View
    {
        // Periods that have final rekap but no pengajuan yet
        $periodeTersedia = RekapBulanan::final()
            ->select('periode_bulan', 'periode_tahun')
            ->distinct()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->get();

        return view('pembayaran.form', ['pengajuan' => null, 'periodeTersedia' => $periodeTersedia]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2020',
        ]);

        // Sum values from final rekap
        $totalNilai  = RekapBulanan::final()->byPeriode($data['periode_bulan'], $data['periode_tahun'])->sum('nilai_bantuan');
        $totalTaruna = RekapBulanan::final()->byPeriode($data['periode_bulan'], $data['periode_tahun'])->count();
        $totalPorsi  = RekapBulanan::final()->byPeriode($data['periode_bulan'], $data['periode_tahun'])->sum('total_porsi');

        if ($totalNilai <= 0) {
            return back()->with('error', 'Tidak ada rekap final untuk periode tersebut.');
        }

        $nomorPengajuan = 'PBY/' . $data['periode_tahun'] . '/' . str_pad($data['periode_bulan'], 2, '0', STR_PAD_LEFT) . '/' . now()->format('dmHi');

        $pengajuan = DB::transaction(function () use ($data, $totalNilai, $totalTaruna, $totalPorsi, $nomorPengajuan) {
            $pengajuan = PengajuanPembayaran::create([
                'nomor_pengajuan' => $nomorPengajuan,
                'periode_bulan'   => $data['periode_bulan'],
                'periode_tahun'   => $data['periode_tahun'],
                'total_taruna'    => $totalTaruna,
                'total_porsi'     => $totalPorsi,
                'total_nilai'     => $totalNilai,
                'status'          => PengajuanPembayaran::STATUS_DRAFT,
            ]);

            WorkflowPembayaran::create([
                'pengajuan_id' => $pengajuan->id,
                'status_dari'  => null,
                'status_ke'    => PengajuanPembayaran::STATUS_DRAFT,
                'user_id'      => auth()->id(),
                'catatan'      => 'Pengajuan dibuat',
                'created_at'   => now(),
            ]);

            return $pengajuan;
        });

        return redirect()->route('pembayaran.show', $pengajuan)->with('success', 'Pengajuan pembayaran berhasil dibuat.');
    }

    public function show(PengajuanPembayaran $pembayaran): View
    {
        $pembayaran->load('workflow.user');
        return view('pembayaran.show', compact('pembayaran'));
    }

    public function edit(PengajuanPembayaran $pembayaran): View
    {
        return view('pembayaran.show', compact('pembayaran'));
    }

    public function update(Request $request, PengajuanPembayaran $pembayaran): RedirectResponse
    {
        return back();
    }

    public function destroy(PengajuanPembayaran $pembayaran): RedirectResponse
    {
        if ($pembayaran->status !== PengajuanPembayaran::STATUS_DRAFT) {
            return back()->with('error', 'Hanya pengajuan berstatus Draft yang dapat dihapus.');
        }
        $pembayaran->delete();
        return redirect()->route('pembayaran.index')->with('success', 'Pengajuan berhasil dihapus.');
    }

    // ── State Machine Transitions ────────────────────────────────────

    public function transisi(Request $request, PengajuanPembayaran $pembayaran): RedirectResponse
    {
        $request->validate([
            'aksi'    => 'required|string',
            'catatan' => 'nullable|string|max:500',
        ]);

        $aksi     = $request->aksi;
        $statusNow = $pembayaran->status;

        [$statusBaru, $fieldUpdate] = match ($aksi) {
            'proses_ppk'          => [PengajuanPembayaran::STATUS_DIPROSES_PPK, []],
            'setujui_kpa'         => [PengajuanPembayaran::STATUS_DISETUJUI_KPA, []],
            'permohonan_kppn'     => [PengajuanPembayaran::STATUS_PERMOHONAN_KPPN, []],
            'input_sp2d'          => $this->handleSp2d($request, $pembayaran),
            'transfer_kppn'       => $this->handleUpload($request, $pembayaran, 'bukti_transfer_kppn', PengajuanPembayaran::STATUS_TRANSFER_KPPN),
            'debit_bank'          => $this->handleUpload($request, $pembayaran, 'bukti_debit_bank', PengajuanPembayaran::STATUS_DEBIT_BANK),
            'transfer_penyedia'   => $this->handleUpload($request, $pembayaran, 'bukti_transfer_penyedia', PengajuanPembayaran::STATUS_TRANSFER_PENYEDIA),
            'konfirmasi_penyedia' => [PengajuanPembayaran::STATUS_KONFIRMASI_PENYEDIA, []],
            'lpj_ppk'             => [PengajuanPembayaran::STATUS_LPJ_PPK, []],
            'lpj_kpa'             => [PengajuanPembayaran::STATUS_LPJ_KPA, []],
            'selesai'             => [PengajuanPembayaran::STATUS_SELESAI, []],
            default               => throw new \InvalidArgumentException("Aksi tidak dikenal: $aksi"),
        };

        DB::transaction(function () use ($pembayaran, $statusNow, $statusBaru, $fieldUpdate, $request) {
            $pembayaran->update(array_merge(['status' => $statusBaru], $fieldUpdate));

            WorkflowPembayaran::create([
                'pengajuan_id' => $pembayaran->id,
                'status_dari'  => $statusNow,
                'status_ke'    => $statusBaru,
                'user_id'      => auth()->id(),
                'catatan'      => $request->catatan,
                'created_at'   => now(),
            ]);
        });

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    // ── Private Helpers ──────────────────────────────────────────────

    private function handleSp2d(Request $request, PengajuanPembayaran $pembayaran): array
    {
        $request->validate([
            'nomor_sp2d'    => 'required|string|max:100',
            'tanggal_sp2d'  => 'required|date',
        ]);
        return [
            PengajuanPembayaran::STATUS_SP2D,
            ['nomor_sp2d' => $request->nomor_sp2d, 'tanggal_sp2d' => $request->tanggal_sp2d],
        ];
    }

    private function handleUpload(Request $request, PengajuanPembayaran $pembayaran, string $field, string $status): array
    {
        $request->validate([$field => 'required|mimes:pdf,jpg,jpeg,png|max:5120']);
        $path = $request->file($field)->store('pembayaran', 'public');
        return [$status, [$field => $path]];
    }

    private function statusBadge(PengajuanPembayaran $p): string
    {
        $color = match ($p->status) {
            PengajuanPembayaran::STATUS_DRAFT             => 'secondary',
            PengajuanPembayaran::STATUS_DIPROSES_PPK      => 'info',
            PengajuanPembayaran::STATUS_DISETUJUI_KPA     => 'primary',
            PengajuanPembayaran::STATUS_PERMOHONAN_KPPN   => 'warning',
            PengajuanPembayaran::STATUS_SP2D              => 'info',
            PengajuanPembayaran::STATUS_TRANSFER_KPPN     => 'primary',
            PengajuanPembayaran::STATUS_DEBIT_BANK        => 'warning',
            PengajuanPembayaran::STATUS_TRANSFER_PENYEDIA => 'success',
            PengajuanPembayaran::STATUS_KONFIRMASI_PENYEDIA => 'success',
            PengajuanPembayaran::STATUS_LPJ_PPK           => 'info',
            PengajuanPembayaran::STATUS_LPJ_KPA           => 'info',
            PengajuanPembayaran::STATUS_SELESAI           => 'dark',
            default                                        => 'secondary',
        };
        return '<span class="badge bg-' . $color . '">' . e($p->status_label) . '</span>';
    }

    private function actionButtons(PengajuanPembayaran $p): string
    {
        return '<a href="' . route('pembayaran.show', $p) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
    }
}
