<?php

namespace Tests\Feature;

use App\Http\Controllers\LaporanBamaController;
use App\Models\InvoicePenyedia;
use App\Models\KontrakMakan;
use App\Models\PenyediaMakan;
use App\Models\RekeningPenyedia;
use App\Models\SenatAccount;
use App\Models\TransferPenyedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanBamaIntegrasiTest extends TestCase
{
    use RefreshDatabase;

    private User $ppk;
    private PenyediaMakan $penyedia;
    private SenatAccount $senatBsi;
    private SenatAccount $senatBni;
    private RekeningPenyedia $rekPenyedia;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['laporan_bama.view', 'laporan_bama.buat', 'laporan_bama.finalisasi'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $rolePpk = Role::firstOrCreate(['name' => 'ppk', 'guard_name' => 'web']);
        $rolePpk->syncPermissions(['laporan_bama.view', 'laporan_bama.buat', 'laporan_bama.finalisasi']);

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

    private function makeTransfer(string $bankGroup, float $nilai, string $status): TransferPenyedia
    {
        return TransferPenyedia::create([
            'periode_bulan'        => 5,
            'periode_tahun'        => 2025,
            'bank_group'           => $bankGroup,
            'senat_account_id'     => $bankGroup === 'BSI' ? $this->senatBsi->id : $this->senatBni->id,
            'rekening_penyedia_id' => $this->rekPenyedia->id,
            'total_nilai'          => $nilai,
            'jumlah_kelas'         => 2,
            'jumlah_taruna'        => 10,
            'status'               => $status,
            'dikonfirmasi_by'      => $this->ppk->id,
            'dikonfirmasi_at'      => now(),
        ]);
    }

    // Test 1: queryBabData menyertakan transferBSI, transferBNI, totalTransferPenyedia
    public function test_query_bab_data_menyertakan_data_transfer_penyedia(): void
    {
        $this->makeTransfer('BSI', 3_900_000, TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA);
        $this->makeTransfer('BNI', 5_850_000, TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA);

        $controller = new LaporanBamaController();
        $data = $controller->queryBabData(5, 2025);

        $this->assertArrayHasKey('transferBSI', $data);
        $this->assertArrayHasKey('transferBNI', $data);
        $this->assertArrayHasKey('totalTransferPenyedia', $data);
        $this->assertArrayHasKey('invoicePenyedia', $data);
        $this->assertArrayHasKey('terbayarDalamKampus', $data);

        $this->assertEquals('BSI', $data['transferBSI']->bank_group);
        $this->assertEquals('BNI', $data['transferBNI']->bank_group);
        $this->assertEquals(9_750_000, $data['totalTransferPenyedia']);
        $this->assertEquals(9_750_000, $data['terbayarDalamKampus']);
    }

    // Test 2: terbayarDalamKampus hanya menghitung transfer yang dikonfirmasi
    public function test_terbayar_hanya_transfer_dikonfirmasi(): void
    {
        $this->makeTransfer('BSI', 3_900_000, TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA);
        $this->makeTransfer('BNI', 5_850_000, TransferPenyedia::STATUS_DITRANSFER); // belum dikonfirmasi

        $controller = new LaporanBamaController();
        $data = $controller->queryBabData(5, 2025);

        $this->assertEquals(3_900_000, $data['terbayarDalamKampus']);
        $this->assertEquals(9_750_000, $data['totalTransferPenyedia']);
    }

    // Test 3: invoicePenyedia disertakan dalam data ketika tersedia
    public function test_invoice_penyedia_tersedia_dalam_query_bab_data(): void
    {
        $this->makeTransfer('BSI', 3_900_000, TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA);
        $this->makeTransfer('BNI', 5_850_000, TransferPenyedia::STATUS_DIKONFIRMASI_PENYEDIA);

        $invoice = InvoicePenyedia::create([
            'periode_bulan'   => 5,
            'periode_tahun'   => 2025,
            'penyedia_id'     => $this->penyedia->id,
            'total_nilai'     => 9_750_000,
            'nomor_invoice'   => 'INV/2025/005',
            'tanggal_invoice' => '2025-05-31',
            'status'          => InvoicePenyedia::STATUS_DIVERIFIKASI,
        ]);

        $controller = new LaporanBamaController();
        $data = $controller->queryBabData(5, 2025);

        $this->assertNotNull($data['invoicePenyedia']);
        $this->assertEquals('INV/2025/005', $data['invoicePenyedia']->nomor_invoice);
        $this->assertEquals(InvoicePenyedia::STATUS_DIVERIFIKASI, $data['invoicePenyedia']->status);
    }
}
