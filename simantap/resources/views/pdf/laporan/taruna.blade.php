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
  .footer { text-align: right; font-size: 8px; color: #888; margin-top: 8px; }
</style>
</head>
<body>
  <h2>SIMANTAP — Politeknik KP Sorong</h2>
  <h3>Laporan Data Taruna Tahun {{ $tahun }}</h3>
  <p class="subtitle">Dicetak: {{ $generatedAt }}</p>

  <table>
    <thead>
      <tr>
        <th>No</th><th>NIT</th><th>Nama</th><th>Angkatan</th>
        <th>Prodi</th><th>Kelas</th><th>Status</th><th>Penerima</th><th>Eligible</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($taruna as $i => $t)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $t->nit }}</td>
        <td>{{ $t->nama }}</td>
        <td>{{ $t->angkatan }}</td>
        <td>{{ $t->prodi }}</td>
        <td>{{ $t->kelas }}</td>
        <td>{{ $t->status_taruna_label }}</td>
        <td>{{ $t->penerima_bantuan ? 'Ya' : 'Tidak' }}</td>
        <td>{{ $t->is_eligible_bantuan ? 'Ya' : 'Tidak' }}</td>
      </tr>
      @empty
      <tr><td colspan="9" style="text-align:center">Tidak ada data</td></tr>
      @endforelse
    </tbody>
  </table>

  <p class="footer">Halaman {PAGE_NUM} dari {PAGE_COUNT}</p>
</body>
</html>
