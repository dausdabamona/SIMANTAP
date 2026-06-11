<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Surat Pesanan Makan {{ $pemesanan->tanggal->format('d-m-Y') }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 20mm; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; }
        .border td, .border th { border: 1px solid #000; }
        .center { text-align: center; }
        .right { text-align: right; }
        .title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-surat')

    <p class="title">Surat Pesanan Makan Taruna</p>
    <p style="text-align:center;margin-bottom:16px;">Tanggal: {{ $pemesanan->tanggal->format('l, d F Y') }}</p>

    <table style="margin-bottom:12px;">
        <tr>
            <td style="width:35%">Kepada Yth.</td>
            <td>: {{ $pemesanan->kontrak?->penyedia?->nama ?? 'Penyedia Makan' }}</td>
        </tr>
        <tr>
            <td>Nomor Kontrak</td>
            <td>: {{ $pemesanan->kontrak?->nomor_kontrak }}</td>
        </tr>
        <tr>
            <td>Tanggal Makan</td>
            <td>: {{ $pemesanan->tanggal->format('d F Y') }}</td>
        </tr>
    </table>

    <p>Dengan hormat, bersama ini kami menyampaikan pesanan makan untuk:</p>

    <table class="border" style="margin:12px 0;">
        <tr>
            <th>Keterangan</th>
            <th class="center">Jumlah</th>
            <th class="right">Harga Satuan</th>
            <th class="right">Total</th>
        </tr>
        <tr>
            <td>Makan Taruna (sarapan, makan siang, makan malam)</td>
            <td class="center">{{ number_format($pemesanan->jumlah_taruna_hadir) }} orang × {{ config('simantap.porsi_per_hari', 3) }} porsi</td>
            <td class="right">Rp {{ number_format($pemesanan->harga_porsi_snapshot, 0, ',', '.') }}</td>
            <td class="right">Rp {{ number_format($pemesanan->nilai_total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="3" style="font-weight:bold;text-align:right;">TOTAL</td>
            <td class="right" style="font-weight:bold;">Rp {{ number_format($pemesanan->nilai_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if ($pemesanan->catatan_menu)
    <p><strong>Catatan Menu:</strong> {{ $pemesanan->catatan_menu }}</p>
    @endif

    <p>Demikian pesanan ini kami sampaikan. Atas perhatian dan kerjasamanya diucapkan terima kasih.</p>

    <table style="margin-top:40px;">
        <tr>
            <td style="width:50%;text-align:center;padding:0 20px;vertical-align:top;">
                <div>Mewakili Senat Taruna</div>
                <br><br><br><br>
                <div style="border-top:1px solid #000;padding-top:4px;">
                    <strong>{{ $pemesanan->ttdSenat?->name ?? '____________________' }}</strong>
                </div>
                <div style="font-size:8pt;">Ketua Senat Taruna</div>
            </td>
            <td style="width:50%;text-align:center;padding:0 20px;vertical-align:top;">
                <div>Diverifikasi oleh</div>
                <br><br><br><br>
                <div style="border-top:1px solid #000;padding-top:4px;">
                    <strong>{{ $pemesanan->ttdPembina?->name ?? '____________________' }}</strong>
                </div>
                <div style="font-size:8pt;">Pembina Karakter</div>
                @if ($pemesanan->ttdPembina?->nip)
                <div style="font-size:8pt;">NIP. {{ $pemesanan->ttdPembina->nip }}</div>
                @endif
            </td>
        </tr>
    </table>

    <div style="margin-top:20px;font-size:8pt;color:#666;text-align:center;">
        Dicetak oleh SIMANTAP — {{ now()->format('d F Y H:i') }} WIT
    </div>
</body>
</html>
