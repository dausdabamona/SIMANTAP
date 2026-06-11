<?php

namespace App\Http\Controllers;

use App\Models\KegiatanLuarKampus;
use App\Models\KontrakMakan;
use App\Models\LaporanBama;
use App\Models\PaguAnggaran;
use App\Models\PembayaranLuarKampus;
use App\Models\PemesananHarian;
use App\Models\PengajuanPembayaran;
use App\Models\RekapBulanan;
use App\Models\Taruna;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use Yajra\DataTables\Facades\DataTables;

class LaporanBamaController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->can('laporan_bama.view'), 403);

        if ($request->ajax()) {
            $query = LaporanBama::with('dibuatOleh')
                ->when($request->tahun, fn ($q, $y) => $q->where('periode_tahun', $y));

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('periode', fn ($l) => $l->periode_label)
                ->addColumn('dibuat_oleh', fn ($l) => $l->dibuatOleh?->name)
                ->addColumn('status_badge', fn ($l) => $this->statusBadge($l))
                ->addColumn('action', fn ($l) => $this->actionButtons($l))
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('laporan-bama.index', ['tahun' => request('tahun', now()->year)]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()->canAny(['laporan_bama.buat']), 403);
        return view('laporan-bama.create', ['laporan' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->can('laporan_bama.buat'), 403);

        $data = $request->validate([
            'periode_bulan'       => 'required|integer|min:1|max:12',
            'periode_tahun'       => 'required|integer|min:2020|max:2100',
            'ringkasan_eksekutif' => 'nullable|string',
            'rekomendasi'         => 'nullable|array|max:3',
            'rekomendasi.*'       => 'nullable|string|max:500',
            'permasalahan'        => 'nullable|array',
            'permasalahan.*.jenis'              => 'nullable|string|max:100',
            'permasalahan.*.uraian'             => 'nullable|string|max:1000',
            'permasalahan.*.dampak'             => 'nullable|string|max:500',
            'permasalahan.*.frekuensi'          => 'nullable|string|max:100',
            'permasalahan.*.tindak_lanjut'      => 'nullable|string|max:500',
            'permasalahan.*.status_tindak_lanjut' => 'nullable|string|max:100',
        ]);

        $data['status']   = LaporanBama::STATUS_DRAFT;
        $data['dibuat_by'] = auth()->id();
        $data['rekomendasi']  = array_filter($data['rekomendasi'] ?? []);
        $data['permasalahan'] = array_values(array_filter($data['permasalahan'] ?? [], fn($p) => !empty($p['uraian'])));

        $laporan = LaporanBama::create($data);

        return redirect()->route('laporan-bama.show', $laporan)
            ->with('success', 'Laporan BAMA berhasil dibuat: ' . $laporan->periode_label);
    }

    public function show(LaporanBama $laporanBama): View
    {
        abort_unless(auth()->user()->can('laporan_bama.view'), 403);

        $laporanBama->load(['dibuatOleh', 'disetujuiWadirOleh', 'disetujuiKpaOleh']);
        $data = $this->queryBabData($laporanBama->periode_bulan, $laporanBama->periode_tahun);

        return view('laporan-bama.show', compact('laporanBama', 'data'));
    }

    public function edit(LaporanBama $laporanBama): View
    {
        abort_unless(auth()->user()->can('laporan_bama.buat'), 403);
        abort_unless($laporanBama->status === LaporanBama::STATUS_DRAFT, 403);
        return view('laporan-bama.create', ['laporan' => $laporanBama]);
    }

    public function update(Request $request, LaporanBama $laporanBama): RedirectResponse
    {
        abort_unless(auth()->user()->can('laporan_bama.buat'), 403);
        abort_unless($laporanBama->status === LaporanBama::STATUS_DRAFT, 403);

        $data = $request->validate([
            'ringkasan_eksekutif' => 'nullable|string',
            'rekomendasi'         => 'nullable|array|max:3',
            'rekomendasi.*'       => 'nullable|string|max:500',
            'permasalahan'        => 'nullable|array',
            'permasalahan.*.jenis'              => 'nullable|string|max:100',
            'permasalahan.*.uraian'             => 'nullable|string|max:1000',
            'permasalahan.*.dampak'             => 'nullable|string|max:500',
            'permasalahan.*.frekuensi'          => 'nullable|string|max:100',
            'permasalahan.*.tindak_lanjut'      => 'nullable|string|max:500',
            'permasalahan.*.status_tindak_lanjut' => 'nullable|string|max:100',
        ]);

        $data['rekomendasi']  = array_filter($data['rekomendasi'] ?? []);
        $data['permasalahan'] = array_values(array_filter($data['permasalahan'] ?? [], fn($p) => !empty($p['uraian'])));

        $laporanBama->update($data);

        return redirect()->route('laporan-bama.show', $laporanBama)
            ->with('success', 'Laporan berhasil diperbarui.');
    }

    public function generatePdf(LaporanBama $laporanBama)
    {
        abort_unless(auth()->user()->can('laporan_bama.finalisasi'), 403);

        $laporanBama->load(['dibuatOleh', 'disetujuiWadirOleh', 'disetujuiKpaOleh']);
        $data = $this->queryBabData($laporanBama->periode_bulan, $laporanBama->periode_tahun);
        $satker = config('satker');

        $pdf = Pdf::loadView('pdf.laporan-bulanan-bama', compact('laporanBama', 'data', 'satker'))
            ->setPaper('a4', 'portrait');

        $filename = 'laporan-bama-' . $laporanBama->periode_tahun . '-' . str_pad($laporanBama->periode_bulan, 2, '0', STR_PAD_LEFT) . '.pdf';
        $path     = 'laporan-bama/' . $filename;

        Storage::disk('public')->put($path, $pdf->output());
        $laporanBama->update(['file_pdf' => $path]);

        return $pdf->download($filename);
    }

    public function generateDocx(LaporanBama $laporanBama)
    {
        abort_unless(auth()->user()->can('laporan_bama.finalisasi'), 403);

        $laporanBama->load(['dibuatOleh', 'disetujuiWadirOleh', 'disetujuiKpaOleh']);
        $data   = $this->queryBabData($laporanBama->periode_bulan, $laporanBama->periode_tahun);
        $satker = config('satker');

        $phpWord  = $this->buildDocx($laporanBama, $data, $satker);
        $filename = 'laporan-bama-' . $laporanBama->periode_tahun . '-' . str_pad($laporanBama->periode_bulan, 2, '0', STR_PAD_LEFT) . '.docx';
        $tmpPath  = storage_path('app/tmp/' . $filename);

        if (!is_dir(storage_path('app/tmp'))) {
            mkdir(storage_path('app/tmp'), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tmpPath);

        // Save to public disk
        $storagePath = 'laporan-bama/' . $filename;
        Storage::disk('public')->put($storagePath, file_get_contents($tmpPath));
        $laporanBama->update(['file_docx' => $storagePath]);
        unlink($tmpPath);

        return response()->download(storage_path('app/public/' . $storagePath), $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function setujuiWadir(LaporanBama $laporanBama): RedirectResponse
    {
        abort_unless(auth()->user()->can('laporan_bama.setujui'), 403);
        abort_unless($laporanBama->status === LaporanBama::STATUS_DRAFT, 403);

        $laporanBama->update([
            'status'              => LaporanBama::STATUS_DISETUJUI_WADIR,
            'disetujui_wadir_by'  => auth()->id(),
            'disetujui_wadir_at'  => now(),
        ]);

        return back()->with('success', 'Laporan BAMA disetujui Wadir III.');
    }

    public function setujuiKpa(LaporanBama $laporanBama): RedirectResponse
    {
        abort_unless(auth()->user()->can('laporan_bama.setujui'), 403);
        abort_unless($laporanBama->status === LaporanBama::STATUS_DISETUJUI_WADIR, 403);

        $laporanBama->update([
            'status'            => LaporanBama::STATUS_DISETUJUI_KPA,
            'disetujui_kpa_by'  => auth()->id(),
            'disetujui_kpa_at'  => now(),
        ]);

        return back()->with('success', 'Laporan BAMA disetujui KPA/Direktur.');
    }

    public function kirimPusdik(LaporanBama $laporanBama): RedirectResponse
    {
        abort_unless(auth()->user()->can('laporan_bama.finalisasi'), 403);
        abort_unless($laporanBama->status === LaporanBama::STATUS_DISETUJUI_KPA, 403);

        $laporanBama->update([
            'status'             => LaporanBama::STATUS_DIKIRIM_PUSDIK,
            'dikirim_pusdik_at'  => now(),
        ]);

        $msg = 'Laporan BAMA berhasil ditandai sebagai sudah dikirim ke Pusdik KP.';
        if ($laporanBama->terlambat) {
            $msg .= ' ⚠ Laporan ini melewati batas pengiriman tanggal 10 bulan berikutnya.';
        }

        return back()->with('success', $msg);
    }

    // ── Query Data per Bab ────────────────────────────────────────────

    public function queryBabData(int $bulan, int $tahun): array
    {
        $awalBulan  = \Carbon\Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        $kontrakAktif = KontrakMakan::where('status', 'aktif')->latest()->first();
        $paguTahun    = PaguAnggaran::where('tahun', $tahun)->sum('nilai_pagu');

        $tarunaEligible   = Taruna::eligibleBantuan()->count();
        $tarunaTotal      = Taruna::count();

        // Rekap periode ini
        $rekapList = RekapBulanan::with(['taruna.rekening'])
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->get();

        // Pembayaran dalam kampus (SP2D terbit atau lebih)
        $pembayaranDalamKampus = PengajuanPembayaran::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->whereIn('status', ['sp2d_terbit', 'transfer_selesai', 'selesai'])
            ->get();

        // Pembayaran luar kampus periode ini
        $pembayaranLuarKampus = PembayaranLuarKampus::whereHas('kegiatan', function ($q) use ($awalBulan, $akhirBulan) {
            $q->whereDate('tanggal_mulai', '<=', $akhirBulan)
              ->whereDate('tanggal_selesai', '>=', $awalBulan);
        })
        ->whereIn('status', [
            PembayaranLuarKampus::STATUS_SP2D_TERBIT,
            PembayaranLuarKampus::STATUS_TRANSFER_SELESAI,
            PembayaranLuarKampus::STATUS_DIKONFIRMASI_TARUNA,
        ])
        ->with('kegiatan.peserta.taruna')
        ->get();

        // Kegiatan luar kampus aktif periode ini
        $kegiatanLuar = KegiatanLuarKampus::whereDate('tanggal_mulai', '<=', $akhirBulan)
            ->whereDate('tanggal_selesai', '>=', $awalBulan)
            ->where('status', '!=', KegiatanLuarKampus::STATUS_DIBATALKAN)
            ->with('peserta.taruna')
            ->get();

        // Pemesanan dalam kampus periode ini
        $pemesananBulanIni = PemesananHarian::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $totalDalamKampus = $rekapList->sum('nilai_bantuan');
        $totalLuarKampus  = $pembayaranLuarKampus->sum('nilai_disetujui');

        return compact(
            'kontrakAktif', 'paguTahun', 'tarunaEligible', 'tarunaTotal',
            'rekapList', 'pembayaranDalamKampus', 'pembayaranLuarKampus',
            'kegiatanLuar', 'pemesananBulanIni',
            'totalDalamKampus', 'totalLuarKampus',
            'awalBulan', 'akhirBulan'
        );
    }

    // ── DOCX Builder ─────────────────────────────────────────────────

    private function buildDocx(LaporanBama $laporan, array $data, array $satker): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        // Section A4 dengan margin 2.5cm
        $section = $phpWord->addSection([
            'marginTop'    => Converter::cmToTwip(2.5),
            'marginBottom' => Converter::cmToTwip(2.5),
            'marginLeft'   => Converter::cmToTwip(2.5),
            'marginRight'  => Converter::cmToTwip(2.5),
        ]);

        $styleJudul = ['bold' => true, 'size' => 13, 'allCaps' => true];
        $styleBab   = ['bold' => true, 'size' => 12];
        $styleLabel = ['bold' => true, 'size' => 11];

        // ── COVER ──
        $section->addTextBreak(4);
        $section->addText('LAPORAN BULANAN', array_merge($styleJudul, ['size' => 14]), ['alignment' => 'center']);
        $section->addText('PEMANTAUAN DAN EVALUASI', $styleJudul, ['alignment' => 'center']);
        $section->addText('BANTUAN BIAYA MAKAN PESERTA DIDIK', $styleJudul, ['alignment' => 'center']);
        $section->addTextBreak(1);
        $section->addText(strtoupper($satker['nama']), $styleLabel, ['alignment' => 'center']);
        $section->addTextBreak(1);
        $section->addText('Periode: ' . $laporan->periode_label, ['size' => 12], ['alignment' => 'center']);
        $section->addTextBreak(3);
        $section->addText('KEMENTERIAN KELAUTAN DAN PERIKANAN', ['size' => 11], ['alignment' => 'center']);
        $section->addText('BADAN PENYULUHAN DAN PENGEMBANGAN SDM KP', ['size' => 11], ['alignment' => 'center']);
        $section->addText('TAHUN ' . $laporan->periode_tahun, ['size' => 11], ['alignment' => 'center']);
        $section->addPageBreak();

        // ── BAB I ──
        $section->addText('BAB I. INFORMASI UMUM', $styleBab);
        $section->addTextBreak(1);
        $tableStyle = ['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80];
        $tbl = $section->addTable($tableStyle);
        foreach ([
            ['Nama Satuan Pendidikan', $satker['nama']],
            ['Alamat', $satker['alamat']],
            ['Nama Pimpinan (KPA)', $satker['kpa']['nama']],
            ['NIP', $satker['kpa']['nip']],
            ['Telepon', $satker['telepon']],
            ['Email', $satker['email']],
            ['Periode Laporan', $laporan->periode_label],
            ['Tanggal Penyusunan', now()->format('d F Y')],
        ] as [$label, $val]) {
            $row = $tbl->addRow();
            $row->addCell(3000)->addText($label, $styleLabel);
            $row->addCell(6000)->addText($val ?? '-');
        }
        $section->addPageBreak();

        // ── BAB II ──
        $section->addText('BAB II. RINGKASAN EKSEKUTIF', $styleBab);
        $section->addTextBreak(1);
        $section->addText($laporan->ringkasan_eksekutif ?? '(belum diisi)');
        $section->addPageBreak();

        // ── BAB V (Ringkasan Realisasi) ──
        $section->addText('BAB V. DATA PENERIMA DAN REALISASI PENYALURAN', $styleBab);
        $section->addTextBreak(1);

        $tblv = $section->addTable($tableStyle);
        $hdr  = $tblv->addRow();
        foreach (['No','Nama / NIT','Program Studi','Status','Nomor Rekening','Jumlah Hari','Total Diterima'] as $h) {
            $hdr->addCell(1200)->addText($h, $styleLabel);
        }
        foreach ($data['rekapList'] as $i => $r) {
            $row = $tblv->addRow();
            $row->addCell(400)->addText($i + 1);
            $row->addCell(2000)->addText(($r->taruna?->nama ?? '') . "\n" . ($r->taruna?->nit ?? ''));
            $row->addCell(1500)->addText($r->taruna?->prodi ?? '-');
            $row->addCell(1200)->addText('Dalam Kampus');
            $row->addCell(1800)->addText($r->taruna?->rekening?->nomor_rekening ?? '-');
            $row->addCell(800)->addText($r->total_porsi);
            $row->addCell(1500)->addText('Rp ' . number_format($r->nilai_bantuan, 0, ',', '.'));
        }

        $totalRow = $tblv->addRow();
        $totalRow->addCell(400)->addText('');
        $totalRow->addCell(2000)->addText('TOTAL', $styleLabel);
        $totalRow->addCell(1500)->addText('');
        $totalRow->addCell(1200)->addText('');
        $totalRow->addCell(1800)->addText('');
        $totalRow->addCell(800)->addText($data['rekapList']->sum('total_porsi'), $styleLabel);
        $totalRow->addCell(1500)->addText('Rp ' . number_format($data['totalDalamKampus'], 0, ',', '.'), $styleLabel);
        $section->addPageBreak();

        // ── BAB VI ──
        $section->addText('BAB VI. PERMASALAHAN DAN TINDAK LANJUT', $styleBab);
        $section->addTextBreak(1);
        if (!empty($laporan->permasalahan)) {
            $tblp = $section->addTable($tableStyle);
            $ph   = $tblp->addRow();
            foreach (['No','Jenis','Uraian Permasalahan','Dampak','Frekuensi','Tindak Lanjut','Status TL'] as $h) {
                $ph->addCell(900)->addText($h, $styleLabel);
            }
            foreach ($laporan->permasalahan as $i => $p) {
                $pr = $tblp->addRow();
                $pr->addCell(400)->addText($i + 1);
                $pr->addCell(900)->addText($p['jenis'] ?? '-');
                $pr->addCell(2000)->addText($p['uraian'] ?? '-');
                $pr->addCell(1000)->addText($p['dampak'] ?? '-');
                $pr->addCell(800)->addText($p['frekuensi'] ?? '-');
                $pr->addCell(2000)->addText($p['tindak_lanjut'] ?? '-');
                $pr->addCell(900)->addText($p['status_tindak_lanjut'] ?? '-');
            }
        } else {
            $section->addText('Tidak ada permasalahan yang dilaporkan.');
        }
        $section->addPageBreak();

        // ── BAB VIII ──
        $section->addText('BAB VIII. REKOMENDASI', $styleBab);
        $section->addTextBreak(1);
        foreach ($laporan->rekomendasi ?? [] as $i => $rek) {
            $section->addText(($i + 1) . '. ' . $rek);
        }
        $section->addPageBreak();

        // ── BAB IX Penutup + TTD ──
        $section->addText('BAB IX. PENUTUP', $styleBab);
        $section->addTextBreak(1);
        $section->addText(
            'Demikian laporan bulanan pemantauan dan evaluasi bantuan biaya makan peserta didik ' .
            $satker['nama_singkat'] . ' periode ' . $laporan->periode_label .
            ' ini disusun sebagai bentuk pertanggungjawaban pengelolaan anggaran kepada Kepala Badan ' .
            'Penyuluhan dan Pengembangan SDM Kelautan dan Perikanan.'
        );
        $section->addTextBreak(3);

        $tblTtd = $section->addTable(['borderSize' => 0]);
        $ttdRow = $tblTtd->addRow();
        $kiri   = $ttdRow->addCell(4500);
        $kanan  = $ttdRow->addCell(4500);

        $kiri->addText('Mengetahui/Menyetujui,', [], ['alignment' => 'center']);
        $kiri->addText('Direktur Politeknik KP Sorong', [], ['alignment' => 'center']);
        $kiri->addTextBreak(4);
        $kiri->addText($satker['kpa']['nama'], $styleLabel, ['alignment' => 'center']);
        $kiri->addText('NIP. ' . $satker['kpa']['nip'], [], ['alignment' => 'center']);

        $kanan->addText('Yang Melaporkan,', [], ['alignment' => 'center']);
        $kanan->addText('Pejabat Pembuat Komitmen (PPK)', [], ['alignment' => 'center']);
        $kanan->addTextBreak(4);
        $kanan->addText($satker['ppk']['nama'], $styleLabel, ['alignment' => 'center']);
        $kanan->addText('NIP. ' . $satker['ppk']['nip'], [], ['alignment' => 'center']);

        // ── Lampiran ──
        $section->addPageBreak();
        $section->addText('LAMPIRAN', $styleBab);
        $section->addTextBreak(1);
        $lampiran = [
            'Daftar penerima bantuan biaya makan lengkap',
            'SK Kepala Badan tentang penetapan penerima',
            'SK KPA tentang penetapan penerima',
            'SK PPK tentang nomor rekening dan nilai bayar',
            'Rekapitulasi tanda tangan/daftar hadir penerima bantuan',
            'Bukti transfer bantuan biaya ke rekening penerima',
            'Berita acara kesepakatan penunjukan penyedia',
            'Dokumen kontrak/perjanjian dengan penyedia',
            'Dokumentasi kegiatan makan taruna (dengan geo tagging)',
            'Rekap data menu, jumlah porsi, dan taruna penerima makan',
            'SOP Pemberian Bantuan Uang Makan (Revisi sesuai rekomendasi Itjen T.947)',
            'Surat Tugas taruna di luar kampus',
            'Data jumlah peserta didik dengan status aktif dan tunda',
            'Berita acara penyelesaian masalah (jika ada)',
            'Bukti setor pengembalian kelebihan dana ke Kas Negara (jika ada)',
            'Lain-lain (matriks MR / dokumen pendukung lainnya)',
        ];
        $tblL = $section->addTable($tableStyle);
        $lh   = $tblL->addRow();
        $lh->addCell(500)->addText('No', $styleLabel);
        $lh->addCell(5500)->addText('Nama Lampiran', $styleLabel);
        $lh->addCell(3000)->addText('Tautan / Keterangan', $styleLabel);
        foreach ($lampiran as $i => $l) {
            $lr = $tblL->addRow();
            $lr->addCell(500)->addText($i + 1);
            $lr->addCell(5500)->addText($l);
            $lr->addCell(3000)->addText($laporan->tautan_gdrive ?? '');
        }

        return $phpWord;
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function statusBadge(LaporanBama $l): string
    {
        $map = [
            LaporanBama::STATUS_DRAFT           => ['secondary', 'Draft'],
            LaporanBama::STATUS_DISETUJUI_WADIR => ['info',      'Disetujui Wadir III'],
            LaporanBama::STATUS_DISETUJUI_KPA   => ['success',   'Disetujui KPA'],
            LaporanBama::STATUS_DIKIRIM_PUSDIK  => ['dark',      'Dikirim Pusdik'],
        ];
        [$color, $label] = $map[$l->status] ?? ['secondary', $l->status];
        return '<span class="badge bg-' . $color . '">' . $label . '</span>';
    }

    private function actionButtons(LaporanBama $l): string
    {
        $btn = '<a href="' . route('laporan-bama.show', $l) . '" class="btn btn-sm btn-outline-info me-1"><i class="bi bi-eye"></i></a>';
        if ($l->status === LaporanBama::STATUS_DRAFT && auth()->user()->can('laporan_bama.buat')) {
            $btn .= '<a href="' . route('laporan-bama.edit', $l) . '" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>';
        }
        return $btn;
    }
}
