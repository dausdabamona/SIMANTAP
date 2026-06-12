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
  @php
    $namaBulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                  7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
  @endphp
  <h2>SIMANTAP — Politeknik KP Sorong</h2>
  <h3>Laporan Rekap Bulanan — {{ $namaBulan[$bulan] }} {{ $tahun }}</h3>
  <p class="subtitle">Dicetak: {{ $generatedAt }}</p>

  <table>
    <thead>
      <tr>
        <th>No</th><th>NIT</th><th>Nama</th><th>Kelas</th>
        <th>Total Sesi</th><th>Hari Hadir</th><th>Nilai Bantuan</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($rekaps as $i => $r)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $r->taruna?->nit ?? '-' }}</td>
        <td>{{ $r->taruna?->nama ?? '-' }}</td>
        <td>{{ $r->taruna?->kelas ?? '-' }}</td>
        <td>{{ $r->total_sesi }}</td>
        <td>{{ $r->hari_hadir }}</td>
        <td>Rp {{ number_format($r->nilai_bantuan, 0, ',', '.') }}</td>
      </tr>
      @empty
      <tr><td colspan="7" style="text-align:center">Tidak ada data</td></tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td colspan="6" style="text-align:right">Total Nilai Bantuan:</td>
        <td>Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
      </tr>
    </tfoot>
  </table>

  <p class="footer">Halaman {PAGE_NUM} dari {PAGE_COUNT}</p>
</body>
</html>
