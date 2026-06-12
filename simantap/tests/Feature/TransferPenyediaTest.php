<?php

namespace Tests\Feature;

use App\Models\InvoicePenyedia;
use App\Models\KontrakMakan;
use App\Models\PengajuanPembayaran;
use App\Models\PenyediaMakan;
use App\Models\RekeningPenyedia;
use App\Models\SenatAccount;
use App\Models\TransferPenyedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TransferPenyediaTest extends TestCase
{
    use RefreshDatabase;

    private User $wadir;
    private User $senat;
    private User $ppk;
    private SenatAccount $senatBsi;
    private SenatAccount $senatBni;
    private RekeningPenyedia $rekPenyedia;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        foreach (['pembayaran.view', 'pembayaran.konfirmasi', 'rekap.view'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roleWadir = Role::firstOrCreate(['name' => 'wadir_iii', 'guard_name' => 'web']);
        $roleWadir->syncPermissions(['pembayaran.view', 'rekap.view']);

        $roleSenat = Role::firstOrCreate(['name' => 'senat_taruna', 'guard_name' => 'web']);
        $roleSenat->syncPermissions(['pembayaran.view']);

        $rolePpk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $rolePpk->syncPermissions(['pembayaran.view', 'pembayaran.konfirmasi']);

        $this->wadir = User::factory()->create();
        $this->wadir->assignRole($roleWadir);

        $this->senat = User::factory()->create();
        $this->senat->assignRole($roleSenat);

        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($rolePpk);

        $this->senatBsi = SenatAccount::create([
            'nama_akun' => 'Senat BSI', 'bank' => 'BSI', 'bank_group' => 'BSI',
            'untuk_tingkat' => '1', 'nomor_rekening' => '7200111111',
            'nama_pemilik' => 'Senat Taruna', 'is_aktif' => true,
        ]);

        $this->senatBni = SenatAccount::create([
            'nama_akun' => 'Senat BNI', 'bank' => 'BNI', 'bank_group' => 'BNI',
            'untuk_tingkat' => '2,3', 'nomor_rekening' => '1000222222',
            'nama_pemilik' => 'Senat Taruna', 'is_aktif' => true,
        ]);

        $penyedia = PenyediaMakan::create(['nama' => 'Penyedia Test', 'npwp' => '000', 'alamat' => 'X']);
        $this->rekPenyedia = RekeningPenyedia::create([
            'penyedia_id'    => $penyedia->id,
            'label'          => 'Rekening Utama',
            'bank'           => 'BRI',
            'nomor_rekening' => '9999001001',
            'nama_pemilik'   => 'Penyedia Test',
            'is_default'     => true,
            'is_active'      => true,
            'dibuat_by'      => $this->ppk->id,
        ]);
    }

    private function makeSpm(string $bankGroup, int $jumlah = 2): array
    {
        $spms = [];
        for ($i = 1; $i <= $jumlah; $i++) {
            $spms[] = PengajuanPembayaran::create([
                'nomor_pengajuan' => "PBY/2025/05/{$bankGroup}/{$i}",
                'periode_bulan'   => 5,
                'periode_tahun'   => 2025,
                'kelas'           => ($bankGroup === 'BSI' ? 'X' : 'XI') . "-" . chr(64 + $i),
                'bank_group'      => $bankGroup,
                'rekening_senat_id' => $bankGroup === 'BSI' ? $this->senatBsi->id : $this->senatBni->id,
                'total_taruna'    => 5,
                'total_porsi'     => 130,
                'total_nilai'     => 1_950_000,
                'status'          => PengajuanPembayaran::STATUS_DEBIT_SELESAI,
            ]);
        }
        return $spms;
    }

    // Test 1: Semua SPM BSI debit_selesai → TransferPenyedia BSI terbentuk dengan total benar
    public function test_generate_transfer_bsi_dari_semua_spm_debit_selesai(): void
    {
        $spms = $this->makeSpm('BSI', 2);

        $transfer = TransferPenyedia::generateUntukPeriode(5, 2025, 'BSI');

        $this->assertEquals('BSI', $transfer->bank_group);
        $this->assertEquals(2, $transfer->jumlah_kelas);
        $this->assertEquals(3_900_000, $transfer->total_nilai);
        $this->assertEquals(TransferPenyedia::STATUS_MENUNGGU, $transfer->status);
        $this->assertCount(2, $transfer->spms);
    }

    // Test 2: Semua SPM BNI debit_selesai → TransferPenyedia BNI terbentuk
    public function test_generate_transfer_bni_dari_semua_spm_debit_selesai(): void
    {
        $spms = $this->makeSpm('BNI', 3);

        $transfer = TransferPenyedia::generateUntukPeriode(5, 2025, 'BNI');

        $this->assertEquals('BNI', $transfer->bank_group);
        $this->assertEquals(3, $transfer->jumlah_kelas);
        $this->assertEquals(5_850_000, $transfer->total_nilai);
        $this->assertCount(3, $transfer->spms);
    }

    // Test 3: Wadir III setujui → status disetujui_wadir
    public function test_wadir_setujui_transfer_mengubah_status(): void
    {
        $this->makeSpm('BSI');
        $transfer = TransferPenyedia::generateUntukPeriode(5, 2025, 'BSI');

        $this->actingAs($this->wadir)
            ->post(route('transfer-penyedia.setujui-wadir', $transfer))
            ->assertRedirect();

        $fresh = $transfer->fresh();
        $this->assertEquals(TransferPenyedia::STATUS_DISETUJUI_WADIR, $fresh->status);
        $this->assertEquals($this->wadir->id, $fresh->disetujui_wadir_by);
        $this->assertNotNull($fresh->disetujui_wadir_at);
    }

    // Test 4: Senat upload bukti → status ditransfer
    public function test_senat_upload_bukti_mengubah_status_ke_ditransfer(): void
    {
        $this->makeSpm('BSI');
        $transfer = TransferPenyedia::generateUntukPeriode(5, 2025, 'BSI');
        $transfer->update([
            'status'             => TransferPenyedia::STATUS_DISETUJUI_WADIR,
            'disetujui_wadir_by' => $this->wadir->id,
            'disetujui_wadir_at' => now(),
        ]);

        $file = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $this->actingAs($this->senat)
            ->post(route('transfer-penyedia.upload-bukti', $transfer), [
                'bukti_transfer'   => $file,
                'tanggal_transfer' => '2025-05-20',
            ])
            ->assertRedirect();

        $fresh = $transfer->fresh();
        $this->assertEquals(TransferPenyedia::STATUS_DITRANSFER, $fresh->status);
        $this->assertNotNull($fresh->bukti_transfer);
        $this->assertEquals('2025-05-20', $fresh->tanggal_transfer->format('Y-m-d'));
    }

    // Test 5: PPK konfirmasi → status dikonfirmasi_penyedia
    public function test_ppk_konfirmasi_transfer_mengubah_status(): void
    {
        $this->makeSpm('BSI');
        $transfer = TransferPenyedia::generateUntukPeriode(5, 2025, 'BSI');
        $transfer->update([
            'status'           => TransferPenyedia::STATUS_DITRANSFER,
            'bukti_transfer'   => 'transfer-penyedia/test.pdf',
            'tanggal_transfer' => now(),
            'ditransfer_by'    => $this->senat->id,
        ]);

        $this->actingAs($this->ppk)
            ->post(route('transfer-penyedia.konfirmasi', $transfer))
            ->assertRedirect();

        $fresh = $transfer->fresh();
        $this->assertEquals(TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA, $fresh->status);
        $this->assertEquals($this->ppk->id, $fresh->dikonfirmasi_by);
        $this->assertNotNull($fresh->dikonfirmasi_at);
    }
}
