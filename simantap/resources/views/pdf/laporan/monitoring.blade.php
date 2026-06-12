<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 9px; margin: 15px; }
  h2, h3 { text-align: center; margin: 2px 0; }
  .subtitle { text-align: center; color: #555; margin-bottom: 10px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #1a3c5e; color: #fff; padding: 4px 6px; text-align: left; }
  td { border: 1px solid #ccc; padding: 3px 6px; }
  tr:nth-child(even) { background: #f5f5f5; }
  .footer { text-align: right; font-size: 8px; color: #888; margin-top: 8px; }
</style>
</head>
<body>
  <h2>SIMANTAP — Politeknik KP Sorong</h2>
  <h3>Laporan Monitoring Sesi Penerimaan Makan</h3>
  <p class="subtitle">{{ $tanggalDari }} s/d {{ $tanggalSampai }} | Dicetak: {{ $generatedAt }}</p>

  <table>
    <thead>
      <tr>
        <th>Tanggal</th><th>Sesi</th><th>Dipesan</th><th>Diterima</th>
        <th>Taruna</th><th>Redistribusi</th><th>Sisa</th><th>Rekonsiliasi</th><th>Status</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($sesis as $s)
      <tr>
        <td>{{ $s->tanggal?->format('d/m/Y') }}</td>
        <td>{{ ucfirst($s->sesi) }}</td>
        <td>{{ $s->porsi_dipesan }}</td>
        <td>{{ $s->porsi_diterima }}</td>
        <td>{{ $s->porsi_dimakan_taruna }}</td>
        <td>{{ $s->porsi_redistribusi }}</td>
        <td>{{ $s->porsi_sisa }}</td>
        <td>{{ $s->rekonsiliasiValid() ? 'Valid' : 'Tidak Valid' }}</td>
        <td>{{ $s->status_label }}</td>
      </tr>
      @empty
      <tr><td colspan="9" style="text-align:center">Tidak ada data</td></tr>
      @endforelse
    </tbody>
  </table>

  <p class="footer">Halaman {PAGE_NUM} dari {PAGE_COUNT}</p>
</body>
</html>
