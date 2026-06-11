<?php

namespace Tests\Feature;

use App\Models\KontrakMakan;
use App\Models\PenerimaanMakan;
use App\Models\PenyediaMakan;
use App\Models\RekapBulanan;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RekapBulananTest extends TestCase
{
    use RefreshDatabase;

    private User $ppk;
    private Taruna $taruna;
    private KontrakMakan $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'rekap.hitung', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $role->givePermissionTo('rekap.hitung');

        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($role);

        $this->taruna = Taruna::create([
            'nama' => 'Taruna Rekap', 'nit' => '200000000001',
            'angkatan' => 2022, 'prodi' => 'Teknika', 'kelas' => 'A',
            'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true,
        ]);

        $penyedia = PenyediaMakan::create([
            'nama' => 'Test', 'npwp' => '000', 'alamat' => 'X',
            'bank' => 'BRI', 'nomor_rekening' => '0000000', 'nama_pemilik_rekening' => 'Test',
        ]);

        $this->kontrak = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/002',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 50_000_000,
            'harga_porsi'     => 15000,
            'penyedia_id'     => $penyedia->id,
            'status'          => 'aktif',
        ]);
    }

    private function makePenerimaan(int $porsi, string $tanggal, string $status = 'dapat'): void
    {
        PenerimaanMakan::create([
            'taruna_id'            => $this->taruna->id,
            'tanggal'              => $tanggal,
            'jenis_makan'          => 'makan_siang',
            'jumlah_porsi_diterima'=> $porsi,
            'status_eligibilitas'  => $status,
        ]);
    }

    public function test_hitung_periode_creates_rekap_with_correct_totals(): void
    {
        $this->makePenerimaan(3, '2025-05-01');
        $this->makePenerimaan(3, '2025-05-02');
        $this->makePenerimaan(3, '2025-05-03');

        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), [
                'bulan'      => 5,
                'tahun'      => 2025,
                'kontrak_id' => $this->kontrak->id,
            ])
            ->assertRedirect();

        $rekap = RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 5)->where('periode_tahun', 2025)->first();

        $this->assertNotNull($rekap);
        $this->assertEquals(9, $rekap->total_porsi);
        $this->assertEquals(9 * 15000, $rekap->nilai_bantuan);
    }

    public function test_hitung_excludes_tidak_dapat_penerimaan(): void
    {
        $this->makePenerimaan(3, '2025-06-01', 'dapat');
        $this->makePenerimaan(3, '2025-06-02', 'tidak_dapat');

        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), [
                'bulan'      => 6,
                'tahun'      => 2025,
                'kontrak_id' => $this->kontrak->id,
            ]);

        $rekap = RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 6)->where('periode_tahun', 2025)->first();

        $this->assertNotNull($rekap);
        $this->assertEquals(3, $rekap->total_porsi);
    }

    public function test_hitung_is_idempotent_using_update_or_create(): void
    {
        $this->makePenerimaan(3, '2025-07-01');

        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 7, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        $this->makePenerimaan(3, '2025-07-02');

        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 7, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        $this->assertEquals(1, RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 7)->where('periode_tahun', 2025)->count());

        $rekap = RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 7)->where('periode_tahun', 2025)->first();
        $this->assertEquals(6, $rekap->total_porsi);
    }
}
