<?php

namespace App\Http\Controllers;

use App\Models\PenyediaMakan;
use App\Models\RekeningPenyedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekeningPenyediaController extends Controller
{
    public function index(PenyediaMakan $penyedium): View
    {
        abort_unless(auth()->user()->can('penyedia.view'), 403);
        $penyedium->load('rekening.dibuatOleh');
        return view('penyedia.rekening.index', ['penyedia' => $penyedium]);
    }

    public function create(PenyediaMakan $penyedium): View
    {
        abort_unless(auth()->user()->can('penyedia.edit'), 403);
        return view('penyedia.rekening.form', ['penyedia' => $penyedium, 'rekening' => null]);
    }

    public function store(Request $request, PenyediaMakan $penyedium): RedirectResponse
    {
        abort_unless(auth()->user()->can('penyedia.edit'), 403);

        $data = $this->validatedData($request);
        $data['penyedia_id'] = $penyedium->id;
        $data['dibuat_by']   = auth()->id();

        // Jika is_default, non-aktifkan default rekening lain terlebih dahulu
        if (!empty($data['is_default'])) {
            RekeningPenyedia::where('penyedia_id', $penyedium->id)
                ->update(['is_default' => false]);
        }

        RekeningPenyedia::create($data);

        return redirect()
            ->route('penyedia.rekening.index', $penyedium)
            ->with('success', 'Rekening berhasil ditambahkan.');
    }

    public function edit(PenyediaMakan $penyedium, RekeningPenyedia $rekening): View
    {
        abort_unless(auth()->user()->can('penyedia.edit'), 403);
        abort_unless($rekening->penyedia_id === $penyedium->id, 403);
        return view('penyedia.rekening.form', compact('penyedium', 'rekening') + ['penyedia' => $penyedium]);
    }

    public function update(Request $request, PenyediaMakan $penyedium, RekeningPenyedia $rekening): RedirectResponse
    {
        abort_unless(auth()->user()->can('penyedia.edit'), 403);
        abort_unless($rekening->penyedia_id === $penyedium->id, 403);

        $data = $this->validatedData($request, $rekening->id);

        if (!empty($data['is_default'])) {
            RekeningPenyedia::where('penyedia_id', $penyedium->id)
                ->where('id', '!=', $rekening->id)
                ->update(['is_default' => false]);
        }

        $rekening->update($data);

        return redirect()
            ->route('penyedia.rekening.index', $penyedium)
            ->with('success', 'Rekening berhasil diperbarui.');
    }

    public function destroy(PenyediaMakan $penyedium, RekeningPenyedia $rekening): RedirectResponse
    {
        abort_unless(auth()->user()->can('penyedia.edit'), 403);
        abort_unless($rekening->penyedia_id === $penyedium->id, 403);
        abort_unless(!$rekening->is_default, 422, 'Rekening default tidak dapat dihapus. Tetapkan rekening lain sebagai default terlebih dahulu.');

        $rekening->delete();

        return redirect()
            ->route('penyedia.rekening.index', $penyedium)
            ->with('success', 'Rekening berhasil dihapus.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $nomorRule = 'required|string|max:50|unique:rekening_penyedias,nomor_rekening'
            . ($ignoreId ? ",$ignoreId" : '');

        return $request->validate([
            'label'            => 'nullable|string|max:100',
            'bank'             => 'required|string|max:100',
            'nomor_rekening'   => $nomorRule,
            'nama_pemilik'     => 'required|string|max:150',
            'is_active'        => 'boolean',
            'is_default'       => 'boolean',
            'berlaku_mulai'    => 'nullable|date',
            'berlaku_sampai'   => 'nullable|date|after_or_equal:berlaku_mulai',
            'alasan_perubahan' => 'nullable|string|max:500',
        ]);
    }
}
