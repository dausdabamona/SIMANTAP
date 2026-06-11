<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Rekap Periode {{ $bulan }}/{{ $tahun }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 9pt; margin: 15mm; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 5px; border: 1px solid #000; }
        th { background: #e5e7eb; font-weight: bold; text-align: center; }
        .title { font-size: 11pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
        .subtitle { font-size: 9pt; text-align: center; margin-bottom: 12px; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-surat')

    <p class="title">Rekapitulasi Bantuan Makan Taruna</p>
    <p class="subtitle">
        Periode: {{ \App\Helpers\DateHelper::namaBulan($bulan) }} {{ $tahun }}
        &nbsp;|&nbsp; Politeknik KP Sorong
    </p>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th>NIT</th>
                <th>Nama Taruna</th>
                <th>Prodi</th>
                <th>Kelas</th>
                <th class="center">Total Porsi</th>
                <th class="right">Nilai Bantuan</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; $grandTotal = 0; @endphp
            @foreach ($rekapList as $r)
            @php $no++; $grandTotal += $r->nilai_bantuan; @endphp
            <tr>
                <td class="center">{{ $no }}</td>
                <td>{{ $r->taruna?->nit }}</td>
                <td>{{ $r->taruna?->nama }}</td>
                <td>{{ $r->taruna?->prodi }}</td>
                <td class="center">{{ $r->taruna?->kelas }}</td>
                <td class="center">{{ number_format($r->total_porsi) }}</td>
                <td class="right">Rp {{ number_format($r->nilai_bantuan, 0, ',', '.') }}</td>
                <td class="center">{{ strtoupper(str_replace('_', ' ', $r->status)) }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="6" style="font-weight:bold;text-align:right;">TOTAL</td>
                <td class="right" style="font-weight:bold;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:30px;font-size:8pt;color:#666;text-align:right;">
        Dicetak: {{ now()->format('d F Y H:i') }} WIT
    </div>
</body>
</html>
