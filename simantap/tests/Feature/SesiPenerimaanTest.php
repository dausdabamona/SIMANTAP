<?php

namespace Tests\Feature;

use App\Models\KehadiranMakan;
use App\Models\KontrakMakan;
use App\Models\PemesananHarian;
use App\Models\RekapBulanan;
use App\Models\SesiPenerimaanMakan;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SesiPenerimaanTest extends TestCase
{
    use RefreshDatabase;

    private User $pembina;
    private User $ppk;
    private PemesananHarian $pemesanan;
    private KontrakMakan $kontrak;
    private SesiPenerimaanMakan $sesi;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        foreach (['pembayaran.view'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rolePembina = Role::firstOrCreate(['name' => 'pembina_karakter', 'guard_name' => 'web']);
        $rolePpk     = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);

        $this->pembina = User::factory()->create();
        $this->pembina->assignRole($rolePembina);

        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($rolePpk);

        // Buat kontrak aktif
        $penyedia = \App\Models\PenyediaMakan::create(['nama' => 'Penyedia', 'npwp' => '000', 'alamat' => 'X']);
        $this->kontrak = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/001',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 100_000_000,
            'harga_porsi'     => 15_000,
            'penyedia_id'     => $penyedia->id,
            'status'          => 'aktif',
        ]);

        // Buat pemesanan harian
        $this->pemesanan = PemesananHarian::create([
            'tanggal'              => today(),
            'kontrak_id'           => $this->kontrak->id,
            'jumlah_taruna_hadir'  => 254,
            'jumlah_porsi'         => 762, // 254 × 3
            'harga_porsi_snapshot' => 15_000,
            'nilai_total'          => 762 * 15_000,
            'status'               => 'disajikan',
        ]);

        // Buat satu sesi
        $this->sesi = SesiPenerimaanMakan::create([
            'pemesanan_harian_id' => $this->pemesanan->id,
            'tanggal'             => today(),
            'sesi'                => SesiPenerimaanMakan::SESI_SARAPAN,
            'porsi_dipesan'       => 254,
            'status'              => SesiPenerimaanMakan::STATUS_MENUNGGU,
        ]);
    }

    // Test 1: Serah terima dicatat → status diterima, foto tersimpan, GPS tercatat
    public function test_serah_terima_dicatat_dengan_foto_dan_gps(): void
    {
        $foto1 = UploadedFile::fake()->image('makanan1.jpg');
        $foto2 = UploadedFile::fake()->image('makanan2.jpg');

        $this->actingAs($this->pembina)
            ->post(route('sesi-penerimaan.terima', $this->sesi), [
                'porsi_diterima'  => 254,
                'kondisi_makanan' => 'baik',
                'catatan_kondisi' => 'Menu sesuai jadwal.',
                'foto'            => [$foto1, $foto2],
                'lat'             => -0.8917,
                'lng'             => 131.2558,
            ])
            ->assertRedirect();

        $fresh = $this->sesi->fresh();
        $this->assertEquals(SesiPenerimaanMakan::STATUS_DITERIMA, $fresh->status);
        $this->assertEquals(254, $fresh->porsi_diterima);
        $this->assertNotNull($fresh->foto);
        $this->assertCount(2, $fresh->foto);
        $this->assertEquals(-0.8917, (float) $fresh->lat);
        $this->assertNotNull($fresh->diterima_at);
        $this->assertEquals($this->pembina->id, $fresh->diterima_by);
    }

    // Test 2: Centang 240 dari 254 taruna → porsi_dimakan_taruna=240, porsi_sisa=14
    public function test_centang_240_taruna_update_porsi_dimakan_dan_sisa(): void
    {
        // Set sesi sudah diterima
        $this->sesi->update([
            'porsi_diterima' => 254,
            'porsi_sisa'     => 254,
            'status'         => SesiPenerimaanMakan::STATUS_DITERIMA,
        ]);

        // Buat 254 taruna aktif
        $taruna = collect();
        for ($i = 1; $i <= 254; $i++) {
            $taruna->push(Taruna::create([
                'nit'              => str_pad($i, 8, '0', STR_PAD_LEFT),
                'nama'             => "Taruna {$i}",
                'angkatan'         => 2023,
                'prodi'            => 'TPI',
                'kelas'            => 'X-A',
                'jenis_kelamin'    => 'L',
                'status_taruna'    => 'aktif',
                'penerima_bantuan' => true,
            ]));
        }

        // Centang 240 saja
        $hadirIds = $taruna->take(240)->pluck('id')->toArray();

        $this->actingAs($this->pembina)
            ->post(route('kehadiran-makan.centang', $this->sesi), [
                'hadir' => $hadirIds,
            ])
            ->assertRedirect();

        $fresh = $this->sesi->fresh();
        $this->assertEquals(240, $fresh->porsi_dimakan_taruna);
        $this->assertEquals(14, $fresh->porsi_sisa); // 254 - 240 - 0 redistribusi
    }

    // Test 3: Simpan redistribusi valid → porsi_sisa=3, rekonsiliasi valid
    public function test_simpan_redistribusi_dan_rekonsiliasi_valid(): void
    {
        $this->sesi->update([
            'porsi_diterima'       => 254,
            'porsi_dimakan_taruna' => 240,
            'porsi_redistribusi'   => 0,
            'porsi_sisa'           => 14,
            'status'               => SesiPenerimaanMakan::STATUS_DITERIMA,
        ]);

        // 6 petugas + 5 taruna_lain = 11, sisa = 3
        $this->actingAs($this->pembina)
            ->post(route('sesi-penerimaan.simpan-redistribusi', $this->sesi), [
                'redistribusi' => [
                    'petugas_ketarunaan' => 6,
                    'taruna_lain'        => 5,
                ],
                'porsi_sisa' => 3,
            ])
            ->assertRedirect();

        $fresh = $this->sesi->fresh();
        $this->assertEquals(11, $fresh->porsi_redistribusi);
        $this->assertEquals(3, $fresh->porsi_sisa);
        $this->assertTrue($fresh->rekonsiliasiValid());
    }

    // Test 4: Rekonsiliasi invalid → error dengan selisih yang jelas
    public function test_rekonsiliasi_invalid_mengembalikan_error(): void
    {
        $this->sesi->update([
            'porsi_diterima'       => 254,
            'porsi_dimakan_taruna' => 240,
            'porsi_redistribusi'   => 0,
            'porsi_sisa'           => 14,
            'status'               => SesiPenerimaanMakan::STATUS_DITERIMA,
        ]);

        // Total tidak seimbang: 240 + 6 + 10 = 256 ≠ 254
        $this->actingAs($this->pembina)
            ->post(route('sesi-penerimaan.simpan-redistribusi', $this->sesi), [
                'redistribusi' => [
                    'petugas_ketarunaan' => 6,
                ],
                'porsi_sisa' => 10, // 240 + 6 + 10 = 256 ≠ 254
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // Test 5: Import fingerprint → kehadiran terbaca, sumber='fingerprint'
    public function test_import_fingerprint_mengisi_kehadiran(): void
    {
        $this->sesi->update([
            'porsi_diterima' => 254,
            'porsi_sisa'     => 254,
            'status'         => SesiPenerimaanMakan::STATUS_DITERIMA,
        ]);

        // Buat 3 taruna
        $taruna1 = Taruna::create(['nit' => '20230001', 'nama' => 'A', 'angkatan' => 2023, 'prodi' => 'TPI', 'kelas' => 'X-A', 'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true]);
        $taruna2 = Taruna::create(['nit' => '20230002', 'nama' => 'B', 'angkatan' => 2023, 'prodi' => 'TPI', 'kelas' => 'X-A', 'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true]);
        $taruna3 = Taruna::create(['nit' => '20230003', 'nama' => 'C', 'angkatan' => 2023, 'prodi' => 'TPI', 'kelas' => 'X-A', 'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true]);

        // Buat file CSV fingerprint: NIT + waktu scan
        $csvContent = "20230001,2025-06-13 06:45:00\n20230002,2025-06-13 06:47:00\n99999999,2025-06-13 06:48:00\n"; // NIT 99999999 tidak terdaftar
        $file = UploadedFile::fake()->createWithContent('fingerprint.csv', $csvContent);

        $this->actingAs($this->pembina)
            ->post(route('kehadiran-makan.fingerprint', $this->sesi), [
                'file' => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // 2 terpetakan, 1 tidak ditemukan
        $this->assertEquals(2, KehadiranMakan::where('sesi_id', $this->sesi->id)->where('hadir', true)->count());
        $this->assertEquals('fingerprint', KehadiranMakan::where('taruna_id', $taruna1->id)->value('sumber'));
    }

    // Test 6: Rekap bulanan dihitung dari sesi hadir × harga_porsi (bukan hari×3)
    public function test_rekap_bulanan_dihitung_dari_sesi_kehadiran(): void
    {
        // Buat pemesanan kedua (tanggal berbeda) agar tidak konflik dengan setUp
        $pemesanan2 = PemesananHarian::create([
            'tanggal'              => now()->startOfMonth(),
            'kontrak_id'           => $this->kontrak->id,
            'jumlah_taruna_hadir'  => 100,
            'jumlah_porsi'         => 300,
            'harga_porsi_snapshot' => 15_000,
            'nilai_total'          => 300 * 15_000,
            'status'               => 'disajikan',
        ]);

        // Buat 2 sesi diterima untuk bulan ini
        $sesi1 = SesiPenerimaanMakan::create([
            'pemesanan_harian_id' => $pemesanan2->id,
            'tanggal'             => now()->startOfMonth(),
            'sesi'                => SesiPenerimaanMakan::SESI_SARAPAN,
            'porsi_dipesan'       => 100,
            'porsi_diterima'      => 100,
            'porsi_dimakan_taruna'=> 90,
            'porsi_redistribusi'  => 5,
            'porsi_sisa'          => 5,
            'status'              => SesiPenerimaanMakan::STATUS_DITERIMA,
        ]);

        $sesi2 = SesiPenerimaanMakan::create([
            'pemesanan_harian_id' => $pemesanan2->id,
            'tanggal'             => now()->startOfMonth(),
            'sesi'                => SesiPenerimaanMakan::SESI_SIANG,
            'porsi_dipesan'       => 100,
            'porsi_diterima'      => 100,
            'porsi_dimakan_taruna'=> 85,
            'porsi_redistribusi'  => 10,
            'porsi_sisa'          => 5,
            'status'              => SesiPenerimaanMakan::STATUS_DITERIMA,
        ]);

        // Buat satu taruna
        $taruna = Taruna::create([
            'nit'              => '20230001',
            'nama'             => 'Ahmad',
            'angkatan'         => 2023,
            'prodi'            => 'TPI',
            'kelas'            => 'X-A',
            'jenis_kelamin'    => 'L',
            'status_taruna'    => 'aktif',
            'penerima_bantuan' => true,
        ]);

        // Catat hadir di 2 sesi (sarapan + siang = 2 sesi, tidak hadir malam)
        KehadiranMakan::create(['sesi_id' => $sesi1->id, 'taruna_id' => $taruna->id, 'hadir' => true, 'sumber' => 'manual']);
        KehadiranMakan::create(['sesi_id' => $sesi2->id, 'taruna_id' => $taruna->id, 'hadir' => true, 'sumber' => 'manual']);

        // Jalankan hitung rekap via controller
        $bulan = now()->month;
        $tahun = now()->year;

        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), [
                'bulan'      => $bulan,
                'tahun'      => $tahun,
                'kontrak_id' => $this->kontrak->id,
            ])
            ->assertRedirect();

        $rekap = RekapBulanan::where('taruna_id', $taruna->id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->first();

        $this->assertNotNull($rekap);
        // 2 sesi hadir × 15.000 = 30.000
        $this->assertEquals(2, $rekap->total_sesi);
        $this->assertEquals(30_000, (float) $rekap->nilai_bantuan);
    }
}
