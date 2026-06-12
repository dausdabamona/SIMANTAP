<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Taruna;
use App\Models\RekapBulanan;
use App\Models\SesiPenerimaanMakan;
use App\Models\KontrakMakan;
use App\Models\PemesananHarian;
use App\Models\PenyediaMakan;
use App\Models\PengajuanPembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class LaporanControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $role = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->assignRole($role);
    }

    public function test_laporan_taruna_returns_200(): void
    {
        Taruna::create(['nit' => '20230001', 'nama' => 'Ahmad', 'angkatan' => 2023, 'prodi' => 'TPI', 'kelas' => 'X-A', 'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true]);
        Taruna::create(['nit' => '20230002', 'nama' => 'Budi',  'angkatan' => 2023, 'prodi' => 'TPI', 'kelas' => 'X-A', 'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => false]);
        Taruna::create(['nit' => '20230003', 'nama' => 'Citra', 'angkatan' => 2022, 'prodi' => 'NKP', 'kelas' => 'XI-B', 'jenis_kelamin' => 'P', 'status_taruna' => 'cuti', 'penerima_bantuan' => true]);

        $response = $this->actingAs($this->user)->get('/laporan/taruna');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $response->assertViewHas('taruna');
    }

    public function test_laporan_rekap_returns_200(): void
    {
        $penyedia = PenyediaMakan::create(['nama' => 'Penyedia A', 'npwp' => '000', 'alamat' => 'X']);

        $kontrak = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/001',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 100000000,
            'harga_porsi'     => 15000,
            'penyedia_id'     => $penyedia->id,
            'status'          => 'aktif',
        ]);

        $taruna = Taruna::create(['nit' => '20230001', 'nama' => 'Ahmad', 'angkatan' => 2023, 'prodi' => 'TPI', 'kelas' => 'X-A', 'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true]);

        RekapBulanan::create([
            'taruna_id'     => $taruna->id,
            'periode_bulan' => now()->month,
            'periode_tahun' => now()->year,
            'total_sesi'    => 90,
            'total_porsi'   => 90,
            'hari_hadir'    => 30,
            'nilai_bantuan' => 1350000,
            'kontrak_id'    => $kontrak->id,
            'status'        => 'draft',
        ]);

        $response = $this->actingAs($this->user)->get('/laporan/rekap');

        $response->assertStatus(200);
    }

    public function test_laporan_monitoring_returns_200_with_stats(): void
    {
        $penyedia = PenyediaMakan::create(['nama' => 'Penyedia A', 'npwp' => '000', 'alamat' => 'X']);

        $kontrak = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/001',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 100000000,
            'harga_porsi'     => 15000,
            'penyedia_id'     => $penyedia->id,
            'status'          => 'aktif',
        ]);

        $pemesanan = PemesananHarian::create([
            'tanggal'              => today(),
            'kontrak_id'           => $kontrak->id,
            'jumlah_taruna_hadir'  => 100,
            'jumlah_porsi'         => 300,
            'nilai_total'          => 4500000,
            'harga_porsi_snapshot' => 15000,
            'status'               => 'draft',
        ]);

        SesiPenerimaanMakan::create([
            'pemesanan_harian_id'  => $pemesanan->id,
            'tanggal'              => today(),
            'sesi'                 => 'sarapan',
            'porsi_dipesan'        => 100,
            'porsi_diterima'       => 98,
            'porsi_dimakan_taruna' => 90,
            'porsi_redistribusi'   => 5,
            'porsi_sisa'           => 3,
            'kondisi_makanan'      => 'baik',
            'status'               => 'diterima',
        ]);

        $response = $this->actingAs($this->user)->get('/laporan/monitoring');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_laporan_money_bulanan_returns_12_rows(): void
    {
        $response = $this->actingAs($this->user)->get('/laporan/money-bulanan');

        $response->assertStatus(200);
        $response->assertViewHas('rows');
        $this->assertEquals(12, count($response->viewData('rows')));
    }
}
