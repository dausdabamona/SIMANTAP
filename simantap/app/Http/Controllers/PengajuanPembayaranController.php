<?php

namespace App\Http\Controllers;

use App\Models\PengajuanPembayaran;
use App\Models\RekapBulanan;
use App\Models\SenatAccount;
use App\Models\Taruna;
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
                ->addColumn('kelas_bank', fn ($p) => ($p->kelas ?? '-') . ' / ' . ($p->bank_group ?? '-'))
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
        // Periods with final rekap
        $periodeTersedia = RekapBulanan::final()
            ->select('periode_bulan', 'periode_tahun')
            ->distinct()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->get();

        return view('pembayaran.form', ['pengajuan' => null, 'periodeTersedia' => $periodeTersedia]);
    }

    /**
     * Return kelas available for a given periode (AJAX).
     * Each entry: kelas, tingkat, bank_group, jumlah_taruna, total_nilai
     * Already-created SPMs for that kelas+periode are excluded.
     */
    public function kelasTersedia(Request $request)
    {
        $bulan = (int) $request->periode_bulan;
        $tahun = (int) $request->periode_tahun;

        if (!$bulan || !$tahun) {
            return response()->json([]);
        }

        // Group rekap final by kelas (via taruna join)
        $rekaps = RekapBulanan::final()
            ->byPeriode($bulan, $tahun)
            ->join('taruna', 'rekap_bulanan.taruna_id', '=', 'taruna.id')
            ->whereNotNull('taruna.kelas')
            ->select('taruna.kelas', DB::raw('COUNT(rekap_bulanan.id) as jumlah_taruna'), DB::raw('SUM(rekap_bulanan.nilai_bantuan) as total_nilai'), DB::raw('SUM(rekap_bulanan.total_porsi) as total_porsi'))
            ->groupBy('taruna.kelas')
            ->get();

        // Get kelas that already have SPM this periode
        $sudahAda = PengajuanPembayaran::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->whereNotNull('kelas')
            ->pluck('kelas')
            ->toArray();

        $now   = now();
        $bulanNow = $now->month;
        $tahunNow = $now->year;

        $result = $rekaps->map(function ($r) use ($sudahAda, $bulanNow, $tahunNow) {
            // Determine tingkat from kelas name (e.g., "X-A" → tingkat 1, "XI-B" → 2, "XII-A" → 3)
            $tingkat = $this->tingkatDariKelas($r->kelas);
            $bankGroup = $tingkat === 1 ? 'BSI' : 'BNI';

            return [
                'kelas'         => $r->kelas,
                'tingkat'       => $tingkat,
                'bank_group'    => $bankGroup,
                'jumlah_taruna' => $r->jumlah_taruna,
                'total_nilai'   => $r->total_nilai,
                'total_porsi'   => $r->total_porsi,
                'sudah_ada_spm' => in_array($r->kelas, $sudahAda),
            ];
        });

        return response()->json($result);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_bulan' => 'required|integer|min:1|max:12',
            'periode_tahun' => 'required|integer|min:2020',
            'kelas'         => 'required|array|min:1',
            'kelas.*'       => 'required|string|max:20',
        ]);

        $bulan = $data['periode_bulan'];
        $tahun = $data['periode_tahun'];
        $kelasList = $data['kelas'];

        $created = DB::transaction(function () use ($bulan, $tahun, $kelasList) {
            $results = [];

            foreach ($kelasList as $kelas) {
                // Check not duplicate
                $existing = PengajuanPembayaran::where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->where('kelas', $kelas)
                    ->first();

                if ($existing) {
                    continue;
                }

                $rekap = RekapBulanan::final()
                    ->byPeriode($bulan, $tahun)
                    ->join('taruna', 'rekap_bulanan.taruna_id', '=', 'taruna.id')
                    ->where('taruna.kelas', $kelas)
                    ->selectRaw('COUNT(rekap_bulanan.id) as total_taruna, SUM(rekap_bulanan.nilai_bantuan) as total_nilai, SUM(rekap_bulanan.total_porsi) as total_porsi')
                    ->first();

                if (!$rekap || $rekap->total_nilai <= 0) {
                    continue;
                }

                $tingkat   = $this->tingkatDariKelas($kelas);
                $bankGroup = $tingkat === 1 ? 'BSI' : 'BNI';

                $senatId = SenatAccount::where('bank_group', $bankGroup)
                    ->where('is_aktif', true)
                    ->value('id');

                $nomorPengajuan = 'PBY/' . $tahun . '/' . str_pad($bulan, 2, '0', STR_PAD_LEFT)
                    . '/' . str_replace('-', '', $kelas) . '/' . now()->format('dmHi');

                $pengajuan = PengajuanPembayaran::create([
                    'nomor_pengajuan'  => $nomorPengajuan,
                    'periode_bulan'    => $bulan,
                    'periode_tahun'    => $tahun,
                    'kelas'            => $kelas,
                    'tingkat'          => $tingkat,
                    'bank_group'       => $bankGroup,
                    'rekening_senat_id'=> $senatId,
                    'total_taruna'     => $rekap->total_taruna,
                    'total_porsi'      => $rekap->total_porsi,
                    'total_nilai'      => $rekap->total_nilai,
                    'status'           => PengajuanPembayaran::STATUS_DRAFT,
                ]);

                WorkflowPembayaran::create([
                    'pengajuan_id' => $pengajuan->id,
                    'aksi'         => 'buat',
                    'status_dari'  => '',
                    'status_ke'    => PengajuanPembayaran::STATUS_DRAFT,
                    'user_id'      => auth()->id(),
                    'catatan'      => 'SPM dibuat untuk kelas ' . $kelas,
                    'created_at'   => now(),
                ]);

                $results[] = $pengajuan;
            }

            return $results;
        });

        $jumlah = count($created);
        if ($jumlah === 0) {
            return back()->with('error', 'Tidak ada SPM baru yang dibuat. Mungkin sudah ada atau rekap tidak tersedia.');
        }

        if ($jumlah === 1) {
            return redirect()->route('pembayaran.show', $created[0])
                ->with('success', 'SPM berhasil dibuat untuk kelas ' . $created[0]->kelas . '.');
        }

        return redirect()->route('pembayaran.index')
            ->with('success', $jumlah . ' SPM berhasil dibuat untuk periode ' . $bulan . '/' . $tahun . '.');
    }

    public function show(PengajuanPembayaran $pembayaran): View
    {
        $pembayaran->load('workflow.user', 'rekeningSenat');
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

        $aksi      = $request->aksi;
        $statusNow = $pembayaran->status;

        $permMap = [
            'proses_ppk'          => 'pembayaran.proses_ppk',
            'setujui_kpa'         => 'pembayaran.setujui_kpa',
            'permohonan_kppn'     => 'pembayaran.permohonan_kppn',
            'input_sp2d'          => 'pembayaran.input_sp2d',
            'transfer_kppn'       => 'pembayaran.upload',
            'debit_bank'          => 'pembayaran.upload',
            'transfer_penyedia'   => 'pembayaran.upload',
            'konfirmasi_penyedia' => 'pembayaran.konfirmasi',
            'lpj_ppk'             => 'pembayaran.lpj',
            'lpj_kpa'             => 'pembayaran.lpj',
            'selesai'             => 'pembayaran.selesai',
        ];
        if (isset($permMap[$aksi])) {
            abort_unless(auth()->user()->can($permMap[$aksi]), 403);
        }

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
                'aksi'         => $request->aksi,
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

    private function tingkatDariKelas(string $kelas): int
    {
        $upper = strtoupper($kelas);
        if (str_starts_with($upper, 'XII')) return 3;
        if (str_starts_with($upper, 'XI'))  return 2;
        return 1;
    }

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
            PengajuanPembayaran::STATUS_DRAFT               => 'secondary',
            PengajuanPembayaran::STATUS_DIPROSES_PPK        => 'info',
            PengajuanPembayaran::STATUS_DISETUJUI_KPA       => 'primary',
            PengajuanPembayaran::STATUS_PERMOHONAN_KPPN     => 'warning',
            PengajuanPembayaran::STATUS_SP2D                => 'info',
            PengajuanPembayaran::STATUS_TRANSFER_KPPN       => 'primary',
            PengajuanPembayaran::STATUS_DEBIT_BANK          => 'warning',
            PengajuanPembayaran::STATUS_TRANSFER_PENYEDIA   => 'success',
            PengajuanPembayaran::STATUS_KONFIRMASI_PENYEDIA => 'success',
            PengajuanPembayaran::STATUS_LPJ_PPK             => 'info',
            PengajuanPembayaran::STATUS_LPJ_KPA             => 'info',
            PengajuanPembayaran::STATUS_SELESAI             => 'dark',
            default                                         => 'secondary',
        };
        return '<span class="badge bg-' . $color . '">' . e($p->status_label) . '</span>';
    }

    private function actionButtons(PengajuanPembayaran $p): string
    {
        return '<a href="' . route('pembayaran.show', $p) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
    }
}
