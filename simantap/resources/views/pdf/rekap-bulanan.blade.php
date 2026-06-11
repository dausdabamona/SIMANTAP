<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Rekap Bulanan Bantuan Makan — {{ $rekap->taruna?->nama }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 20mm; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; font-size: 10pt; }
        .border td, .border th { border: 1px solid #000; }
        .center { text-align: center; }
        .right { text-align: right; }
        .title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
        .subtitle { font-size: 10pt; text-align: center; margin-bottom: 16px; }
        .ttd-box { width: 180px; text-align: center; display: inline-block; margin: 0 20px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-surat')

    <p class="title">Rekapitulasi Bantuan Makan Taruna</p>
    <p class="subtitle">
        Periode: {{ $rekap->periode_label }} &nbsp;|&nbsp;
        Taruna: {{ $rekap->taruna?->nama }} ({{ $rekap->taruna?->nit }})
    </p>

    <table class="border" style="margin-bottom:16px;">
        <tr>
            <th style="width:40%">Keterangan</th>
            <th>Nilai</th>
        </tr>
        <tr>
            <td>Nama Taruna</td>
            <td>{{ $rekap->taruna?->nama }}</td>
        </tr>
        <tr>
            <td>NIT</td>
            <td>{{ $rekap->taruna?->nit }}</td>
        </tr>
        <tr>
            <td>Program Studi / Kelas</td>
            <td>{{ $rekap->taruna?->prodi }} / {{ $rekap->taruna?->kelas }}</td>
        </tr>
        <tr>
            <td>Periode</td>
            <td>{{ $rekap->periode_label }}</td>
        </tr>
        <tr>
            <td>Kontrak Referensi</td>
            <td>{{ $rekap->kontrak?->nomor_kontrak }}</td>
        </tr>
        <tr>
            <td>Total Porsi Diterima</td>
            <td>{{ number_format($rekap->total_porsi) }} porsi</td>
        </tr>
        <tr>
            <td><strong>Nilai Bantuan Makan</strong></td>
            <td><strong>Rp {{ number_format($rekap->nilai_bantuan, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{{ strtoupper(str_replace('_', ' ', $rekap->status)) }}</td>
        </tr>
    </table>

    {{-- Tanda Tangan --}}
    <div style="margin-top:40px;">
        <table>
            <tr>
                @foreach ($rekap->approvals as $apv)
                <td style="width:33%;text-align:center;padding:0 10px;vertical-align:top;">
                    <div>{{ ucfirst(str_replace('_', ' ', $apv->role)) }}</div>
                    <br><br><br><br>
                    <div style="border-top:1px solid #000;padding-top:4px;">
                        <strong>{{ $apv->user?->name ?? '___________________' }}</strong>
                    </div>
                    <div style="font-size:8pt;">{{ $apv->user?->nip ? 'NIP. ' . $apv->user->nip : '' }}</div>
                </td>
                @endforeach
                @for ($i = $rekap->approvals->count(); $i < 3; $i++)
                <td style="width:33%;text-align:center;padding:0 10px;vertical-align:top;">
                    <div>{{ ['Pembina Karakter', 'Pejabat Pembuat Komitmen', 'Kuasa Pengguna Anggaran'][$i] }}</div>
                    <br><br><br><br>
                    <div style="border-top:1px solid #000;padding-top:4px;">
                        <strong>___________________</strong>
                    </div>
                    <div style="font-size:8pt;">NIP. ______________</div>
                </td>
                @endfor
            </tr>
        </table>
    </div>

    <div style="margin-top:30px;font-size:8pt;color:#666;text-align:center;">
        Dicetak oleh SIMANTAP — {{ now()->format('d F Y H:i') }} WIT
    </div>
</body>
</html>
