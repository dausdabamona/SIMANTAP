<?php

namespace Tests\Feature;

use App\Models\InvoicePenyedia;
use App\Models\KontrakMakan;
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

class InvoicePenyediaTest extends TestCase
{
    use RefreshDatabase;

    private User $ppk;
    private User $penyediaUser;
    private PenyediaMakan $penyedia;
    private SenatAccount $senatBsi;
    private SenatAccount $senatBni;
    private RekeningPenyedia $rekPenyedia;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        foreach (['pembayaran.view', 'pembayaran.lpj', 'rekap.view'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rolePpk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $rolePpk->syncPermissions(['pembayaran.view', 'pembayaran.lpj']);

        $rolePenyedia = Role::firstOrCreate(['name' => 'penyedia', 'guard_name' => 'web']);
        $rolePenyedia->syncPermissions(['pembayaran.view']);

        $this->ppk = User::factory()->create();
        $this->ppk->assignRole($rolePpk);

        $this->penyediaUser = User::factory()->create();
        $this->penyediaUser->assignRole($rolePenyedia);

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

        $this->penyedia = PenyediaMakan::create(['nama' => 'Penyedia Test', 'npwp' => '000', 'alamat' => 'X']);
        $this->rekPenyedia = RekeningPenyedia::create([
            'penyedia_id'    => $this->penyedia->id,
            'label'          => 'Rekening Utama',
            'bank'           => 'BRI',
            'nomor_rekening' => '9999001001',
            'nama_pemilik'   => 'Penyedia Test',
            'is_default'     => true,
            'is_active'      => true,
            'dibuat_by'      => $this->ppk->id,
        ]);

        KontrakMakan::create([
            'nomor_kontrak'   => 'KTR/2025/001',
            'tanggal_kontrak' => now(),
            'tanggal_mulai'   => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'nilai_kontrak'   => 100_000_000,
            'harga_porsi'     => 15000,
            'penyedia_id'     => $this->penyedia->id,
            'status'          => 'aktif',
        ]);
    }

    private function makeTransferDikonfirmasi(string $bankGroup, float $nilai = 5_000_000): TransferPenyedia
    {
        return TransferPenyedia::create([
            'periode_bulan'       => 5,
            'periode_tahun'       => 2025,
            'bank_group'          => $bankGroup,
            'senat_account_id'    => $bankGroup === 'BSI' ? $this->senatBsi->id : $this->senatBni->id,
            'rekening_penyedia_id'=> $this->rekPenyedia->id,
            'total_nilai'         => $nilai,
            'jumlah_kelas'        => 2,
            'jumlah_taruna'       => 10,
            'status'              => TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA,
            'dikonfirmasi_by'     => $this->ppk->id,
            'dikonfirmasi_at'     => now(),
        ]);
    }

    // Test 1: Kedua transfer dikonfirmasi → InvoicePenyedia terbentuk dengan total BSI + BNI
    public function test_generate_invoice_setelah_bsi_dan_bni_dikonfirmasi(): void
    {
        $this->makeTransferDikonfirmasi('BSI', 3_900_000);
        $this->makeTransferDikonfirmasi('BNI', 5_850_000);

        $invoice = InvoicePenyedia::generateUntukPeriode(5, 2025);

        $this->assertNotNull($invoice);
        $this->assertEquals(9_750_000, $invoice->total_nilai);
        $this->assertEquals(InvoicePenyedia::STATUS_MENUNGGU, $invoice->status);
        $this->assertEquals($this->penyedia->id, $invoice->penyedia_id);
    }

    // Test 2: Penyedia tidak bisa upload sebelum kedua transfer dikonfirmasi
    public function test_penyedia_tidak_bisa_upload_sebelum_kedua_transfer_dikonfirmasi(): void
    {
        // Hanya BSI yang dikonfirmasi, BNI belum
        $this->makeTransferDikonfirmasi('BSI', 3_900_000);
        TransferPenyedia::create([
            'periode_bulan'        => 5,
            'periode_tahun'        => 2025,
            'bank_group'           => 'BNI',
            'senat_account_id'     => $this->senatBni->id,
            'rekening_penyedia_id' => $this->rekPenyedia->id,
            'total_nilai'          => 5_850_000,
            'jumlah_kelas'         => 2,
            'jumlah_taruna'        => 10,
            'status'               => TransferPenyedia::STATUS_DITRANSFER, // belum dikonfirmasi
        ]);

        $invoice = InvoicePenyedia::create([
            'periode_bulan' => 5,
            'periode_tahun' => 2025,
            'penyedia_id'   => $this->penyedia->id,
            'total_nilai'   => 9_750_000,
            'status'        => InvoicePenyedia::STATUS_MENUNGGU,
        ]);

        $file = UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf');

        $this->actingAs($this->penyediaUser)
            ->post(route('invoice-penyedia.upload', $invoice), [
                'nomor_invoice'   => 'INV/2025/001',
                'tanggal_invoice' => '2025-05-31',
                'total_nilai'     => 9_750_000,
                'file_invoice'    => $file,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // Test 3: Penyedia upload invoice → status diterima
    public function test_penyedia_upload_invoice_mengubah_status_ke_diterima(): void
    {
        $this->makeTransferDikonfirmasi('BSI', 3_900_000);
        $this->makeTransferDikonfirmasi('BNI', 5_850_000);

        $invoice = InvoicePenyedia::create([
            'periode_bulan' => 5,
            'periode_tahun' => 2025,
            'penyedia_id'   => $this->penyedia->id,
            'total_nilai'   => 9_750_000,
            'status'        => InvoicePenyedia::STATUS_MENUNGGU,
        ]);

        $file = UploadedFile::fake()->create('invoice.pdf', 100, 'application/pdf');

        $this->actingAs($this->penyediaUser)
            ->post(route('invoice-penyedia.upload', $invoice), [
                'nomor_invoice'   => 'INV/2025/001',
                'tanggal_invoice' => '2025-05-31',
                'total_nilai'     => 9_750_000,
                'file_invoice'    => $file,
            ])
            ->assertRedirect();

        $fresh = $invoice->fresh();
        $this->assertEquals(InvoicePenyedia::STATUS_DITERIMA, $fresh->status);
        $this->assertEquals('INV/2025/001', $fresh->nomor_invoice);
        $this->assertNotNull($fresh->file_invoice);
    }

    // Test 4: PPK verifikasi invoice → status diverifikasi_ppk
    public function test_ppk_verifikasi_invoice_mengubah_status(): void
    {
        $this->makeTransferDikonfirmasi('BSI', 3_900_000);
        $this->makeTransferDikonfirmasi('BNI', 5_850_000);

        $invoice = InvoicePenyedia::create([
            'periode_bulan'   => 5,
            'periode_tahun'   => 2025,
            'penyedia_id'     => $this->penyedia->id,
            'total_nilai'     => 9_750_000,
            'nomor_invoice'   => 'INV/2025/001',
            'tanggal_invoice' => '2025-05-31',
            'file_invoice'    => 'invoice-penyedia/test.pdf',
            'status'          => InvoicePenyedia::STATUS_DITERIMA,
        ]);

        $this->actingAs($this->ppk)
            ->post(route('invoice-penyedia.verifikasi', $invoice))
            ->assertRedirect();

        $fresh = $invoice->fresh();
        $this->assertEquals(InvoicePenyedia::STATUS_DIVERIFIKASI, $fresh->status);
        $this->assertEquals($this->ppk->id, $fresh->diverifikasi_by);
        $this->assertNotNull($fresh->diverifikasi_at);
    }
}
