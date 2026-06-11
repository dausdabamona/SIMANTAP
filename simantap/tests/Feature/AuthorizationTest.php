<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $ppk;
    private User $outsider;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'laporan_bama.view', 'laporan_bama.buat',
            'rekap.view', 'pembayaran.view',
            'taruna.view', 'penyedia.view', 'kontrak.view',
        ] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rolePpk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $rolePpk->givePermissionTo(['laporan_bama.view', 'laporan_bama.buat', 'rekap.view', 'pembayaran.view', 'taruna.view']);

        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($rolePpk);

        // User tanpa role/permission apapun
        $this->outsider = User::factory()->create();
    }

    public function test_outsider_tidak_bisa_akses_laporan_bama(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('laporan-bama.index'))
            ->assertForbidden();
    }

    public function test_outsider_tidak_bisa_akses_transfer_monitor(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('transfer-monitor.index'))
            ->assertForbidden();
    }

    public function test_outsider_tidak_bisa_akses_portal_penyedia(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('penyedia.pesanan'))
            ->assertForbidden();
    }

    public function test_ppk_dapat_akses_laporan_bama(): void
    {
        $this->actingAs($this->ppk)
            ->get(route('laporan-bama.index'))
            ->assertOk();
    }

    public function test_ppk_dapat_akses_laporan_bama_create(): void
    {
        $this->actingAs($this->ppk)
            ->get(route('laporan-bama.create'))
            ->assertOk();
    }

    public function test_guest_diarahkan_ke_login(): void
    {
        $this->get(route('laporan-bama.index'))
            ->assertRedirect(route('login'));
    }
}
