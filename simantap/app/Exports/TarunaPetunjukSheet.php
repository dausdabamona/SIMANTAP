<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class TarunaPetunjukSheet implements FromArray, WithTitle
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        return [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT TARUNA'],
            [''],
            ['1. Isi data di sheet "Template Import" mulai dari baris ke-3 (baris 1 = header, baris 2 = keterangan)'],
            ['2. JANGAN mengubah urutan atau nama kolom header'],
            ['3. Kolom WAJIB: nit, nama, angkatan, kode_prodi, kelas, jenis_kelamin'],
            ['4. Kolom OPSIONAL: nik, status_taruna (default: aktif), penerima_bantuan (default: ya)'],
            ['5. NIT harus UNIK — tidak boleh sama dengan taruna yang sudah ada di sistem'],
            ['6. kode_prodi harus sesuai daftar di sheet "Referensi Prodi"'],
            ['7. Jenis kelamin: L (Laki-laki) atau P (Perempuan)'],
            ['8. Kelas gunakan format: X-A, X-B, XI-A, XI-B, XII-A, dst'],
            [''],
            ['STATUS TARUNA yang valid:'],
            ['aktif                   → Taruna aktif, eligible bantuan makan'],
            ['cuti                    → Cuti akademik, TIDAK eligible bantuan makan'],
            ['pesiar                  → Izin pesiar, TIDAK eligible bantuan makan'],
            ['sakit_di_kampus         → Sakit di asrama/kampus, TETAP eligible bantuan makan'],
            ['sakit_di_rumah_keluarga → Sakit pulang ke rumah, TIDAK eligible bantuan makan'],
            ['penundaan_studi         → Hukuman penundaan studi, TIDAK eligible bantuan makan'],
        ];
    }
}
