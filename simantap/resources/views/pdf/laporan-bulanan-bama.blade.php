<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; }
.page { margin: 0; padding: 0; }
.page-break { page-break-after: always; }

/* KOP SURAT */
.kop { display:table; width:100%; border-bottom:3px solid #003399; margin-bottom:10px; padding-bottom:6px; }
.kop-logo-kiri  { display:table-cell; width:60px; vertical-align:middle; }
.kop-tengah     { display:table-cell; text-align:center; vertical-align:middle; }
.kop-logo-kanan { display:table-cell; width:60px; vertical-align:middle; text-align:right; }
.kop-tengah h1  { font-size:14pt; font-weight:bold; text-transform:uppercase; color:#003399; line-height:1.2; }
.kop-tengah p   { font-size:9pt; margin-top:2px; }

/* COVER */
.cover { text-align:center; margin-top:80px; }
.cover .judul-besar { font-size:16pt; font-weight:bold; text-transform:uppercase; line-height:1.5; }
.cover .satdik { font-size:13pt; font-weight:bold; margin-top:20px; }
.cover .periode { font-size:12pt; margin-top:15px; }
.cover .kementerian { font-size:10pt; margin-top:40px; }

/* HEADING BAB */
h2 { font-size:12pt; font-weight:bold; text-transform:uppercase; margin:14px 0 6px 0; }
h3 { font-size:11pt; font-weight:bold; margin:10px 0 4px 0; }

/* TABEL */
table { width:100%; border-collapse:collapse; margin-bottom:10px; font-size:10pt; }
th, td { border:1px solid #444; padding:4px 6px; vertical-align:top; }
th { background:#dce6f1; font-weight:bold; text-align:center; }
td.label { font-weight:bold; width:35%; }
.tbl-info td { border:1px solid #888; }

/* FOOTER TANDA TANGAN */
.ttd-wrap { display:table; width:100%; margin-top:30px; }
.ttd-kiri  { display:table-cell; width:50%; text-align:center; }
.ttd-kanan { display:table-cell; width:50%; text-align:center; }
.ttd-space { height:60px; }

/* LAMPIRAN */
ol.lampiran li { margin-bottom:3px; font-size:10pt; }
</style>
</head>
<body>

{{-- ═══════════════ COVER ═══════════════ --}}
<div class="page">
    <div style="text-align:center; margin-top:120px;">
        <p style="font-size:10pt">KEMENTERIAN KELAUTAN DAN PERIKANAN</p>
        <p style="font-size:10pt">BADAN PENYULUHAN DAN PENGEMBANGAN SDM KP</p>
        <hr style="border-top:2px solid #003399; margin:10px auto; width:60%">
        <div style="margin-top:60px;" class="judul-besar">
            <p style="font-size:14pt; font-weight:bold; text-transform:uppercase">LAPORAN BULANAN</p>
            <p style="font-size:13pt; font-weight:bold; text-transform:uppercase; margin-top:6px">PEMANTAUAN DAN EVALUASI</p>
            <p style="font-size:13pt; font-weight:bold; text-transform:uppercase; margin-top:6px">BANTUAN BIAYA MAKAN PESERTA DIDIK</p>
        </div>
        <div style="margin-top:40px;">
            <p style="font-size:12pt; font-weight:bold; text-transform:uppercase">{{ strtoupper($satker['nama']) }}</p>
        </div>
        <div style="margin-top:30px; font-size:12pt;">
            <p>Periode : {{ $laporanBama->periode_label }}</p>
        </div>
        <div style="margin-top:50px; font-size:10pt; color:#444;">
            <p>{{ strtoupper($satker['alamat']) }}</p>
            <p>Tahun {{ $laporanBama->periode_tahun }}</p>
        </div>
    </div>
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB I ═══════════════ --}}
<div class="page">
    <h2>BAB I. INFORMASI UMUM</h2>
    <table class="tbl-info">
        <tr><td class="label">Nama Satuan Pendidikan</td><td>{{ $satker['nama'] }}</td></tr>
        <tr><td class="label">Alamat</td><td>{{ $satker['alamat'] }}</td></tr>
        <tr><td class="label">Nama Pimpinan (KPA)</td><td>{{ $satker['kpa']['nama'] }}</td></tr>
        <tr><td class="label">NIP</td><td>{{ $satker['kpa']['nip'] }}</td></tr>
        <tr><td class="label">Nomor Telepon</td><td>{{ $satker['telepon'] }}</td></tr>
        <tr><td class="label">Email</td><td>{{ $satker['email'] }}</td></tr>
        <tr><td class="label">Periode Laporan</td><td>{{ $laporanBama->periode_label }}</td></tr>
        <tr><td class="label">Tanggal Penyusunan</td><td>{{ now()->format('d F Y') }}</td></tr>
    </table>
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB II ═══════════════ --}}
<div class="page">
    <h2>BAB II. RINGKASAN EKSEKUTIF</h2>
    <p style="text-align:justify; line-height:1.6; margin-top:8px;">
        {{ $laporanBama->ringkasan_eksekutif ?: '(belum diisi)' }}
    </p>
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB III ═══════════════ --}}
<div class="page">
    <h2>BAB III. PROSES PERENCANAAN BANTUAN BIAYA</h2>

    <h3>A. Dasar Perencanaan</h3>
    <table class="tbl-info">
        <tr><td class="label">1. DIPA Satuan Kerja</td><td>{{ $satker['nomor_dipa'] }}</td></tr>
        <tr><td class="label">2. Pagu Anggaran Bantuan Makan</td><td>Rp {{ number_format($data['paguTahun'], 0, ',', '.') }}</td></tr>
        <tr><td class="label">3. Standar Biaya Masukan (SBM)</td>
            <td>Rp {{ number_format($data['kontrakAktif']?->harga_porsi ?? 0, 0, ',', '.') }} / porsi
                (Kontrak: {{ $data['kontrakAktif']?->nomor_kontrak ?? '-' }})</td></tr>
        <tr><td class="label">4. Jumlah Taruna Penerima SK</td><td>{{ $data['tarunaTotal'] }} taruna</td></tr>
        <tr><td class="label">5. Jumlah Taruna Eligible</td><td>{{ $data['tarunaEligible'] }} taruna</td></tr>
    </table>

    <h3>B. Rencana Kebutuhan Bulanan</h3>
    <table>
        <tr><th>No</th><th>Uraian</th><th>Jumlah</th></tr>
        <tr><td>1</td><td>Jumlah Peserta Didik Penerima Bantuan</td><td>{{ $data['tarunaEligible'] }} orang</td></tr>
        <tr><td>2</td><td>Standar Biaya / Hari / Orang</td><td>Rp {{ number_format($data['kontrakAktif']?->harga_porsi ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>3</td><td>Total Realisasi Dalam Kampus</td><td>Rp {{ number_format($data['totalDalamKampus'], 0, ',', '.') }}</td></tr>
        <tr><td>4</td><td>Total Realisasi Luar Kampus</td><td>Rp {{ number_format($data['totalLuarKampus'], 0, ',', '.') }}</td></tr>
        <tr><td>5</td><td><strong>Total Realisasi Keseluruhan</strong></td>
            <td><strong>Rp {{ number_format($data['totalDalamKampus'] + $data['totalLuarKampus'], 0, ',', '.') }}</strong></td></tr>
    </table>

    @if ($data['kegiatanLuar']->isNotEmpty())
    <h3>C. Rencana Kegiatan Luar Kampus Bulan Ini</h3>
    <table>
        <tr><th>No</th><th>Nama Kegiatan</th><th>Jml Peserta</th><th>Lokasi</th><th>Mulai</th><th>Selesai</th><th>Rencana Biaya</th></tr>
        @foreach ($data['kegiatanLuar'] as $i => $k)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $k->nama_kegiatan }}</td>
            <td>{{ $k->peserta->count() }}</td>
            <td>{{ $k->lokasi }}</td>
            <td>{{ $k->tanggal_mulai->format('d/m/Y') }}</td>
            <td>{{ $k->tanggal_selesai->format('d/m/Y') }}</td>
            <td>Rp {{ number_format($k->total_nilai_disetujui, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    @endif
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB IV ═══════════════ --}}
<div class="page">
    <h2>BAB IV. PROSES PENGUSULAN</h2>
    <table class="tbl-info">
        <tr><td class="label">1. Jumlah Peserta Didik Mengajukan</td><td>{{ $data['tarunaTotal'] }} orang</td></tr>
        <tr><td class="label">2. Memenuhi Syarat</td><td>{{ $data['tarunaEligible'] }} orang</td></tr>
        <tr><td class="label">3. Tidak Memenuhi Syarat</td><td>{{ $data['tarunaTotal'] - $data['tarunaEligible'] }} orang</td></tr>
        <tr><td class="label">4. Diverifikasi & Lengkap</td><td>{{ $data['rekapList']->count() }} orang (data rekap bulanan tersedia)</td></tr>
    </table>
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB V ═══════════════ --}}
<div class="page">
    <h2>BAB V. PROSES PELAKSANAAN</h2>

    <h3>A. Data Penerima Bantuan Bulan Ini</h3>
    <p style="font-size:9pt; margin-bottom:4px;">(Daftar lengkap penerima terlampir)</p>
    <table>
        <tr><th>No</th><th>Nama / NIT</th><th>Program Studi</th><th>Status</th><th>No. Rekening</th><th>Porsi</th><th>Total</th></tr>
        @foreach ($data['rekapList'] as $i => $r)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $r->taruna?->nama }}<br><small>{{ $r->taruna?->nit }}</small></td>
            <td>{{ $r->taruna?->prodi }}</td>
            <td>Dalam Kampus</td>
            <td>{{ $r->taruna?->rekeningTaruna?->nomor_rekening ?? '-' }}</td>
            <td>{{ $r->total_porsi }}</td>
            <td>Rp {{ number_format($r->nilai_bantuan, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5"><strong>TOTAL</strong></td>
            <td><strong>{{ $data['rekapList']->sum('total_porsi') }}</strong></td>
            <td><strong>Rp {{ number_format($data['totalDalamKampus'], 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    @if ($data['pembayaranLuarKampus']->isNotEmpty())
    <h3>B. Realisasi Penyaluran Luar Kampus</h3>
    <table>
        <tr><th>No</th><th>Kegiatan</th><th>Tahap</th><th>Jml Peserta</th><th>Nilai SP2D</th><th>Status</th></tr>
        @foreach ($data['pembayaranLuarKampus'] as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $p->kegiatan?->nama_kegiatan }}</td>
            <td>{{ $p->tahap }}</td>
            <td>{{ $p->kegiatan?->peserta->count() }}</td>
            <td>Rp {{ number_format($p->nilai_disetujui, 0, ',', '.') }}</td>
            <td>{{ str_replace('_', ' ', $p->status) }}</td>
        </tr>
        @endforeach
    </table>
    @endif
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB VI ═══════════════ --}}
<div class="page">
    <h2>BAB VI. PERMASALAHAN DAN TINDAK LANJUT</h2>
    @if (!empty($laporanBama->permasalahan))
    <table>
        <tr><th>No</th><th>Jenis</th><th>Uraian Permasalahan</th><th>Dampak</th><th>Frekuensi</th><th>Tindak Lanjut</th><th>Status TL</th></tr>
        @foreach ($laporanBama->permasalahan as $i => $p)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $p['jenis'] ?? '-' }}</td>
            <td>{{ $p['uraian'] ?? '-' }}</td>
            <td>{{ $p['dampak'] ?? '-' }}</td>
            <td>{{ $p['frekuensi'] ?? '-' }}</td>
            <td>{{ $p['tindak_lanjut'] ?? '-' }}</td>
            <td>{{ ucwords(str_replace('_', ' ', $p['status_tindak_lanjut'] ?? '-')) }}</td>
        </tr>
        @endforeach
    </table>
    @else
    <p>Tidak ada permasalahan yang dilaporkan pada periode ini.</p>
    @endif
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB VII ═══════════════ --}}
<div class="page">
    <h2>BAB VII. EVALUASI</h2>
    <h3>A. Realisasi Keuangan</h3>
    <table>
        <tr><th>Uraian</th><th>Realisasi (Rp)</th></tr>
        <tr><td>Bantuan Dalam Kampus</td><td>Rp {{ number_format($data['totalDalamKampus'], 0, ',', '.') }}</td></tr>
        <tr><td>Bantuan Luar Kampus</td><td>Rp {{ number_format($data['totalLuarKampus'], 0, ',', '.') }}</td></tr>
        <tr><td><strong>Total</strong></td><td><strong>Rp {{ number_format($data['totalDalamKampus'] + $data['totalLuarKampus'], 0, ',', '.') }}</strong></td></tr>
    </table>
    <h3>B. Realisasi Fisik</h3>
    <table>
        <tr><th>Uraian</th><th>Realisasi</th></tr>
        <tr><td>Penerima Dalam Kampus</td><td>{{ $data['rekapList']->count() }} taruna</td></tr>
        <tr><td>Penerima Luar Kampus</td>
            <td>{{ $data['pembayaranLuarKampus']->map(fn($p) => $p->kegiatan?->peserta->count())->sum() }} taruna</td></tr>
    </table>
    <h3>C. Pengendalian Risiko</h3>
    <p>Lihat Lampiran Matriks Risiko.</p>
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB VIII ═══════════════ --}}
<div class="page">
    <h2>BAB VIII. REKOMENDASI</h2>
    @if (!empty($laporanBama->rekomendasi))
    <ol>
        @foreach ($laporanBama->rekomendasi as $rek)
        @if ($rek)
        <li style="margin-bottom:6px; line-height:1.5;">{{ $rek }}</li>
        @endif
        @endforeach
    </ol>
    @else
    <p>(Belum ada rekomendasi yang diinput)</p>
    @endif
