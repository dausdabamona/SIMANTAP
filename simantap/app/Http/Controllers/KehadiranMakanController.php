<?php

namespace App\Http\Controllers;

use App\Imports\KehadiranFingerprintImport;
use App\Models\KehadiranMakan;
use App\Models\SesiPenerimaanMakan;
use App\Models\Taruna;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class KehadiranMakanController extends Controller
{
    private function authorizeEdit(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['pembina_karakter', 'senat_taruna']),
            403
        );
    }

    public function index(SesiPenerimaanMakan $sesiPenerimaan): View
    {
        abort_unless(
            auth()->user()->hasAnyRole(['pembina_karakter', 'senat_taruna', 'ppk']),
            403
        );

        $sesiPenerimaan->load('pemesananHarian');

        // Ambil taruna yang terdaftar berdasarkan kelas dari pemesanan harian
        $tarunaList = Taruna::where('status_taruna', 'aktif')
            ->where('penerima_bantuan', true)
            ->orderBy('kelas')
            ->orderBy('nama')
            ->get();

        // Load kehadiran existing
        $kehadiranMap = KehadiranMakan::where('sesi_id', $sesiPenerimaan->id)
            ->pluck('hadir', 'taruna_id');

        return view('kehadiran-makan.centang', compact('sesiPenerimaan', 'tarunaList', 'kehadiranMap'));
    }

    public function simpanCentang(Request $request, SesiPenerimaanMakan $sesiPenerimaan): RedirectResponse
    {
        $this->authorizeEdit();
        abort_unless(
            in_array($sesiPenerimaan->status, [SesiPenerimaanMakan::STATUS_DITERIMA, SesiPenerimaanMakan::STATUS_ADA_MASALAH]),
            403,
            'Serah terima belum dilakukan untuk sesi ini.'
        );

        $data = $request->validate([
            'hadir'         => 'nullable|array',
            'hadir.*'       => 'integer|exists:taruna,id',
            'keterangan'    => 'nullable|array',
            'keterangan.*'  => 'nullable|string|max:200',
        ]);

        $hadirIds = collect($data['hadir'] ?? []);
        $keteranganMap = $data['keterangan'] ?? [];

        // Semua taruna aktif penerima bantuan
        $semuaTaruna = Taruna::where('status_taruna', 'aktif')
            ->where('penerima_bantuan', true)
            ->pluck('id');

        foreach ($semuaTaruna as $tarunaId) {
            KehadiranMakan::updateOrCreate(
                ['sesi_id' => $sesiPenerimaan->id, 'taruna_id' => $tarunaId],
                [
                    'hadir'      => $hadirIds->contains($tarunaId),
                    'sumber'     => KehadiranMakan::SUMBER_MANUAL,
                    'diinput_by' => auth()->id(),
                    'keterangan' => $keteranganMap[$tarunaId] ?? null,
                ]
            );
        }

        // Update porsi_dimakan_taruna di sesi
        $porsiTaruna = $sesiPenerimaan->hitungPorsiTaruna();
        $porsiSisa   = max(0, (int) $sesiPenerimaan->porsi_diterima - $porsiTaruna - (int) $sesiPenerimaan->porsi_redistribusi);

        $sesiPenerimaan->update([
            'porsi_dimakan_taruna' => $porsiTaruna,
            'porsi_sisa'           => $porsiSisa,
        ]);

        // Update agregat di pemesanan_harian
        $this->updateAgregatPemesanan($sesiPenerimaan->pemesanan_harian_id);

        return redirect()->route('kehadiran-makan.index', $sesiPenerimaan)
            ->with('success', "Kehadiran disimpan. {$porsiTaruna} taruna hadir, sisa {$porsiSisa} porsi.");
    }

    public function importFingerprint(Request $request, SesiPenerimaanMakan $sesiPenerimaan): RedirectResponse
    {
        $this->authorizeEdit();

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        // Baca file dan map NIT → taruna_id
        $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $request->file('file'));
        $sheet = $rows[0] ?? [];

        $nitMap = Taruna::whereIn('nit', collect($sheet)->flatten()->filter()->values())
            ->pluck('id', 'nit');

        $tidakDitemukan = 0;
        $terpetakan = 0;

        foreach ($sheet as $row) {
            $nit = trim($row[0] ?? '');
            $waktuScan = isset($row[1]) ? \Carbon\Carbon::parse($row[1]) : now();

            if (!$nit || !isset($nitMap[$nit])) {
                $tidakDitemukan++;
                continue;
            }

            KehadiranMakan::updateOrCreate(
                ['sesi_id' => $sesiPenerimaan->id, 'taruna_id' => $nitMap[$nit]],
                [
                    'hadir'      => true,
                    'sumber'     => KehadiranMakan::SUMBER_FINGERPRINT,
                    'waktu_scan' => $waktuScan,
                    'diinput_by' => auth()->id(),
                ]
            );
            $terpetakan++;
        }

        // Update porsi setelah import
        $porsiTaruna = $sesiPenerimaan->hitungPorsiTaruna();
        $porsiSisa   = max(0, (int) $sesiPenerimaan->porsi_diterima - $porsiTaruna - (int) $sesiPenerimaan->porsi_redistribusi);
        $sesiPenerimaan->update([
            'porsi_dimakan_taruna' => $porsiTaruna,
            'porsi_sisa'           => $porsiSisa,
        ]);

        $this->updateAgregatPemesanan($sesiPenerimaan->pemesanan_harian_id);

        return redirect()->route('kehadiran-makan.index', $sesiPenerimaan)
            ->with('success', "Import fingerprint: {$terpetakan} terpetakan, {$tidakDitemukan} tidak ditemukan.");
    }

    public function updateSatu(Request $request, SesiPenerimaanMakan $sesiPenerimaan, int $tarunaId): JsonResponse
    {
        $this->authorizeEdit();

        $data = $request->validate([
            'hadir'      => 'required|boolean',
            'keterangan' => 'nullable|string|max:200',
        ]);

        $taruna = Taruna::findOrFail($tarunaId);

        KehadiranMakan::updateOrCreate(
            ['sesi_id' => $sesiPenerimaan->id, 'taruna_id' => $tarunaId],
            [
                'hadir'      => $data['hadir'],
                'sumber'     => KehadiranMakan::SUMBER_MANUAL,
                'diinput_by' => auth()->id(),
                'keterangan' => $data['keterangan'] ?? null,
            ]
        );

        $porsiTaruna = $sesiPenerimaan->hitungPorsiTaruna();
        $porsiSisa   = max(0, (int) $sesiPenerimaan->porsi_diterima - $porsiTaruna - (int) $sesiPenerimaan->porsi_redistribusi);

        $sesiPenerimaan->update([
            'porsi_dimakan_taruna' => $porsiTaruna,
            'porsi_sisa'           => $porsiSisa,
        ]);

        $this->updateAgregatPemesanan($sesiPenerimaan->pemesanan_harian_id);

        return response()->json([
            'hadir'               => $data['hadir'],
            'porsi_dimakan_taruna'=> $porsiTaruna,
            'porsi_sisa'          => $porsiSisa,
            'rekonsiliasi_valid'  => $sesiPenerimaan->fresh()->rekonsiliasiValid(),
        ]);
    }

    // ── Helper ───────────────────────────────────────────────

    private function updateAgregatPemesanan(int $pemesananId): void
    {
        $sesiList = SesiPenerimaanMakan::where('pemesanan_harian_id', $pemesananId)->get();

        \App\Models\PemesananHarian::where('id', $pemesananId)->update([
            'total_porsi_diterima'      => $sesiList->sum('porsi_diterima'),
            'total_porsi_taruna'        => $sesiList->sum('porsi_dimakan_taruna'),
            'total_porsi_redistribusi'  => $sesiList->sum('porsi_redistribusi'),
        ]);
    }
}
