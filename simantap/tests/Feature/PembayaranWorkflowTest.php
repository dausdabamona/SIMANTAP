<?php

namespace Tests\Feature;

use App\Models\PengajuanPembayaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PembayaranWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $roleName, array $permissions): User
    {
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    private function makePembayaran(string $status = 'draft'): PengajuanPembayaran
    {
        return PengajuanPembayaran::create([
            'nomor_pengajuan' => 'PBY/' . now()->year . '/' . rand(1000, 9999),
            'periode_bulan'   => 5,
            'periode_tahun'   => 2025,
            'total_taruna'    => 50,
            'total_porsi'     => 4500,
            'total_nilai'     => 67_500_000,
            'status'          => $status,
        ]);
    }

    public function test_proses_ppk_transitions_from_draft(): void
    {
        $ppk = $this->makeUser('ppk', ['pembayaran.proses_ppk']);
        $pembayaran = $this->makePembayaran('draft');

        $this->actingAs($ppk)
            ->post(route('pembayaran.transisi', $pembayaran), ['aksi' => 'proses_ppk'])
            ->assertRedirect();

        $this->assertEquals(PengajuanPembayaran::STATUS_DIPROSES_PPK, $pembayaran->fresh()->status);
    }

    public function test_setujui_kpa_transitions_from_diproses_ppk(): void
    {
        $kpa = $this->makeUser('kpa', ['pembayaran.setujui_kpa']);
        $pembayaran = $this->makePembayaran(PengajuanPembayaran::STATUS_DIPROSES_PPK);

        $this->actingAs($kpa)
            ->post(route('pembayaran.transisi', $pembayaran), ['aksi' => 'setujui_kpa'])
            ->assertRedirect();

        $this->assertEquals(PengajuanPembayaran::STATUS_DISETUJUI_KPA, $pembayaran->fresh()->status);
    }

    public function test_input_sp2d_stores_nomor_and_tanggal(): void
    {
        $ppk = $this->makeUser('ppk2', ['pembayaran.input_sp2d']);
        $pembayaran = $this->makePembayaran(PengajuanPembayaran::STATUS_PERMOHONAN_KPPN);

        $this->actingAs($ppk)
            ->post(route('pembayaran.transisi', $pembayaran), [
                'aksi'         => 'input_sp2d',
                'nomor_sp2d'   => 'SP2D/2025/001',
                'tanggal_sp2d' => '2025-05-15',
            ])
            ->assertRedirect();

        $fresh = $pembayaran->fresh();
        $this->assertEquals(PengajuanPembayaran::STATUS_SP2D, $fresh->status);
        $this->assertEquals('SP2D/2025/001', $fresh->nomor_sp2d);
    }

    public function test_unauthorized_user_cannot_transition(): void
    {
        $viewer = User::factory()->create();
        $pembayaran = $this->makePembayaran('draft');

        $this->actingAs($viewer)
            ->post(route('pembayaran.transisi', $pembayaran), ['aksi' => 'proses_ppk'])
            ->assertForbidden();
    }

    public function test_workflow_log_is_appended_on_transition(): void
    {
        $ppk = $this->makeUser('ppk3', ['pembayaran.proses_ppk']);
        $pembayaran = $this->makePembayaran('draft');

        $this->actingAs($ppk)
            ->post(route('pembayaran.transisi', $pembayaran), ['aksi' => 'proses_ppk', 'catatan' => 'Diproses segera']);

        $this->assertEquals(1, $pembayaran->workflow()->count());
        $this->assertEquals('diproses_ppk', $pembayaran->workflow()->first()->status_ke);
    }
}
