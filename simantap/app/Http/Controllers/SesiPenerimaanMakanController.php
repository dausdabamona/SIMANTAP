<?php

namespace App\Http\Controllers;

use App\Models\PemesananHarian;
use App\Models\SesiPenerimaanMakan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SesiPenerimaanMakanController extends Controller
{
    private function authorize(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['pembina_karakter', 'senat_taruna', 'ppk']),
            403
        );
    }

    private function authorizeEdit(): void
    {
        abort_unless(
            auth()->user()->hasAnyRole(['pembina_karakter', 'senat_taruna']),
            403
        );
    }

    public function index(Request $request): View
    {
        $this->authorize();

        $tanggal = $request->input('tanggal', today()->toDateString());
        $tanggalCarbon = \Carbon\Carbon::parse($tanggal);

        $pemesanan = PemesananHarian::whereDate('tanggal', $tanggal)->first();

        $sesiList = collect();
        if ($pemesanan) {
            // Ensure 3 sesi exist for this pemesanan
            foreach ([SesiPenerimaanMakan::SESI_SARAPAN, SesiPenerimaanMakan::SESI_SIANG, SesiPenerimaanMakan::SESI_MALAM] as $sesiKey) {
                SesiPenerimaanMakan::firstOrCreate(
                    ['pemesanan_harian_id' => $pemesanan->id, 'sesi' => $sesiKey],
                    [
                        'tanggal'     => $tanggal,
                        'porsi_dipesan' => (int) ceil($pemesanan->jumlah_porsi / 3),
                        'status'      => SesiPenerimaanMakan::STATUS_MENUNGGU,
                    ]
                );
            }
            $sesiList = SesiPenerimaanMakan::where('pemesanan_harian_id', $pemesanan->id)
                ->orderByRaw("FIELD(sesi, 'sarapan', 'siang', 'malam')")
                ->get();
        }

        return view('sesi-penerimaan.index', compact('tanggal', 'tanggalCarbon', 'pemesanan', 'sesiList'));
    }

    public function show(SesiPenerimaanMakan $sesiPenerimaan): View
    {
        $this->authorize();
        $sesiPenerimaan->load(['pemesananHarian', 'diterimaOleh', 'kehadiranMakan.taruna']);
        return view('sesi-penerimaan.show', ['sesi' => $sesiPenerimaan]);
    }

    public function terimaMakanan(Request $request, SesiPenerimaanMakan $sesiPenerimaan): RedirectResponse
    {
        $this->authorizeEdit();
        abort_unless($sesiPenerimaan->status === SesiPenerimaanMakan::STATUS_MENUNGGU, 403, 'Sesi sudah diproses.');

        $batasMaks = (int) ceil($sesiPenerimaan->porsi_dipesan * 1.1);

        $data = $request->validate([
            'porsi_diterima'   => "required|integer|min:0|max:{$batasMaks}",
            'kondisi_makanan'  => 'required|in:baik,kurang_baik,buruk',
            'catatan_kondisi'  => 'nullable|string|max:500',
            'lat'              => 'nullable|numeric',
            'lng'              => 'nullable|numeric',
            'foto'             => 'nullable|array|max:5',
            'foto.*'           => 'file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $fotoPaths[] = $file->store('sesi-penerimaan/foto', 'public');
            }
        }

        $porsiDiterima = (int) $data['porsi_diterima'];

        $sesiPenerimaan->update([
            'porsi_diterima'    => $porsiDiterima,
            'porsi_sisa'        => $porsiDiterima, // akan dikurangi saat kehadiran dicatat
            'kondisi_makanan'   => $data['kondisi_makanan'],
            'catatan_kondisi'   => $data['catatan_kondisi'] ?? null,
            'lat'               => $data['lat'] ?? null,
            'lng'               => $data['lng'] ?? null,
            'foto'              => $fotoPaths ?: null,
            'status'            => $data['kondisi_makanan'] === SesiPenerimaanMakan::KONDISI_BURUK
                                    ? SesiPenerimaanMakan::STATUS_ADA_MASALAH
                                    : SesiPenerimaanMakan::STATUS_DITERIMA,
            'waktu_serah_terima'=> now(),
            'diterima_by'       => auth()->id(),
            'diterima_at'       => now(),
        ]);

        return redirect()->route('sesi-penerimaan.show', $sesiPenerimaan)
            ->with('success', 'Serah terima makanan dicatat. Silakan input kehadiran taruna.');
    }

    public function simpanRedistribusi(Request $request, SesiPenerimaanMakan $sesiPenerimaan): RedirectResponse
    {
        $this->authorizeEdit();
        abort_unless(
            in_array($sesiPenerimaan->status, [SesiPenerimaanMakan::STATUS_DITERIMA, SesiPenerimaanMakan::STATUS_ADA_MASALAH]),
            403
        );

        $data = $request->validate([
            'redistribusi'  => 'nullable|array',
            'redistribusi.*' => 'nullable|integer|min:0',
            'porsi_sisa'    => 'required|integer|min:0',
        ]);

        $redistribuDict = $data['redistribusi'] ?? [];
        $redistribusiDetail = [];
        $totalRedistribusi = 0;

        foreach ($redistribuDict as $kategori => $jumlah) {
            $jumlah = (int) $jumlah;
            if ($jumlah > 0 && array_key_exists($kategori, SesiPenerimaanMakan::KATEGORI_REDISTRIBUSI)) {
                $redistribusiDetail[] = ['kategori' => $kategori, 'jumlah' => $jumlah];
                $totalRedistribusi += $jumlah;
            }
        }

        $porsiSisa = (int) $data['porsi_sisa'];
        $porsiTaruna = (int) ($sesiPenerimaan->porsi_dimakan_taruna ?? 0);
        $check = $porsiTaruna + $totalRedistribusi + $porsiSisa;

        if ($check !== (int) $sesiPenerimaan->porsi_diterima) {
            $selisih = (int) $sesiPenerimaan->porsi_diterima - $check;
            return back()->with('error',
                "Rekonsiliasi tidak seimbang. Selisih: {$selisih} porsi. " .
                "({$porsiTaruna} taruna + {$totalRedistribusi} redistribusi + {$porsiSisa} sisa = {$check} ≠ {$sesiPenerimaan->porsi_diterima} diterima)"
            );
        }

        $sesiPenerimaan->update([
            'redistribusi_detail' => $redistribusiDetail ?: null,
            'porsi_redistribusi'  => $totalRedistribusi,
            'porsi_sisa'          => $porsiSisa,
        ]);

        return redirect()->route('sesi-penerimaan.show', $sesiPenerimaan)
            ->with('success', 'Redistribusi porsi berhasil disimpan. Rekonsiliasi valid.');
    }

    public function formTerima(SesiPenerimaanMakan $sesiPenerimaan): View
    {
        $this->authorizeEdit();
        abort_unless($sesiPenerimaan->status === SesiPenerimaanMakan::STATUS_MENUNGGU, 403);
        return view('sesi-penerimaan.terima', ['sesi' => $sesiPenerimaan]);
    }
}