</div>
<div class="page-break"></div>

{{-- ═══════════════ BAB IX + TTD ═══════════════ --}}
<div class="page">
    <h2>BAB IX. PENUTUP</h2>
    <p style="text-align:justify; line-height:1.6; margin-top:8px;">
        Demikian laporan bulanan pemantauan dan evaluasi bantuan biaya makan peserta didik
        {{ $satker['nama'] }} periode {{ $laporanBama->periode_label }} ini disusun sebagai
        bentuk pertanggungjawaban pengelolaan anggaran kepada Kepala Badan Penyuluhan dan
        Pengembangan SDM Kelautan dan Perikanan. Laporan ini memuat data riil pelaksanaan
        dan disampaikan paling lambat tanggal 10 bulan berikutnya sesuai ketentuan yang berlaku.
    </p>

    <div class="ttd-wrap" style="margin-top:40px;">
        <div class="ttd-kiri">
            <p>Mengetahui/Menyetujui,</p>
            <p>Direktur Politeknik KP Sorong</p>
            <div class="ttd-space"></div>
            <p><strong>{{ $satker['kpa']['nama'] }}</strong></p>
            <p>NIP. {{ $satker['kpa']['nip'] }}</p>
        </div>
        <div class="ttd-kanan">
            <p>{{ $laporanBama->dikirim_pusdik_at ? $laporanBama->dikirim_pusdik_at->format('d F Y') : 'Sorong, ' . now()->format('d F Y') }}</p>
            <p>Pejabat Pembuat Komitmen (PPK)</p>
            <div class="ttd-space"></div>
            <p><strong>{{ $satker['ppk']['nama'] }}</strong></p>
            <p>NIP. {{ $satker['ppk']['nip'] }}</p>
        </div>
    </div>
</div>
<div class="page-break"></div>

{{-- ═══════════════ LAMPIRAN ═══════════════ --}}
<div class="page">
    <h2>LAMPIRAN</h2>
    <table style="margin-top:8px;">
        <tr><th style="width:40px">No</th><th>Nama Lampiran</th><th style="width:200px">Tautan / Keterangan</th></tr>
        @foreach ([
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
        ] as $i => $lamp)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $lamp }}</td>
            <td>{{ $laporanBama->tautan_gdrive ?: '' }}</td>
        </tr>
        @endforeach
    </table>
</div>

</body>
</html>
