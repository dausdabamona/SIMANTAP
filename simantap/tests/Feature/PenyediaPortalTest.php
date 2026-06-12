<?php

namespace Tests\Feature;

use App\Models\KontrakMakan;
use App\Models\PemesananHarian;
use App\Models\PengajuanPembayaran;
use App\Models\PenyediaMakan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PenyediaPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $userPenyedia;
    private PenyediaMakan $penyedia;
    private KontrakMakan $kontrak;

    protected function setUp(): void
    {
        parent::setUp();

        $rolePenyedia = Role::firstOrCreate(['name' => 'penyedia', 'guard_name' => 'web']);

        $this->userPenyedia = User::factory()->create();
        $this->userPenyedia->assignRole($rolePenyedia);

        $this->penyedia = PenyediaMakan::create([
            'nama'    => 'Penyedia Test',
            'npwp'    => '123456789',
            'alamat'  => 'Sorong',
            'user_id' => $this->userPenyedia->id,
        ]);

        $this->kontrak = KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/001',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 50_000_000,
            'harga_porsi'     => 20000,
            'penyedia_id'     => $this->penyedia->id,
            'status'          => 'aktif',
        ]);
    }

    public function test_penyedia_dapat_akses_halaman_pesanan(): void
    {
        $this->actingAs($this->userPenyedia)
            ->get(route('penyedia.pesanan'))
            ->assertOk();
    }

    public function test_penyedia_dapat_akses_halaman_pembayaran(): void
    {
        $this->actingAs($this->userPenyedia)
            ->get(route('penyedia.pembayaran'))
            ->assertOk();
    }

    public function test_penyedia_konfirmasi_pesanan_mengubah_status_ke_disajikan(): void
    {
        $pemesanan = PemesananHarian::create([
            'tanggal'               => now()->toDateString(),
            'kontrak_id'            => $this->kontrak->id,
            'jumlah_taruna_hadir'   => 50,
            'jumlah_porsi'          => 50,
            'harga_porsi_snapshot'  => 20000,
            'nilai_total'           => 1_000_000,
            'menu_sesuai_jadwal'    => true,
            'status'                => 'dikirim_penyedia',
        ]);

        $this->actingAs($this->userPenyedia)
            ->post(route('penyedia.pesanan.konfirmasi', $pemesanan->id))
            ->assertRedirect();

        $this->assertEquals('disajikan', $pemesanan->fresh()->status);
    }

    public function test_konfirmasi_pesanan_gagal_jika_status_bukan_dikirim_penyedia(): void
    {
        $pemesanan = PemesananHarian::create([
            'tanggal'               => now()->toDateString(),
            'kontrak_id'            => $this->kontrak->id,
            'jumlah_taruna_hadir'   => 50,
            'jumlah_porsi'          => 50,
            'harga_porsi_snapshot'  => 20000,
            'nilai_total'           => 1_000_000,
            'menu_sesuai_jadwal'    => true,
            'status'                => 'selesai',
        ]);

        $this->actingAs($this->userPenyedia)
            ->post(route('penyedia.pesanan.konfirmasi', $pemesanan->id))
            ->assertForbidden();
    }

    public function test_konfirmasi_transfer_mengubah_status_ke_lpj_ppk(): void
    {
        $pembayaran = PengajuanPembayaran::create([
            'nomor_pengajuan' => 'PAY/2025/06/001',
            'periode_bulan'   => 6,
            'periode_tahun'   => 2025,
            'total_nilai'     => 5_000_000,
            'status'          => 'debit_selesai',
        ]);

        $this->actingAs($this->userPenyedia)
            ->post(route('penyedia.pembayaran.konfirmasi', $pembayaran->id))
            ->assertRedirect();

        $this->assertEquals('lpj_ppk', $pembayaran->fresh()->status);
    }

    public function test_user_tanpa_role_penyedia_ditolak(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('penyedia.pesanan'))
            ->assertForbidden();
    }
}
