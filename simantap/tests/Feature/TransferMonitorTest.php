<?php

namespace Tests\Feature;

use App\Models\PengajuanPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransferMonitorTest extends TestCase
{
    use RefreshDatabase;

    private User $wadir;
    private User $ppk;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['rekap.view', 'pembayaran.view', 'transfer.senat.mengetahui', 'transfer.penyedia.setujui'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roleWadir = Role::firstOrCreate(['name' => 'wadir_iii', 'guard_name' => 'web']);
        $roleWadir->givePermissionTo(['rekap.view', 'pembayaran.view', 'transfer.senat.mengetahui', 'transfer.penyedia.setujui']);

        $rolePpk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $rolePpk->givePermissionTo(['rekap.view', 'pembayaran.view']);

        $this->wadir = User::factory()->create();
        $this->wadir->assignRole($roleWadir);

        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($rolePpk);
    }

    private function makePembayaran(string $status): PengajuanPembayaran
    {
        static $seq = 0;
        return PengajuanPembayaran::create([
            'nomor_pengajuan' => 'PAY/2025/06/' . str_pad(++$seq, 3, '0', STR_PAD_LEFT),
            'periode_bulan'   => 6,
            'periode_tahun'   => 2025,
            'total_nilai'     => 5_000_000,
            'status'          => $status,
        ]);
    }

    public function test_wadir_dapat_akses_halaman_transfer_monitor(): void
    {
        $this->actingAs($this->wadir)
            ->get(route('transfer-monitor.index'))
            ->assertOk();
    }

    public function test_mengetahui_kppn_oleh_wadir_berhasil(): void
    {
        $p = $this->makePembayaran('sp2d');

        $this->actingAs($this->wadir)
            ->post(route('transfer-monitor.mengetahui', $p))
            ->assertRedirect();
    }

    public function test_setujui_transfer_penyedia_mengubah_status_ke_selesai(): void
    {
        $p = $this->makePembayaran('transfer_penyedia');

        $this->actingAs($this->wadir)
            ->post(route('transfer-monitor.setujui-penyedia', $p))
            ->assertRedirect();

        $this->assertEquals('selesai', $p->fresh()->status);
    }

    public function test_ppk_tidak_bisa_setujui_transfer_penyedia(): void
    {
        $p = $this->makePembayaran('transfer_penyedia');

        $this->actingAs($this->ppk)
            ->post(route('transfer-monitor.setujui-penyedia', $p))
            ->assertForbidden();
    }

    public function test_setujui_transfer_gagal_jika_status_bukan_transfer_penyedia(): void
    {
        $p = $this->makePembayaran('sp2d');

        $this->actingAs($this->wadir)
            ->post(route('transfer-monitor.setujui-penyedia', $p))
            ->assertForbidden();
    }
}
