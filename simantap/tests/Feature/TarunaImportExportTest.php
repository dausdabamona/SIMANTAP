<?php

namespace Tests\Feature;

use App\Exports\TarunaExport;
use App\Imports\TarunaImport;
use App\Models\Prodi;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TarunaImportExportTest extends TestCase
{
    use RefreshDatabase;

    private Prodi $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prodi = Prodi::create([
            'kode_prodi' => 'TPI',
            'nama_prodi' => 'Teknologi Penangkapan Ikan',
            'jenjang'    => 'D4',
            'is_active'  => true,
        ]);
    }

    // Test 1: Import model() method → prodi_id terisi benar dari kode_prodi
    public function test_import_model_mengisi_prodi_id_dari_kode_prodi(): void
    {
        $import = new TarunaImport();

        $taruna = $import->model([
            'nit'              => '20230001',
            'nama'             => 'Ahmad Fauzi',
            'nik'              => '',
            'angkatan'         => 2023,
            'kode_prodi'       => 'TPI',
            'kelas'            => 'X-A',
            'jenis_kelamin'    => 'L',
            'status_taruna'    => 'aktif',
            'penerima_bantuan' => 'ya',
        ]);

        $this->assertNotNull($taruna);
        $this->assertEquals('20230001', $taruna->nit);
        $this->assertEquals($this->prodi->id, $taruna->prodi_id);
        $this->assertEquals('TPI', $taruna->prodi);
        $this->assertTrue($taruna->penerima_bantuan);
    }

    // Test 2: Export → 12 kolom, nomor urut reset per instance (bukan static)
    public function test_export_kolom_lengkap_dan_nomor_urut_reset_per_instance(): void
    {
        // Buat 3 taruna tanpa factory
        foreach (['20230001', '20230002', '20230003'] as $nit) {
            Taruna::create([
                'nit'              => $nit,
                'nama'             => 'Taruna ' . $nit,
                'angkatan'         => 2023,
                'prodi_id'         => $this->prodi->id,
                'prodi'            => 'TPI',
                'kelas'            => 'X-A',
                'jenis_kelamin'    => 'L',
                'status_taruna'    => 'aktif',
                'penerima_bantuan' => true,
            ]);
        }

        // Headings harus 12 kolom
        $export   = new TarunaExport();
        $headings = $export->headings();
        $this->assertCount(12, $headings);
        $this->assertContains('Kode Prodi', $headings);
        $this->assertContains('Nama Prodi', $headings);
        $this->assertContains('Eligible Bantuan', $headings);

        // map() menghasilkan nomor mulai 1
        $collection = $export->collection();
        $this->assertCount(3, $collection);
        $firstRow = $export->map($collection->first());
        $this->assertEquals(1, $firstRow[0]);

        // Instance baru harus mulai dari 1 lagi (bukan static)
        $export2   = new TarunaExport();
        $firstRow2 = $export2->map($collection->first());
        $this->assertEquals(1, $firstRow2[0]);

        // Kolom Kode Prodi dan Nama Prodi terisi dari relasi
        $this->assertEquals('TPI', $firstRow[5]);
        $this->assertEquals('Teknologi Penangkapan Ikan', $firstRow[6]);
    }
}
