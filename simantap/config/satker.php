<?php

return [
    'nama'          => env('SATKER_NAMA',   'Politeknik Kelautan dan Perikanan Sorong'),
    'nama_singkat'  => env('SATKER_SINGKAT','Poltek KP Sorong'),
    'alamat'        => env('SATKER_ALAMAT', 'Jl. Kapitan Pattimura Km. 10, Sorong, Papua Barat Daya'),
    'telepon'       => env('SATKER_TELP',   '(0951) 000000'),
    'email'         => env('SATKER_EMAIL',  'info@polikpsorong.ac.id'),
    'website'       => env('SATKER_WEB',    'polikpsorong.ac.id'),
    'kpa'           => [
        'nama' => env('KPA_NAMA', 'NAMA DIREKTUR'),
        'nip'  => env('KPA_NIP',  '196X0101 199X01 1 001'),
    ],
    'ppk'           => [
        'nama' => env('PPK_NAMA', 'NAMA PPK'),
        'nip'  => env('PPK_NIP',  '197X0101 200X01 1 001'),
    ],
    'nomor_dipa'    => env('SATKER_DIPA',   'SP DIPA-032.12.2.XXXXXX/2025'),
];
