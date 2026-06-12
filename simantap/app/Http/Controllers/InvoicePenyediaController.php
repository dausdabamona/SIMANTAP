<?php

namespace App\Http\Controllers;

use App\Models\InvoicePenyedia;
use App\Models\PengajuanPembayaran;
use App\Models\TransferPenyedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class InvoicePenyediaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->canAny([
            'pembayaran.view', 'rekap.view', 'invoice_penyedia.view',
        ]), 403);

        if ($request->ajax()) {
            $query = InvoicePenyedia::with('penyedia');

            if ($tahun = $request->input('tahun')) {
                $query->where('periode_tahun', $tahun);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('periode_label', fn ($i) => $i->periode_label)
                ->addColumn('nama_penyedia', fn ($i) => $i->penyedia?->nama ?? '-')
                ->addColumn('nilai_fmt', fn ($i) => 'Rp ' . number_format($i->total_nilai, 0, ',', '.'))
                ->addColumn('status_badge', fn ($i) => $this->statusBadge($i))
                ->addColumn('action', fn ($i) => $this->actionButtons($i))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('invoice-penyedia.index', ['tahun' => now()->year]);
    }

    public function show(InvoicePenyedia $invoicePenyedia): View
    {
        abort_unless(auth()->user()->canAny([
            'pembayaran.view', 'rekap.view', 'invoice_penyedia.view',
        ]), 403);

        $invoicePenyedia->load(['penyedia', 'diverifikasiOleh']);

        // Ambil transfer BSI + BNI periode yang sama
        $transfers = TransferPenyedia::where([
            'periode_bulan' => $invoicePenyedia->periode_bulan,
            'periode_tahun' => $invoicePenyedia->periode_tahun,
        ])->with('senatAccount')->get();

        return view('invoice-penyedia.show', compact('invoicePenyedia', 'transfers'));
    }

    public function upload(Request $request, InvoicePenyedia $invoicePenyedia): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('penyedia'), 403);
        abort_unless($invoicePenyedia->status === InvoicePenyedia::STATUS_MENUNGGU, 422);

        // Guard: kedua transfer periode ini sudah dikonfirmasi
        $dikonfirmasi = TransferPenyedia::where([
            'periode_bulan' => $invoicePenyedia->periode_bulan,
            'periode_tahun' => $invoicePenyedia->periode_tahun,
            'status'        => TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA,
        ])->count();

        if ($dikonfirmasi < 2) {
            return back()->with('error', 'Invoice hanya bisa diupload setelah kedua transfer (BSI dan BNI) dikonfirmasi.');
        }

        $request->validate([
            'nomor_invoice'   => 'required|string|max:50',
            'tanggal_invoice' => 'required|date',
            'file_invoice'    => 'required|mimes:pdf|max:5120',
            'total_nilai'     => 'required|numeric|min:0',
        ]);

        if ($request->total_nilai > $invoicePenyedia->total_nilai) {
            $selisih = number_format($request->total_nilai - $invoicePenyedia->total_nilai, 0, ',', '.');
            return back()->with('error', "Nilai invoice melebihi total transfer sebesar Rp {$selisih}.");
        }

        $path = $request->file('file_invoice')->store('invoice-penyedia', 'public');

        $invoicePenyedia->update([
            'nomor_invoice'   => $request->nomor_invoice,
            'tanggal_invoice' => $request->tanggal_invoice,
            'total_nilai'     => $request->total_nilai,
            'file_invoice'    => $path,
            'status'          => InvoicePenyedia::STATUS_DITERIMA,
        ]);

        return back()->with('success', 'Invoice berhasil diupload.');
    }

    public function verifikasi(Request $request, InvoicePenyedia $invoicePenyedia): RedirectResponse
    {
        abort_unless(auth()->user()->can('pembayaran.lpj'), 403);
        abort_unless($invoicePenyedia->status === InvoicePenyedia::STATUS_DITERIMA, 422);

        // Validasi nilai invoice vs total transfer
        $totalTransfer = TransferPenyedia::where([
            'periode_bulan' => $invoicePenyedia->periode_bulan,
            'periode_tahun' => $invoicePenyedia->periode_tahun,
            'status'        => TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA,
        ])->sum('total_nilai');

        $selisih = abs($invoicePenyedia->total_nilai - $totalTransfer);
        if ($selisih > 1) { // toleransi 1 rupiah rounding
            $selisihFmt = number_format($selisih, 0, ',', '.');
            return back()->with('error', "Nilai invoice tidak cocok dengan total transfer. Selisih: Rp {$selisihFmt}.");
        }

        $invoicePenyedia->update([
            'status'          => InvoicePenyedia::STATUS_DIVERIFIKASI,
            'diverifikasi_by' => auth()->id(),
            'diverifikasi_at' => now(),
        ]);

        // SPM periode ini yang masih debit_selesai boleh lanjut ke lpj_ppk
        // (tidak otomatis — PPK yang memproses secara individual)

        return back()->with('success', 'Invoice terverifikasi. SPM periode ini dapat dilanjutkan ke LPJ.');
    }

    // ── Private helpers ──────────────────────────────────────────────

    private function statusBadge(InvoicePenyedia $i): string
    {
        return '<span class="badge bg-' . $i->status_badge_color . '">' . e($i->status_label) . '</span>';
    }

    private function actionButtons(InvoicePenyedia $i): string
    {
        return '<a href="' . route('invoice-penyedia.show', $i) . '" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>';
    }
}
