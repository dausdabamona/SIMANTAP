<?php

namespace App\Http\Controllers;

use App\Models\PemblokiranUangMakan;
use App\Models\RekapBulanan;
use App\Models\SenatAccount;
use App\Models\Taruna;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PemblokiranController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return DataTables::eloquent(PemblokiranUangMakan::with('senatAccount'))
                ->addIndexColumn()
                ->addColumn('bank_group_badge', fn ($p) => $p->bank_group
                    ? '<span class="badge bg-info text-dark">' . e($p->bank_group) . '</span>'
                    : '-')
                ->addColumn('nilai_fmt', fn ($p) => 'Rp ' . number_format($p->total_nilai_diblokir ?? $p->nilai_bantuan ?? 0, 0, ',', '.'))
                ->addColumn('status_badge', fn ($p) => $this->statusBadge($p))
                ->addColumn('action', fn ($p) => $this->actionButtons($p))
                ->rawColumns(['bank_group_badge', 'status_badge', 'action'])
                ->make(true);
        }

        return view('pemblokiran.index');
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

        return view('pemblokiran.form', [
            'pemblokiran'   => null,
            'periodeTersedia' => $periodeTersedia,
        ]);
    }

    /**
     * Auto-generate 2 pemblokiran drafts (BSI + BNI) for a given periode.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'periode_bulan'           => 'required|integer|min:1|max:12',
            'periode_tahun'           => 'required|integer|min:2020|max:2100',
            'nomor_surat_pemblokiran' => 'nullable|string|max:100',
            'tanggal_surat'           => 'nullable|date',
            'catatan'                 => 'nullable|string|max:500',
        ]);

        $bulan = $data['periode_bulan'];
        $tahun = $data['periode_tahun'];

        // Compute aggregate per bank_group from rekap final
        $stats = RekapBulanan::final()
            ->byPeriode($bulan, $tahun)
            ->join('taruna', 'rekap_bulanan.taruna_id', '=', 'taruna.id')
            ->join('rekening_taruna', 'taruna.id', '=', 'rekening_taruna.taruna_id')
            ->select(
                'rekening_taruna.bank_group',
                DB::raw('COUNT(rekap_bulanan.id) as jumlah_taruna'),
                DB::raw('SUM(rekap_bulanan.nilai_bantuan) as total_nilai')
            )
            ->groupBy('rekening_taruna.bank_group')
            ->get();

        if ($stats->isEmpty()) {
            return back()->with('error', 'Tidak ada rekap final untuk periode tersebut.');
        }

        $created = DB::transaction(function () use ($bulan, $tahun, $stats, $data) {
            $results = [];
            foreach ($stats as $stat) {
                $bankGroup = $stat->bank_group;
                if (!$bankGroup) continue;

                // Skip if already exists
                $existing = PemblokiranUangMakan::where('periode_bulan', $bulan)
                    ->where('periode_tahun', $tahun)
                    ->where('bank_group', $bankGroup)
                    ->first();
                if ($existing) continue;

                $senatId = SenatAccount::where('bank_group', $bankGroup)
                    ->where('is_aktif', true)
                    ->value('id');

                $pemblokiran = PemblokiranUangMakan::create([
                    'periode_bulan'           => $bulan,
                    'periode_tahun'           => $tahun,
                    'bank_group'              => $bankGroup,
                    'jumlah_taruna_terdampak' => $stat->jumlah_taruna,
                    'total_nilai_diblokir'    => $stat->total_nilai,
                    'senat_account_id'        => $senatId,
                    'nilai_bantuan'           => $stat->total_nilai,
                    'status'                  => PemblokiranUangMakan::STATUS_DIUSULKAN,
                    'nomor_surat_pemblokiran' => $data['nomor_surat_pemblokiran'] ?? null,
                    'tanggal_surat'           => $data['tanggal_surat'] ?? null,
                    'catatan'                 => $data['catatan'] ?? null,
                    'diusulkan_oleh'          => auth()->id(),
                    'diusulkan_at'            => now(),
                    'created_by'              => auth()->id(),
                ]);

                $results[] = $pemblokiran;
            }
            return $results;
        });

        $jumlah = count($created);
        if ($jumlah === 0) {
            return back()->with('error', 'Pemblokiran untuk periode ini sudah ada.');
        }

        return redirect()->route('pemblokiran.index')
            ->with('success', $jumlah . ' surat pemblokiran berhasil diusulkan (' . implode(', ', array_column($created, 'bank_group')) . ').');
    }

    public function show(PemblokiranUangMakan $pemblokiranUangMakan): View
    {
        $pemblokiranUangMakan->load(['taruna', 'senatAccount', 'diusulkanOleh', 'diprosesOleh']);
        return view('pemblokiran.show', ['pemblokiran' => $pemblokiranUangMakan]);
    }

    public function edit(PemblokiranUangMakan $pemblokiranUangMakan): View
    {
        if ($pemblokiranUangMakan->status !== PemblokiranUangMakan::STATUS_DIUSULKAN) {
            return redirect()->route('pemblokiran.show', $pemblokiranUangMakan)
                ->with('error', 'Hanya pemblokiran berstatus Diusulkan yang dapat diedit.');
        }
        $periodeTersedia = RekapBulanan::final()
            ->select('periode_bulan', 'periode_tahun')
            ->distinct()
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->get();
        return view('pemblokiran.form', [
            'pemblokiran'     => $pemblokiranUangMakan,
            'periodeTersedia' => $periodeTersedia,
        ]);
    }

    public function update(Request $request, PemblokiranUangMakan $pemblokiranUangMakan): RedirectResponse
    {
        if ($pemblokiranUangMakan->status !== PemblokiranUangMakan::STATUS_DIUSULKAN) {
            return back()->with('error', 'Data ini tidak dapat diubah.');
        }
        $data = $request->validate([
            'nomor_surat_pemblokiran' => 'nullable|string|max:100',
            'tanggal_surat'           => 'nullable|date',
            'catatan'                 => 'nullable|string|max:500',
            'file_surat_pemblokiran'  => 'nullable|mimes:pdf|max:10240',
        ]);
        if ($request->hasFile('file_surat_pemblokiran')) {
            if ($pemblokiranUangMakan->file_surat_pemblokiran) {
                Storage::disk('public')->delete($pemblokiranUangMakan->file_surat_pemblokiran);
            }
            $data['file_surat_pemblokiran'] = $request->file('file_surat_pemblokiran')->store('pemblokiran', 'public');
        }
        $pemblokiranUangMakan->update($data);
        return redirect()->route('pemblokiran.index')->with('success', 'Pemblokiran berhasil diperbarui.');
    }

    public function destroy(PemblokiranUangMakan $pemblokiranUangMakan): RedirectResponse
    {
        if ($pemblokiranUangMakan->status !== PemblokiranUangMakan::STATUS_DIUSULKAN) {
            return back()->with('error', 'Pemblokiran yang sudah diproses tidak dapat dihapus.');
        }
        $pemblokiranUangMakan->forceDelete();
        return redirect()->route('pemblokiran.index')->with('success', 'Usulan pemblokiran dibatalkan.');
    }

    public function proses(Request $request, PemblokiranUangMakan $pemblokiranUangMakan): RedirectResponse
    {
        $request->validate([
            'bukti_debit_bank' => 'required|mimes:pdf,jpg,jpeg,png|max:5120',
            'tanggal_debit'    => 'required|date',
            'nilai_didebit'    => 'required|numeric|min:0',
        ]);

        $buktiPath = $request->file('bukti_debit_bank')->store('pemblokiran/debit', 'public');

        $pemblokiranUangMakan->update([
            'status'           => PemblokiranUangMakan::STATUS_DIDEBIT,
            'bukti_debit_bank' => $buktiPath,
            'tanggal_debit'    => $request->tanggal_debit,
            'nilai_didebit'    => $request->nilai_didebit,
            'diproses_oleh'    => auth()->id(),
            'diproses_at'      => now(),
        ]);

        return back()->with('success', 'Pemblokiran berhasil dicatat sebagai didebit.');
    }

    private function statusBadge(PemblokiranUangMakan $p): string
    {
        $map = [
            PemblokiranUangMakan::STATUS_DIUSULKAN => 'warning',
            PemblokiranUangMakan::STATUS_DIBLOKIR  => 'info',
            PemblokiranUangMakan::STATUS_DIDEBIT   => 'success',
        ];
        $label = [
            PemblokiranUangMakan::STATUS_DIUSULKAN => 'Diusulkan',
            PemblokiranUangMakan::STATUS_DIBLOKIR  => 'Diblokir',
            PemblokiranUangMakan::STATUS_DIDEBIT   => 'Didebit',
        ];
        $color = $map[$p->status] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . ($label[$p->status] ?? $p->status) . '</span>';
    }

    private function actionButtons(PemblokiranUangMakan $p): string
    {
        return '<a href="' . route('pemblokiran.show', $p) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>'
            . ($p->status === PemblokiranUangMakan::STATUS_DIUSULKAN
                ? '<a href="' . route('pemblokiran.edit', $p) . '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>'
                : '');
    }
}
