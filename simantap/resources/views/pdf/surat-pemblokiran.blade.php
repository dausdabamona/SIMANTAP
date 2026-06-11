<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Surat Pemblokiran Uang Makan — {{ $pemblokiran->taruna?->nama }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 20mm; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        .title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
        .info-row td { padding: 3px 0; }
        p { margin: 6px 0; line-height: 1.6; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-surat')

    @if ($pemblokiran->nomor_surat_pemblokiran)
    <p style="text-align:right;margin-bottom:4px;">
        Nomor: {{ $pemblokiran->nomor_surat_pemblokiran }}<br>
        Sorong, {{ $pemblokiran->tanggal_surat?->format('d F Y') ?? now()->format('d F Y') }}
    </p>
    @endif

    <p class="title">Surat Pemblokiran Bantuan Uang Makan Taruna</p>

    <p>Berdasarkan ketentuan yang berlaku, dengan ini kami memberitahukan pemblokiran bantuan uang makan taruna sebagai berikut:</p>

    <table class="info-row" style="margin:12px 0 20px 0;">
        <tr><td style="width:40%">Nama Taruna</td><td>: {{ $pemblokiran->taruna?->nama }}</td></tr>
        <tr><td>NIT</td><td>: {{ $pemblokiran->taruna?->nit }}</td></tr>
        <tr><td>Periode</td><td>: {{ \App\Helpers\DateHelper::namaBulan($pemblokiran->periode_bulan) }} {{ $pemblokiran->periode_tahun }}</td></tr>
        <tr><td>Nilai Bantuan yang Diblokir</td><td>: Rp {{ number_format($pemblokiran->nilai_bantuan, 0, ',', '.') }}</td></tr>
        <tr><td>Rekening Target Debit</td>
            <td>: {{ $pemblokiran->senatAccount?->nama_rekening }}
                ({{ $pemblokiran->senatAccount?->bank }} — {{ $pemblokiran->senatAccount?->nomor_rekening }})</td></tr>
    </table>

    <p>{{ $pemblokiran->catatan ?? 'Pemblokiran dilakukan sesuai dengan prosedur yang berlaku di lingkungan Politeknik KP Sorong.' }}</p>

    <p>Demikian surat pemblokiran ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <div style="margin-top:40px;">
        <p>Sorong, {{ now()->format('d F Y') }}</p>
        <p>Diusulkan oleh</p>
        <br><br><br>
        <p><strong>{{ $pemblokiran->diusulkanOleh?->name ?? '____________________________' }}</strong></p>
        <p style="font-size:9pt;">{{ $pemblokiran->diusulkanOleh?->jabatan ?? 'Jabatan' }}</p>
        @if ($pemblokiran->diusulkanOleh?->nip)
        <p style="font-size:9pt;">NIP. {{ $pemblokiran->diusulkanOleh->nip }}</p>
        @endif
    </div>

    <div style="margin-top:20px;font-size:8pt;color:#666;text-align:center;">
        Dicetak oleh SIMANTAP — {{ now()->format('d F Y H:i') }} WIT
    </div>
</body>
</html>
