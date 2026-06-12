<?php

namespace Tests\Feature;

use App\Models\PenyediaMakan;
use App\Models\RekeningPenyedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RekeningPenyediaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PenyediaMakan $penyedia;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['penyedia.view', 'penyedia.edit'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $role->givePermissionTo(['penyedia.view', 'penyedia.edit']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);

        $this->penyedia = PenyediaMakan::create([
            'nama'   => 'CV Maju Jaya',
            'npwp'   => '99.999.999.9-999.000',
            'alamat' => 'Jl. Merdeka 1, Sorong',
        ]);
    }

    private function makeRekening(array $override = []): RekeningPenyedia
    {
        return RekeningPenyedia::create(array_merge([
            'penyedia_id'    => $this->penyedia->id,
            'bank'           => 'BRI',
            'nomor_rekening' => '0001234567',
            'nama_pemilik'   => 'CV Maju Jaya',
            'is_active'      => true,
            'is_default'     => true,
            'dibuat_by'      => $this->admin->id,
        ], $override));
    }

    public function test_bisa_tambah_rekening_baru(): void
    {
        $this->actingAs($this->admin)
            ->post(route('penyedia.rekening.store', $this->penyedia), [
                'bank'           => 'BNI',
                'nomor_rekening' => '1112223334',
                'nama_pemilik'   => 'CV Maju Jaya',
                'is_active'      => '1',
                'is_default'     => '1',
            ])
            ->assertRedirect(route('penyedia.rekening.index', $this->penyedia));

        $this->assertDatabaseHas('rekening_penyedias', [
            'penyedia_id'    => $this->penyedia->id,
            'bank'           => 'BNI',
            'nomor_rekening' => '1112223334',
        ]);
    }

    public function test_hanya_satu_rekening_default_per_penyedia(): void
    {
        $r1 = $this->makeRekening(['nomor_rekening' => '0000000001', 'is_default' => true]);
        $r2 = $this->makeRekening(['nomor_rekening' => '0000000002', 'is_default' => false]);

        // Jadikan r2 default via HTTP
        $this->actingAs($this->admin)
            ->patch(route('penyedia.rekening.update', [$this->penyedia, $r2]), [
                'bank'           => $r2->bank,
                'nomor_rekening' => $r2->nomor_rekening,
                'nama_pemilik'   => $r2->nama_pemilik,
                'is_active'      => '1',
                'is_default'     => '1',
            ])
            ->assertRedirect();

        $this->assertFalse($r1->fresh()->is_default, 'r1 harus bukan default lagi');
        $this->assertTrue($r2->fresh()->is_default, 'r2 harus menjadi default');
    }

    public function test_rekening_default_tidak_bisa_dihapus(): void
    {
        $r = $this->makeRekening();

        $this->actingAs($this->admin)
            ->delete(route('penyedia.rekening.destroy', [$this->penyedia, $r]))
            ->assertStatus(422);

        $this->assertDatabaseHas('rekening_penyedias', ['id' => $r->id, 'deleted_at' => null]);
    }

    public function test_rekening_non_default_bisa_dihapus(): void
    {
        $r = $this->makeRekening(['nomor_rekening' => '9998887771', 'is_default' => false]);

        $this->actingAs($this->admin)
            ->delete(route('penyedia.rekening.destroy', [$this->penyedia, $r]))
            ->assertRedirect();

        $this->assertSoftDeleted('rekening_penyedias', ['id' => $r->id]);
    }

    public function test_nomor_rekening_harus_unik(): void
    {
        $this->makeRekening(['nomor_rekening' => '5556667778']);

        $this->actingAs($this->admin)
            ->post(route('penyedia.rekening.store', $this->penyedia), [
                'bank'           => 'BCA',
                'nomor_rekening' => '5556667778', // duplikat
                'nama_pemilik'   => 'Lainnya',
                'is_active'      => '1',
                'is_default'     => '0',
            ])
            ->assertSessionHasErrors('nomor_rekening');
    }

    public function test_penyedia_memiliki_relasi_rekening(): void
    {
        $this->makeRekening(['nomor_rekening' => '7771112223']);
        $this->penyedia->load('rekening', 'rekeningDefault');

        $this->assertCount(1, $this->penyedia->rekening);
        $this->assertNotNull($this->penyedia->rekeningDefault);
        $this->assertEquals('7771112223', $this->penyedia->rekeningDefault->nomor_rekening);
    }

    public function test_halaman_index_rekening_tampil(): void
    {
        $this->actingAs($this->admin)
            ->get(route('penyedia.rekening.index', $this->penyedia))
            ->assertOk();
    }
}
