<?php

namespace Tests\Feature;

use App\Http\Controllers\LaporanBamaController;
use App\Models\KontrakMakan;
use App\Models\LaporanBama;
use App\Models\PenyediaMakan;
use App\Models\PembayaranLuarKampus;
use App\Models\RekapBulanan;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanBamaTest extends TestCase
{
    use RefreshDatabase;

    private User $ppk;
    private User $wadir;
    private User $kpa;
    private Taruna $taruna;
    private KontrakMakan $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['laporan_bama.view','laporan_bama.buat','laporan_bama.setujui','laporan_bama.finalisasi'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rolePpk   = Role::firstOrCreate(['name' => 'ppk',       'guard_name' => 'web']);
        $roleWadir = Role::firstOrCreate(['name' => 'wadir_iii', 'guard_name' => 'web']);
        $roleKpa   = Role::firstOrCreate(['name' => 'kpa',       'guard_name' => 'web']);

        $rolePpk->givePermissionTo(['laporan_bama.view','laporan_bama.buat','laporan_bama.finalisasi']);
        $roleWadir->givePermissionTo(['laporan_bama.view','laporan_bama.setujui']);
        $roleKpa->givePermissionTo(['laporan_bama.view','laporan_bama.setujui']);

        $this->ppk   = User::factory()->create();  $this->ppk->assignRole($rolePpk);
        $this->wadir = User::factory()->create();  $this->wadir->assignRole($roleWadir);
        $this->kpa   = User::factory()->create();  $this->kpa->assignRole($roleKpa);

        $this->taruna = Taruna::create([
            'nama' => 'Taruna BAMA', 'nit' => '220000000001',
            'angkatan' => 2024, 'prodi' => 'Nautika', 'kelas' => 'A',
            'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true,
        ]);

        $penyedia = PenyediaMakan::create([
            'nama' => 'Penyedia BAMA', 'npwp' => '111', 'alamat' => 'Sorong',
            'bank' => 'BRI', 'nomor_rekening' => '1111111', 'nama_pemilik_rekening' => 'Penyedia',
        ]);

        $this->kontrak = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/BAMA',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 100_000_000,
            'harga_porsi'     => 20000,
            'penyedia_id'     => $penyedia->id,
            'status'          => 'aktif',
        ]);
    }

    public function test_bab_v_realisasi_gabungan_dalam_dan_luar_kampus(): void
    {
        // Data dalam kampus
        RekapBulanan::create([
            'taruna_id'     => $this->taruna->id,
            'periode_bulan' => 6,
            'periode_tahun' => 2025,
            'total_porsi'   => 20,
            'nilai_bantuan' => 20 * 20000,
            'kontrak_id'    => $this->kontrak->id,
            'status'        => RekapBulanan::STATUS_FINAL,
        ]);

        $controller = new LaporanBamaController();
        $data = $controller->queryBabData(6, 2025);

        $this->assertEquals(400_000, $data['totalDalamKampus'],
            'Total dalam kampus harus 20 porsi × Rp20.000');
        $this->assertEquals(0, $data['totalLuarKampus'],
            'Tidak ada pembayaran luar kampus, total harus 0');
        $this->assertCount(1, $data['rekapList'],
            'Harus ada 1 baris rekap untuk taruna ini');
    }

    public function test_state_machine_draft_ke_dikirim_pusdik(): void
    {
        $laporan = LaporanBama::create([
            'periode_bulan'  => 7,
            'periode_tahun'  => 2025,
            'status'         => LaporanBama::STATUS_DRAFT,
            'dibuat_by'      => $this->ppk->id,
            'ringkasan_eksekutif' => 'Test ringkasan',
        ]);

        // Draft → disetujui_wadir
        $this->actingAs($this->wadir)
            ->post(route('laporan-bama.setujui-wadir', $laporan))
            ->assertRedirect();
        $this->assertEquals(LaporanBama::STATUS_DISETUJUI_WADIR, $laporan->fresh()->status);
        $this->assertEquals($this->wadir->id, $laporan->fresh()->disetujui_wadir_by);

        // disetujui_wadir → disetujui_kpa
        $this->actingAs($this->kpa)
            ->post(route('laporan-bama.setujui-kpa', $laporan))
            ->assertRedirect();
        $this->assertEquals(LaporanBama::STATUS_DISETUJUI_KPA, $laporan->fresh()->status);
        $this->assertEquals($this->kpa->id, $laporan->fresh()->disetujui_kpa_by);

        // disetujui_kpa → dikirim_pusdik
        $this->actingAs($this->ppk)
            ->post(route('laporan-bama.kirim-pusdik', $laporan))
            ->assertRedirect();
        $this->assertEquals(LaporanBama::STATUS_DIKIRIM_PUSDIK, $laporan->fresh()->status);
        $this->assertNotNull($laporan->fresh()->dikirim_pusdik_at);
    }

    public function test_generate_pdf_tersimpan_di_storage(): void
    {
        Storage::fake('public');

        $laporan = LaporanBama::create([
            'periode_bulan'  => 8,
            'periode_tahun'  => 2025,
            'status'         => LaporanBama::STATUS_DISETUJUI_KPA,
            'dibuat_by'      => $this->ppk->id,
            'ringkasan_eksekutif' => 'Test PDF',
        ]);

        $this->actingAs($this->ppk)
            ->post(route('laporan-bama.generate-pdf', $laporan))
            ->assertOk();

        $laporan->refresh();
        $this->assertNotNull($laporan->file_pdf, 'field file_pdf harus terisi setelah generate');

        Storage::disk('public')->assertExists($laporan->file_pdf);
    }
}
