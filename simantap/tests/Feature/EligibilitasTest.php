<?php

namespace Tests\Feature;

use App\Models\Taruna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EligibilitasTest extends TestCase
{
    use RefreshDatabase;

    private function makeTaruna(array $attrs = []): Taruna
    {
        return Taruna::create(array_merge([
            'nama'              => 'Taruna Test',
            'nit'               => '123456789012',
            'angkatan'          => 2022,
            'prodi'             => 'Teknika',
            'kelas'             => 'A',
            'jenis_kelamin'     => 'L',
            'status_taruna'     => 'aktif',
            'penerima_bantuan'  => true,
        ], $attrs));
    }

    public function test_taruna_aktif_penerima_bantuan_is_eligible(): void
    {
        $taruna = $this->makeTaruna(['status_taruna' => 'aktif', 'penerima_bantuan' => true]);
        $this->assertTrue($taruna->is_eligible_bantuan);
    }

    public function test_taruna_tidak_penerima_bantuan_not_eligible(): void
    {
        $taruna = $this->makeTaruna(['penerima_bantuan' => false]);
        $this->assertFalse($taruna->is_eligible_bantuan);
    }

    public function test_taruna_cuti_not_eligible(): void
    {
        $taruna = $this->makeTaruna(['status_taruna' => 'cuti']);
        $this->assertFalse($taruna->is_eligible_bantuan);
    }

    public function test_taruna_pesiar_not_eligible(): void
    {
        $taruna = $this->makeTaruna(['status_taruna' => 'pesiar']);
        $this->assertFalse($taruna->is_eligible_bantuan);
    }

    public function test_taruna_sakit_di_rumah_keluarga_not_eligible(): void
    {
        $taruna = $this->makeTaruna(['status_taruna' => 'sakit_di_rumah_keluarga']);
        $this->assertFalse($taruna->is_eligible_bantuan);
    }

    public function test_taruna_sakit_di_kampus_is_eligible(): void
    {
        $taruna = $this->makeTaruna(['status_taruna' => 'sakit_di_kampus']);
        $this->assertTrue($taruna->is_eligible_bantuan);
    }

    public function test_eligible_bantuan_scope_returns_only_eligible(): void
    {
        $this->makeTaruna(['nit' => '000000000001', 'status_taruna' => 'aktif',  'penerima_bantuan' => true]);
        $this->makeTaruna(['nit' => '000000000002', 'status_taruna' => 'cuti',   'penerima_bantuan' => true]);
        $this->makeTaruna(['nit' => '000000000003', 'status_taruna' => 'aktif',  'penerima_bantuan' => false]);
        $this->makeTaruna(['nit' => '000000000004', 'status_taruna' => 'pesiar', 'penerima_bantuan' => true]);

        $this->assertCount(1, Taruna::eligibleBantuan()->get());
    }
}
