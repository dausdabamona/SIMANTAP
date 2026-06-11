<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Pengajuan Pembayaran LS — {{ $pembayaran->nomor_pengajuan }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; margin: 20mm; color: #000; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px 6px; }
        .border td, .border th { border: 1px solid #000; }
        .center { text-align: center; }
        .right { text-align: right; }
        .title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
    </style>
</head>
<body>
    @include('pdf.partials.kop-surat')

    <p class="title">Dokumen Pengajuan Pembayaran Langsung (LS)</p>
    <p style="text-align:center;margin-bottom:16px;">
        Nomor: {{ $pembayaran->nomor_pengajuan }}
    </p>

    <table style="margin-bottom:12px;">
        <tr><td style="width:40%">Nomor Pengajuan</td><td>: {{ $pembayaran->nomor_pengajuan }}</td></tr>
        <tr><td>Periode</td><td>: {{ $pembayaran->nama_bulan }} {{ $pembayaran->periode_tahun }}</td></tr>
        <tr><td>Jumlah Taruna</td><td>: {{ number_format($pembayaran->total_taruna) }} orang</td></tr>
        <tr><td>Total Porsi</td><td>: {{ number_format($pembayaran->total_porsi) }} porsi</td></tr>
        <tr><td><strong>Total Nilai</strong></td><td><strong>: Rp {{ number_format($pembayaran->total_nilai, 0, ',', '.') }}</strong></td></tr>
        @if ($pembayaran->nomor_sp2d)
        <tr><td>Nomor SP2D</td><td>: {{ $pembayaran->nomor_sp2d }}</td></tr>
        <tr><td>Tanggal SP2D</td><td>: {{ $pembayaran->tanggal_sp2d?->format('d F Y') }}</td></tr>
        @endif
        <tr><td>Status</td><td>: {{ strtoupper(str_replace('_', ' ', $pembayaran->status)) }}</td></tr>
    </table>

    <p style="font-weight:bold;margin-top:12px;">Riwayat Alur Pembayaran:</p>
    <table class="border">
        <tr>
            <th>No</th>
            <th>Waktu</th>
            <th>Status</th>
            <th>Pengguna</th>
            <th>Catatan</th>
        </tr>
        @foreach ($pembayaran->workflow as $i => $wf)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $wf->created_at?->format('d/m/Y H:i') }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $wf->status_ke ?? 'Dibuat')) }}</td>
            <td>{{ $wf->user?->name ?? '-' }}</td>
            <td>{{ $wf->catatan }}</td>
        </tr>
        @endforeach
    </table>

    <div style="margin-top:40px;">
        <p>Sorong, {{ now()->format('d F Y') }}</p>
        <p>Pejabat Pembuat Komitmen</p>
        <br><br><br>
        <p style="border-top:1px solid #000;display:inline-block;min-width:200px;"><strong>___________________________</strong></p>
        <p style="font-size:9pt;">NIP. ____________________</p>
    </div>

    <div style="margin-top:20px;font-size:8pt;color:#666;text-align:center;">
        Dicetak oleh SIMANTAP — {{ now()->format('d F Y H:i') }} WIT
    </div>
</body>
</html>
