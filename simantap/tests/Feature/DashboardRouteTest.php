<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardRouteTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function test_super_admin_diarahkan_ke_dashboard_super_admin(): void
    {
        $this->assertEquals('dashboard.super_admin', $this->userWithRole('super_admin')->dashboardRoute());
    }

    public function test_kpa_diarahkan_ke_dashboard_kpa(): void
    {
        $this->assertEquals('dashboard.kpa', $this->userWithRole('kpa')->dashboardRoute());
    }

    public function test_ppk_diarahkan_ke_dashboard_ppk(): void
    {
        $this->assertEquals('dashboard.ppk', $this->userWithRole('ppk')->dashboardRoute());
    }

    public function test_wadir_iii_diarahkan_ke_dashboard_wadir_iii(): void
    {
        $this->assertEquals('dashboard.wadir_iii', $this->userWithRole('wadir_iii')->dashboardRoute());
    }

    public function test_pembina_karakter_diarahkan_ke_dashboard_pembina(): void
    {
        $this->assertEquals('dashboard.pembina', $this->userWithRole('pembina_karakter')->dashboardRoute());
    }

    public function test_senat_taruna_diarahkan_ke_dashboard_senat(): void
    {
        $this->assertEquals('dashboard.senat', $this->userWithRole('senat_taruna')->dashboardRoute());
    }

    public function test_kaprodi_diarahkan_ke_dashboard_kaprodi(): void
    {
        $this->assertEquals('dashboard.kaprodi', $this->userWithRole('kaprodi')->dashboardRoute());
    }

    public function test_penyedia_diarahkan_ke_dashboard_penyedia(): void
    {
        $this->assertEquals('dashboard.penyedia', $this->userWithRole('penyedia')->dashboardRoute());
    }

    public function test_auditor_diarahkan_ke_dashboard_auditor(): void
    {
        $this->assertEquals('dashboard.auditor', $this->userWithRole('auditor')->dashboardRoute());
    }

    public function test_user_tanpa_role_fallback_ke_ppk_dashboard(): void
    {
        $user = User::factory()->create();
        $this->assertEquals('dashboard.ppk', $user->dashboardRoute());
    }
}
