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
    private User $wadir;
    private Taruna $taruna;
    private KontrakMakan $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['rekap.hitung', 'rekap.setujui'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $rolePpk   = Role::firstOrCreate(['name' => 'ppk',      'guard_name' => 'web']);
        $roleWadir = Role::firstOrCreate(['name' => 'wadir_iii', 'guard_name' => 'web']);
        $rolePpk->givePermissionTo('rekap.hitung');
        $roleWadir->givePermissionTo('rekap.setujui');

        $this->ppk   = User::factory()->create();
        $this->ppk->assignRole($rolePpk);

        $this->wadir = User::factory()->create();
        $this->wadir->assignRole($roleWadir);

        $this->taruna = Taruna::create([
            'nama' => 'Taruna Rekap', 'nit' => '200000000001',
            'angkatan' => 2022, 'prodi' => 'Teknika', 'kelas' => 'A',
            'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true,
        ]);

        $penyedia = PenyediaMakan::create([
            'nama' => 'Test', 'npwp' => '000', 'alamat' => 'X',
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
        // New state: fresh rekap starts at draft, waits for wadir_iii approval
        $this->assertEquals(RekapBulanan::STATUS_DRAFT, $rekap->status);
    }

    public function test_wadir_iii_can_approve_draft_rekap(): void
    {
        $this->makePenerimaan(3, '2025-08-01');

        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 8, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        $rekap = RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 8)->where('periode_tahun', 2025)->first();

        $this->assertEquals(RekapBulanan::STATUS_DRAFT, $rekap->status);

        $this->actingAs($this->wadir)
            ->post(route('rekap.setujui-wadir', $rekap))
            ->assertRedirect();

        $this->assertEquals(RekapBulanan::STATUS_DISETUJUI_WADIR, $rekap->fresh()->status);
    }

    public function test_ppk_hitung_after_wadir_advances_to_dihitung_ppk(): void
    {
        $this->makePenerimaan(3, '2025-09-01');

        // Hitung awal → draft
        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 9, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        $rekap = RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 9)->where('periode_tahun', 2025)->first();

        // Wadir setujui
        $rekap->update(['status' => RekapBulanan::STATUS_DISETUJUI_WADIR]);

        // PPK hitung ulang → dihitung_ppk
        $this->makePenerimaan(3, '2025-09-02');
        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 9, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        $this->assertEquals(RekapBulanan::STATUS_DIHITUNG_PPK, $rekap->fresh()->status);
        $this->assertEquals(6, $rekap->fresh()->total_porsi);
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

    public function test_hitung_is_idempotent_after_wadir_approval(): void
    {
        $this->makePenerimaan(3, '2025-07-01');

        // Hitung pertama → draft
        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 7, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        $rekap = RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 7)->where('periode_tahun', 2025)->first();

        // Wadir setujui
        $rekap->update(['status' => RekapBulanan::STATUS_DISETUJUI_WADIR]);

        // Tambah data baru, hitung ulang
        $this->makePenerimaan(3, '2025-07-02');
        $this->actingAs($this->ppk)
            ->post(route('rekap.hitung'), ['bulan' => 7, 'tahun' => 2025, 'kontrak_id' => $this->kontrak->id]);

        // Hanya satu baris per taruna per periode
        $this->assertEquals(1, RekapBulanan::where('taruna_id', $this->taruna->id)
            ->where('periode_bulan', 7)->where('periode_tahun', 2025)->count());

        $this->assertEquals(6, $rekap->fresh()->total_porsi);
    }
}
