<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLuarKampus;
use App\Models\PesertaKegiatanLuarKampus;
use App\Models\Taruna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PesertaKegiatanController extends Controller
{
    public function store(Request $request, KegiatanLuarKampus $kegiatanLuar): RedirectResponse
    {
        abort_unless(auth()->user()->can('peserta_luar.input'), 403);
        abort_unless(in_array($kegiatanLuar->status, [
            KegiatanLuarKampus::STATUS_DRAFT,
            KegiatanLuarKampus::STATUS_DIUSULKAN_KAPRODI,
        ]), 403);

        $data = $request->validate([
            'taruna_ids'   => 'required|array|min:1',
            'taruna_ids.*' => 'exists:taruna,id',
        ]);

        foreach ($data['taruna_ids'] as $tarunaId) {
            PesertaKegiatanLuarKampus::firstOrCreate([
                'kegiatan_id' => $kegiatanLuar->id,
                'taruna_id'   => $tarunaId,
            ]);
        }

        return back()->with('success', count($data['taruna_ids']) . ' taruna berhasil ditambahkan sebagai peserta.');
    }

    public function updateHadir(Request $request, KegiatanLuarKampus $kegiatanLuar, PesertaKegiatanLuarKampus $peserta): RedirectResponse
    {
        abort_unless(auth()->user()->can('daftar_hadir_luar.upload'), 403);
        abort_unless($peserta->kegiatan_id === $kegiatanLuar->id, 403);

        $durasi = $kegiatanLuar->tanggal_mulai->diffInDays($kegiatanLuar->tanggal_selesai) + 1;

        $data = $request->validate([
            'hari_hadir'       => 'required|integer|min:0|max:' . $durasi,
            'file_daftar_hadir'=> 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        if ($request->hasFile('file_daftar_hadir')) {
            $data['file_daftar_hadir'] = $request->file('file_daftar_hadir')
                ->store('daftar-hadir', 'public');
        }

        $data['nilai_bantuan'] = $data['hari_hadir'] * (float) $kegiatanLuar->standar_biaya_per_hari;
        $peserta->update($data);

        return back()->with('success', 'Data kehadiran taruna berhasil diperbarui.');
    }

    public function destroy(KegiatanLuarKampus $kegiatanLuar, PesertaKegiatanLuarKampus $peserta): RedirectResponse
    {
        abort_unless(auth()->user()->can('peserta_luar.input'), 403);
        abort_unless($peserta->kegiatan_id === $kegiatanLuar->id, 403);
        abort_unless($kegiatanLuar->status === KegiatanLuarKampus::STATUS_DRAFT, 403);

        $peserta->delete();
        return back()->with('success', 'Peserta dihapus dari daftar kegiatan.');
    }
}
