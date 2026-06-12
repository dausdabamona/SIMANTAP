<?php

namespace Tests\Feature;

use App\Models\KontrakMakan;
use App\Models\PengajuanPembayaran;
use App\Models\PenyediaMakan;
use App\Models\RekapBulanan;
use App\Models\SenatAccount;
use App\Models\Taruna;
use App\Models\RekeningTaruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiBankSpmTest extends TestCase
{
    use RefreshDatabase;

    private User $ppk;
    private SenatAccount $senatBsi;
    private SenatAccount $senatBni;
    private int $kontrakId;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['pembayaran.create', 'pembayaran.proses_ppk'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $role = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $role->syncPermissions(['pembayaran.create', 'pembayaran.proses_ppk']);
        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($role);

        $this->senatBsi = SenatAccount::create([
            'nama_akun'     => 'Rekening BSI',
            'bank'          => 'BSI',
            'bank_group'    => 'BSI',
            'untuk_tingkat' => '1',
            'nomor_rekening'=> '7200000001',
            'nama_pemilik'  => 'Senat Taruna',
            'is_aktif'      => true,
        ]);

        $this->senatBni = SenatAccount::create([
            'nama_akun'     => 'Rekening BNI',
            'bank'          => 'BNI',
            'bank_group'    => 'BNI',
            'untuk_tingkat' => '2,3',
            'nomor_rekening'=> '1000000002',
            'nama_pemilik'  => 'Senat Taruna',
            'is_aktif'      => true,
        ]);

        $penyedia = PenyediaMakan::create(['nama' => 'Test Penyedia', 'npwp' => '000', 'alamat' => 'X']);
        $kontrak  = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/TEST',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 100_000_000,
            'harga_porsi'     => 15000,
            'penyedia_id'     => $penyedia->id,
            'status'          => 'aktif',
        ]);
        $this->kontrakId = $kontrak->id;
    }

    private function makeTarunaWithRekap(string $kelas, string $bankGroup, int $count = 3): void
    {
        $bank = $bankGroup === 'BSI' ? 'BSI (Bank Syariah Indonesia)' : 'BNI (Bank Negara Indonesia)';
        for ($i = 1; $i <= $count; $i++) {
            $taruna = Taruna::create([
                'nit'              => 'NIT-' . $kelas . '-' . $i,
                'nama'             => 'Taruna ' . $kelas . ' ' . $i,
                'angkatan'         => 2025,
                'kelas'            => $kelas,
                'prodi'            => 'Teknologi Penangkapan Ikan',
                'jenis_kelamin'    => 'L',
                'status_taruna'    => 'aktif',
                'penerima_bantuan' => true,
            ]);
            RekapBulanan::create([
                'taruna_id'    => $taruna->id,
                'periode_bulan'=> 5,
                'periode_tahun'=> 2025,
                'total_porsi'  => 26,
                'nilai_bantuan'=> 390_000,
                'kontrak_id'   => $this->kontrakId,
                'status'       => RekapBulanan::STATUS_FINAL,
            ]);
        }
    }

    public function test_bank_group_bsi_untuk_tingkat_satu(): void
    {
        $this->makeTarunaWithRekap('X-A', 'BSI');

        $this->actingAs($this->ppk)
            ->post(route('pembayaran.store'), [
                'periode_bulan' => 5,
                'periode_tahun' => 2025,
                'kelas'         => ['X-A'],
            ])
            ->assertRedirect();

        $spm = PengajuanPembayaran::where('kelas', 'X-A')->first();
        $this->assertNotNull($spm);
        $this->assertEquals('BSI', $spm->bank_group);
        $this->assertEquals(1, $spm->tingkat);
        $this->assertEquals($this->senatBsi->id, $spm->rekening_senat_id);
    }

    public function test_bank_group_bni_untuk_tingkat_dua(): void
    {
        $this->makeTarunaWithRekap('XI-B', 'BNI');

        $this->actingAs($this->ppk)
            ->post(route('pembayaran.store'), [
                'periode_bulan' => 5,
                'periode_tahun' => 2025,
                'kelas'         => ['XI-B'],
            ])
            ->assertRedirect();

        $spm = PengajuanPembayaran::where('kelas', 'XI-B')->first();
        $this->assertNotNull($spm);
        $this->assertEquals('BNI', $spm->bank_group);
        $this->assertEquals(2, $spm->tingkat);
        $this->assertEquals($this->senatBni->id, $spm->rekening_senat_id);
    }

    public function test_satu_spm_per_kelas_per_periode(): void
    {
        $this->makeTarunaWithRekap('X-A', 'BSI');
        $this->makeTarunaWithRekap('XI-A', 'BNI');

        $this->actingAs($this->ppk)
            ->post(route('pembayaran.store'), [
                'periode_bulan' => 5,
                'periode_tahun' => 2025,
                'kelas'         => ['X-A', 'XI-A'],
            ])
            ->assertRedirect();

        $this->assertEquals(1, PengajuanPembayaran::where('kelas', 'X-A')->count());
        $this->assertEquals(1, PengajuanPembayaran::where('kelas', 'XI-A')->count());
    }

    public function test_duplikat_spm_kelas_periode_diabaikan(): void
    {
        $this->makeTarunaWithRekap('X-A', 'BSI');

        // First create
        $this->actingAs($this->ppk)
            ->post(route('pembayaran.store'), [
                'periode_bulan' => 5,
                'periode_tahun' => 2025,
                'kelas'         => ['X-A'],
            ]);

        // Second attempt for same kelas + periode
        $this->actingAs($this->ppk)
            ->post(route('pembayaran.store'), [
                'periode_bulan' => 5,
                'periode_tahun' => 2025,
                'kelas'         => ['X-A'],
            ])
            ->assertSessionHas('error');

        $this->assertEquals(1, PengajuanPembayaran::where('kelas', 'X-A')->count());
    }

    public function test_rekening_taruna_auto_detect_bank_group(): void
    {
        $taruna = Taruna::create([
            'nit'              => 'NIT-001',
            'nama'             => 'Taruna BSI',
            'angkatan'         => 2025,
            'kelas'            => 'X-A',
            'prodi'            => 'Teknologi Penangkapan Ikan',
            'jenis_kelamin'    => 'L',
            'status_taruna'    => 'aktif',
            'penerima_bantuan' => true,
        ]);

        $rekening = RekeningTaruna::create([
            'taruna_id'    => $taruna->id,
            'bank'         => 'BSI (Bank Syariah Indonesia)',
            'nomor_rekening'=> '7200099999',
            'nama_pemilik' => 'Taruna BSI',
        ]);

        $this->assertEquals('BSI', $rekening->fresh()->bank_group);
    }

    public function test_kelas_tersedia_endpoint(): void
    {
        $this->makeTarunaWithRekap('X-A', 'BSI');

        $response = $this->actingAs($this->ppk)
            ->getJson(route('pembayaran.kelas-tersedia', [
                'periode_bulan' => 5,
                'periode_tahun' => 2025,
            ]));

        $response->assertOk();
        $data = $response->json();
        $this->assertNotEmpty($data);
        $kelas = collect($data)->firstWhere('kelas', 'X-A');
        $this->assertNotNull($kelas);
        $this->assertEquals('BSI', $kelas['bank_group']);
        $this->assertEquals(3, $kelas['jumlah_taruna']);
    }
}
