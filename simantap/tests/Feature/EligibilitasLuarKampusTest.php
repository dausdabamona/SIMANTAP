<?php

namespace Tests\Feature;

use App\Models\KegiatanLuarKampus;
use App\Models\PesertaKegiatanLuarKampus;
use App\Models\Taruna;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EligibilitasLuarKampusTest extends TestCase
{
    use RefreshDatabase;

    private User $senat;
    private Taruna $taruna;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['penerimaan.create', 'peserta_luar.input', 'kegiatan_luar.buat'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $role = Role::firstOrCreate(['name' => 'senat_taruna', 'guard_name' => 'web']);
        $role->givePermissionTo(['penerimaan.create', 'peserta_luar.input', 'kegiatan_luar.buat']);

        $this->senat = User::factory()->create();
        $this->senat->assignRole($role);

        $this->taruna = Taruna::create([
            'nama' => 'Taruna PKL', 'nit' => '210000000001',
            'angkatan' => 2023, 'prodi' => 'Nautika', 'kelas' => 'A',
            'jenis_kelamin' => 'L', 'status_taruna' => 'aktif', 'penerima_bantuan' => true,
        ]);
    }

    private function buatKegiatan(string $mulai, string $selesai, string $status): KegiatanLuarKampus
    {
        $kegiatan = KegiatanLuarKampus::create([
            'kode_kegiatan'          => 'KLK/2025/001',
            'nama_kegiatan'          => 'PKL Industri',
            'tanggal_mulai'          => $mulai,
            'tanggal_selesai'        => $selesai,
            'lokasi'                 => 'Sorong',
            'jenis_kegiatan'         => 'pkl',
            'kaprodi_id'             => $this->senat->id,
            'standar_biaya_per_hari' => 50000,
            'status'                 => $status,
        ]);

        PesertaKegiatanLuarKampus::create([
            'kegiatan_id' => $kegiatan->id,
            'taruna_id'   => $this->taruna->id,
            'hari_hadir'  => 0,
        ]);

        return $kegiatan;
    }

    public function test_taruna_aktif_pkl_tidak_muncul_di_list_eligible_dalam_kampus(): void
    {
        $this->buatKegiatan('2025-06-01', '2025-06-30', KegiatanLuarKampus::STATUS_DISETUJUI_PUSDIK);

        $this->actingAs($this->senat);

        // Request create penerimaan untuk tanggal 15 Juni — taruna sedang PKL aktif
        $response = $this->get(route('penerimaan.create') . '?tanggal=2025-06-15');
        $response->assertOk();

        $tarunaList = $response->viewData('tarunaList');
        $this->assertFalse($tarunaList->contains('id', $this->taruna->id),
            'Taruna yang sedang PKL aktif seharusnya tidak muncul dalam daftar eligible penerimaan makan dalam kampus.');
    }

    public function test_taruna_pkl_selesai_muncul_kembali_di_list_eligible(): void
    {
        // Kegiatan sudah selesai (status final)
        $this->buatKegiatan('2025-05-01', '2025-05-31', KegiatanLuarKampus::STATUS_SELESAI);

        $this->actingAs($this->senat);

        $response = $this->get(route('penerimaan.create') . '?tanggal=2025-06-15');
        $response->assertOk();

        $tarunaList = $response->viewData('tarunaList');
        $this->assertTrue($tarunaList->contains('id', $this->taruna->id),
            'Taruna yang PKL-nya sudah selesai harus kembali eligible untuk penerimaan makan dalam kampus.');
    }

    public function test_taruna_sakit_di_kampus_tetap_eligible_bantuan(): void
    {
        $taruna = Taruna::create([
            'nama' => 'Taruna Sakit Kampus', 'nit' => '210000000002',
            'angkatan' => 2023, 'prodi' => 'Nautika', 'kelas' => 'A',
            'jenis_kelamin' => 'L', 'status_taruna' => 'sakit_di_kampus', 'penerima_bantuan' => true,
        ]);

        $this->assertTrue($taruna->is_eligible_bantuan,
            'Taruna sakit di kampus seharusnya tetap eligible bantuan makan.');
    }

    public function test_taruna_sakit_di_rumah_tidak_eligible_bantuan(): void
    {
        $taruna = Taruna::create([
            'nama' => 'Taruna Sakit Rumah', 'nit' => '210000000003',
            'angkatan' => 2023, 'prodi' => 'Nautika', 'kelas' => 'A',
            'jenis_kelamin' => 'L', 'status_taruna' => 'sakit_di_rumah_keluarga', 'penerima_bantuan' => true,
        ]);

        $this->assertFalse($taruna->is_eligible_bantuan,
            'Taruna sakit di rumah keluarga seharusnya tidak eligible bantuan makan dalam kampus.');
    }
}
