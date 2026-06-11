<?php

namespace Tests\Feature;

use App\Models\KontrakMakan;
use App\Models\PemesananHarian;
use App\Models\PenyediaMakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PemesananStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private User $senat;
    private User $pembina;
    private KontrakMakan $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions required for tests
        foreach (['pemesanan.ttd-senat', 'pemesanan.verifikasi', 'pemesanan.kirim'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $roleSenat   = Role::firstOrCreate(['name' => 'senat_taruna',    'guard_name' => 'web']);
        $rolePembina = Role::firstOrCreate(['name' => 'pembina_karakter', 'guard_name' => 'web']);
        $rolePpk     = Role::firstOrCreate(['name' => 'ppk',             'guard_name' => 'web']);

        $roleSenat->givePermissionTo('pemesanan.ttd-senat');
        $rolePembina->givePermissionTo('pemesanan.verifikasi');
        $rolePpk->givePermissionTo('pemesanan.kirim');

        $this->senat   = User::factory()->create();
        $this->senat->assignRole($roleSenat);

        $this->pembina = User::factory()->create();
        $this->pembina->assignRole($rolePembina);

        $penyedia = PenyediaMakan::create([
            'nama' => 'Penyedia Test', 'npwp' => '123', 'alamat' => 'Sorong',
            'bank' => 'BNI', 'nomor_rekening' => '1111111', 'nama_pemilik_rekening' => 'Penyedia',
        ]);

        $this->kontrak = KontrakMakan::create([
            'nomor_kontrak'  => 'KTR/2025/001',
            'tanggal_kontrak'=> now(),
            'tanggal_mulai'  => now()->startOfYear(),
            'tanggal_selesai'=> now()->endOfYear(),
            'nilai_kontrak'  => 100_000_000,
            'harga_porsi'    => 15000,
            'penyedia_id'    => $penyedia->id,
            'status'         => 'aktif',
        ]);
    }

    private function makePemesanan(array $attrs = []): PemesananHarian
    {
        return PemesananHarian::create(array_merge([
            'tanggal'              => today(),
            'kontrak_id'           => $this->kontrak->id,
            'jumlah_taruna_hadir'  => 100,
            'jumlah_porsi'         => 300,
            'harga_porsi_snapshot' => 15000,
            'nilai_total'          => 4_500_000,
            'status'               => PemesananHarian::STATUS_DRAFT,
        ], $attrs));
    }

    public function test_hitung_nilai_correctly_calculates_porsi_and_total(): void
    {
        $pemesanan = $this->makePemesanan(['jumlah_taruna_hadir' => 50, 'jumlah_porsi' => 0, 'nilai_total' => 0]);
        $pemesanan->hitungNilai();

        $porsiPerHari = config('simantap.porsi_per_hari', 3);
        $this->assertEquals(50 * $porsiPerHari, $pemesanan->jumlah_porsi);
        $this->assertEquals(50 * $porsiPerHari * 15000, $pemesanan->nilai_total);
    }

    public function test_ttd_senat_transitions_to_correct_state(): void
    {
        $pemesanan = $this->makePemesanan();

        $this->actingAs($this->senat)
            ->post(route('pemesanan.ttd-senat', $pemesanan))
            ->assertRedirect();

        $this->assertEquals(PemesananHarian::STATUS_DRAFT, $pemesanan->fresh()->status);
        $this->assertNotNull($pemesanan->fresh()->ttd_senat_id);
    }

    public function test_verifikasi_pembina_requires_senat_signature_first(): void
    {
        $pemesanan = $this->makePemesanan(); // no ttd_senat_id

        $this->actingAs($this->pembina)
            ->post(route('pemesanan.verifikasi', $pemesanan))
            ->assertRedirect();

        // Status should not advance without senat signature
        $this->assertNotEquals('diverifikasi_pembina', $pemesanan->fresh()->status);
    }

    public function test_full_approval_flow_draft_to_dikirim(): void
    {
        $ppkRole = Role::findByName('ppk');
        $ppk     = User::factory()->create();
        $ppk->assignRole($ppkRole);

        $pemesanan = $this->makePemesanan();

        // Step 1: TTD Senat
        $this->actingAs($this->senat)
            ->post(route('pemesanan.ttd-senat', $pemesanan));

        $this->assertNotNull($pemesanan->fresh()->ttd_senat_id);

        // Step 2: Verifikasi Pembina
        $this->actingAs($this->pembina)
            ->post(route('pemesanan.verifikasi', $pemesanan), ['catatan_pembina' => 'OK']);

        $this->assertEquals('diverifikasi_pembina', $pemesanan->fresh()->status);

        // Step 3: Kirim ke Penyedia
        $this->actingAs($ppk)
            ->post(route('pemesanan.kirim', $pemesanan));

        $this->assertEquals('dikirim_penyedia', $pemesanan->fresh()->status);
    }
}
