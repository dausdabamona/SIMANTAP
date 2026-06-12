<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 10px; margin: 15px; }
  h2, h3 { text-align: center; margin: 2px 0; }
  .subtitle { text-align: center; color: #555; margin-bottom: 10px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #1a3c5e; color: #fff; padding: 4px 6px; text-align: left; }
  td { border: 1px solid #ccc; padding: 3px 6px; }
  tr:nth-child(even) { background: #f5f5f5; }
  tfoot td { font-weight: bold; background: #e8e8e8; }
  .footer { text-align: right; font-size: 8px; color: #888; margin-top: 8px; }
</style>
</head>
<body>
  <h2>SIMANTAP — Politeknik KP Sorong</h2>
  <h3>Ringkasan Keuangan Bulanan Tahun {{ $tahun }}</h3>
  @if ($pagu)
  <p style="text-align:center">Pagu: Rp {{ number_format($pagu->nilai_pagu, 0, ',', '.') }} ({{ $pagu->akun_belanja }})</p>
  @endif
  <p class="subtitle">Dicetak: {{ $generatedAt }}</p>

  <table>
    <thead>
      <tr>
        <th>Bulan</th><th>Pengajuan</th><th>Nilai LS</th>
        <th>Transfer Penyedia</th><th>Invoice Penyedia</th><th>Status BAMA</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($rows as $row)
      <tr>
        <td>{{ $row['bulan'] }}</td>
        <td>{{ $row['jumlah_pengajuan'] }}</td>
        <td>Rp {{ number_format($row['total_nilai_pengajuan'], 0, ',', '.') }}</td>
        <td>Rp {{ number_format($row['total_transfer_penyedia'], 0, ',', '.') }}</td>
        <td>Rp {{ number_format($row['total_invoice_penyedia'], 0, ',', '.') }}</td>
        <td>{{ $row['status_laporan_bama'] ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td>Grand Total</td>
        <td>{{ collect($rows)->sum('jumlah_pengajuan') }}</td>
        <td>Rp {{ number_format(collect($rows)->sum('total_nilai_pengajuan'), 0, ',', '.') }}</td>
        <td>Rp {{ number_format(collect($rows)->sum('total_transfer_penyedia'), 0, ',', '.') }}</td>
        <td>Rp {{ number_format(collect($rows)->sum('total_invoice_penyedia'), 0, ',', '.') }}</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <p class="footer">Halaman {PAGE_NUM} dari {PAGE_COUNT}</p>
</body>
</html>
