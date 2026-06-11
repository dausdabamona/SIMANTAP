# TAHAP 1 DESIGN — SIMANTAP
## Sistem Informasi Manajemen Bantuan Makan Taruna
### Politeknik Kelautan dan Perikanan Sorong
### Stack: Laravel 12 + PHP 8.4 + MySQL 8

---

## A. ENTITY RELATIONSHIP DIAGRAM (ERD)

```
+---------------------------------------------------------------------+
|                       SIMANTAP ERD — COMPLETE                       |
+---------------------------------------------------------------------+

+----------------------+         +------------------------------------------+
|        users         |         |                  taruna                   |
+----------------------+         +------------------------------------------+
| PK id (bigint)       |         | PK id (bigint)                           |
|    name (varchar100) |         |    nit (varchar20) UNIQUE                |
|    email (varchar255)|         |    nama (varchar100)                     |
|    password (varchar)|         |    nik (varchar16) UNIQUE NULLABLE       |
|    created_at        |         |    angkatan (year)                       |
|    updated_at        |         |    prodi (varchar100)                    |
|    deleted_at        |         |    kelas (varchar20) NULLABLE            |
+----------------------+         |    jenis_kelamin ENUM(L,P)               |
         |                       |    status_taruna ENUM(aktif,cuti,pesiar, |
         | (via Spatie Roles)    |      sakit_di_kampus,                   |
                                 |      sakit_di_rumah_keluarga,           |
                                 |      penundaan_studi) DEFAULT aktif      |
                                 |    penerima_bantuan (bool) DEFAULT true  |
                                 |    created_at, updated_at, deleted_at    |
                                 | IDX: (status_taruna, penerima_bantuan)   |
                                 | IDX: angkatan                            |
                                 +------------------------------------------+
                                           | 1
                          +----------------+-------------------+
                          | 1              | 1                 | M
                          v                v                   v
              +-----------------+  +----------------+  +--------------------------+
              | rekening_taruna |  |  sk_penerima   |  |    penerimaan_makan      |
              +-----------------+  +----------------+  +--------------------------+
              | PK id           |  | PK id          |  | PK id                    |
              | FK taruna_id    |  |    nomor_sk    |  | FK taruna_id             |
              |    bank         |  |    judul       |  |    tanggal (date)        |
              |    nomor_       |  |    penerbit    |  |    jenis_makan ENUM(     |
              |    rekening     |  |    tanggal_sk  |  |      sarapan,            |
              |    UNIQUE       |  |    periode_    |  |      makan_siang,        |
              |    nama_pemilik |  |    mulai       |  |      makan_malam)        |
              |    created_at   |  |    periode_    |  |    jumlah_porsi_diterima |
              |    updated_at   |  |    selesai     |  |    status_eligibilitas   |
              |    deleted_at   |  |    jenis_sk    |  |      ENUM(dapat,         |
              +-----------------+  |    file_sk     |  |           tidak_dapat)   |
                                   |    created_at  |  |    alasan_pengecualian   |
                                   |    updated_at  |  |    file_lampiran_        |
                                   |    deleted_at  |  |      pengecualian        |
                                   +----------------+  |    lat DECIMAL(10,8)     |
                                                        |    long DECIMAL(11,8)   |
                                                        |    created_at           |
                                                        |    updated_at           |
                                                        |    deleted_at           |
                                                        | IDX:(taruna_id,tanggal, |
                                                        |      jenis_makan)       |
                                                        +--------------------------+
                                                                    | 1
                                                                    v M
                                                        +----------------------+
                                                        |   monitoring_foto    |
                                                        +----------------------+
                                                        | PK id                |
                                                        | FK penerimaan_id NULL|
                                                        | FK monitoring_id NULL|
                                                        |    file_path         |
                                                        |    urutan TINYINT    |
                                                        |      (1-5)           |
                                                        |    lat DECIMAL(10,8) |
                                                        |    long DECIMAL(11,8)|
                                                        |    captured_at       |
                                                        |    created_at        |
                                                        |    updated_at        |
                                                        +----------------------+

+------------------------------+        +--------------------------------------------+
|       penyedia_makan         |        |              kontrak_makan                 |
+------------------------------+        +--------------------------------------------+
| PK id (bigint)               |<-------| FK penyedia_id                             |
|    nama (varchar150)         | 1   M  | PK id (bigint)                             |
|    npwp (varchar20) UNIQUE   |        |    nomor_kontrak (varchar50) UNIQUE        |
|    alamat (text)             |        |    tanggal_kontrak (date)                  |
|    telp (varchar20)          |        |    tanggal_mulai (date)                    |
|    email (varchar100)        |        |    tanggal_selesai (date)                  |
|    bank (varchar50)          |        |    nilai_kontrak DECIMAL(15,2)             |
|    nomor_rekening (varchar30)|        |    harga_porsi DECIMAL(10,2)               |
|    created_at, updated_at    |        |    pihak_pertama VARCHAR(100)              |
|    deleted_at                |        |      DEFAULT 'Senat Taruna'                |
+------------------------------+        |    status ENUM(draft,aktif,               |
                                        |      berakhir,dibatalkan)                  |
                                        |    file_kontrak (varchar255) NULL          |
                                        |    file_addendum (varchar255) NULL         |
                                        |    berita_acara_penunjukan                 |
                                        |      (varchar255) NULL                     |
                                        |    notulensi_rapat (varchar255) NULL       |
                                        |    created_at, updated_at, deleted_at      |
                                        +--------------------------------------------+
                                                         | 1
                                     +-------------------+-------------------+
                                     | M                                     | M
                                     v                                       v
                         +-------------------------+           +------------------------------+
                         |    pemesanan_harian      |           |      rekap_bulanan            |
                         +-------------------------+           +------------------------------+
                         | PK id                   |           | PK id                        |
                         |    tanggal (date)        |           |    periode_bulan (tinyint)   |
                         | FK kontrak_id            |           |    periode_tahun (year)      |
                         |    jumlah_taruna_hadir  |           | FK taruna_id                 |
                         |    jumlah_porsi          |           |    total_porsi (int)         |
                         |    nilai_total           |           |    nilai_bantuan DEC(15,2)   |
                         |      DECIMAL(15,2)       |           | FK kontrak_id                |
                         |    status ENUM(          |           |    status ENUM(              |
                         |      draft,              |           |      draft,                  |
                         |      diverifikasi_       |           |      dihitung_ppk,           |
                         |        pembina,          |           |      ditandatangani_pembina, |
                         |      dikirim_penyedia,   |           |      ditandatangani_ppk,     |
                         |      perubahan,          |           |      ditandatangani_kpa,     |
                         |      disajikan,          |           |      final)                  |
                         |      selesai)            |           |    created_at, updated_at    |
                         |    created_at, updated_at|           |    deleted_at                |
                         |    deleted_at            |           | IDX: (taruna_id,             |
                         | IDX: (tanggal,kontrak_id)|           |   periode_bulan,             |
                         +-------------------------+           |   periode_tahun)             |
                                    | 1                         +------------------------------+
                                    v M                                     | 1
                         +-------------------------+                        v M
                         |  berita_acara_perubahan  |           +------------------------------+
                         +-------------------------+           |   rekap_bulanan_approval     |
                         | PK id                   |           +------------------------------+
                         | FK pemesanan_id          |           | PK id                        |
                         |    alasan (text)         |           | FK rekap_bulanan_id          |
                         |    solusi (text)         |           |    role ENUM(                |
                         |    file (varchar255) NULL|           |      pembina,ppk,kpa)        |
                         |    status ENUM(          |           | FK user_id                   |
                         |      diajukan,           |           |    signed_at (datetime)      |
                         |      disetujui,          |           |    catatan (text) NULL       |
                         |      ditolak)            |           |    created_at, updated_at    |
                         |    created_at, updated_at|           +------------------------------+
                         |    deleted_at            |
                         +-------------------------+

+---------------------------------------------------------------------+
|                     PAYMENT FLOW TABLES                             |
+---------------------------------------------------------------------+

+----------------------+      +---------------------------------------------------+
|    senat_accounts    |      |              pengajuan_pembayaran                  |
+----------------------+      +---------------------------------------------------+
| PK id                |      | PK id                                             |
|    nama_akun         |      |    nomor_pengajuan (varchar50) UNIQUE             |
|    bank              |      |    periode_bulan (tinyint)                        |
|    nomor_rekening    |      |    periode_tahun (year)                           |
|    UNIQUE            |      |    total_taruna (int)                             |
|    nama_pemilik      |      |    total_porsi (int)                              |
|    is_aktif (bool)   |      |    total_nilai DECIMAL(15,2)                      |
|    keterangan (text) |      |    status ENUM(draft,diproses_ppk,               |
|    created_at        |      |      disetujui_kpa,permohonan_kppn,sp2d,         |
|    updated_at        |      |      transfer_kppn,debit_bank,                   |
|    deleted_at        |      |      transfer_penyedia,konfirmasi_penyedia,      |
+----------------------+      |      lpj_ppk,lpj_kpa,selesai)                    |
         ^                    |    nomor_sp2d (varchar50) NULL                    |
         | FK                 |    tanggal_sp2d (date) NULL                       |
         |                    |    invoice_penyedia (varchar255) NULL             |
+--------------------------+  |    bukti_transfer_kppn (varchar255) NULL          |
|  pemblokiran_uang_makan  |  |    bukti_debit_bank (varchar255) NULL             |
+--------------------------+  |    bukti_transfer_penyedia (varchar255) NULL      |
| PK id                    |  |    created_at, updated_at, deleted_at             |
|    periode_bulan         |  +---------------------------------------------------+
|    periode_tahun         |                      | 1
| FK taruna_id             |                      v M
|    status ENUM(          |  +---------------------------------------------------+
|      diusulkan,          |  |              workflow_pembayaran                   |
|      diblokir,           |  +---------------------------------------------------+
|      didebit)            |  | PK id                                             |
|    nomor_surat           |  | FK pengajuan_id                                   |
|    bukti_debit NULL      |  | FK user_id                                        |
| FK target_rekening_id    |  |    status_dari (varchar50)                        |
|    (senat_accounts)      |  |    status_ke (varchar50)                          |
|    created_at            |  |    aksi (varchar100)                              |
|    updated_at            |  |    catatan (text) NULL                            |
|    deleted_at            |  |    created_at (append-only, NO updated_at)        |
+--------------------------+  +---------------------------------------------------+

+--------------------------------------+    +--------------------------------------+
|           pagu_anggaran              |    |           laporan_montev             |
+--------------------------------------+    +--------------------------------------+
| PK id                                |    | PK id                               |
|    tahun (year)                      |    |    periode_bulan (tinyint)          |
|    akun_belanja (varchar20)          |    |    periode_tahun (year)             |
|    nilai_pagu DECIMAL(15,2)          |    |    menu_dievaluasi (text)           |
|    keterangan (text) NULL            |    |    nilai_gizi_rata DECIMAL(5,2) NULL|
|    created_at, updated_at            |    |    catatan_prosedur (text) NULL     |
|    deleted_at                        |    |    hasil_evaluasi (text)            |
| UNIQUE: (tahun, akun_belanja)        |    | FK user_id                          |
+--------------------------------------+    |    created_at, updated_at           |
                                            |    deleted_at                        |
                                            +--------------------------------------+

+------------------------------------------------------------------------------------------------------+
|                    jadwal_menu                                                                       |
+------------------------------------------------------------------------------------------------------+
| PK id | tanggal (date) | jenis_makan ENUM(sarapan,makan_siang,makan_malam) | menu (text)            |
| nilai_gizi (text) NULL | porsi_per_taruna TINYINT DEFAULT 1                                          |
| created_at | updated_at | deleted_at                                                                 |
| UNIQUE: (tanggal, jenis_makan)                                                                       |
+------------------------------------------------------------------------------------------------------+
```

---

## B. LARAVEL FOLDER STRUCTURE

```
simantap/
+-- app/
|   +-- Console/
|   |   +-- Commands/
|   |       +-- HitungRekapBulanan.php
|   |       +-- SyncStatusTaruna.php
|   +-- Exceptions/
|   |   +-- Handler.php
|   +-- Http/
|   |   +-- Controllers/
|   |   |   +-- Auth/
|   |   |   +-- Api/
|   |   |   |   +-- TarunaController.php
|   |   |   |   +-- PemesananHarianController.php
|   |   |   |   +-- PenerimaanMakanController.php
|   |   |   |   +-- RekapBulananController.php
|   |   |   |   +-- PengajuanPembayaranController.php
|   |   |   |   +-- PaguAnggaranController.php
|   |   |   |   +-- LaporanController.php
|   |   |   +-- Web/
|   |   |       +-- DashboardController.php
|   |   |       +-- TarunaController.php
|   |   |       +-- KontrakMakanController.php
|   |   |       +-- PemesananHarianController.php
|   |   |       +-- PenerimaanMakanController.php
|   |   |       +-- RekapBulananController.php
|   |   |       +-- PengajuanPembayaranController.php
|   |   |       +-- PaguAnggaranController.php
|   |   |       +-- LaporanMonteVController.php
|   |   +-- Middleware/
|   |   |   +-- CheckRole.php
|   |   |   +-- EnsureUserIsActive.php
|   |   +-- Requests/
|   |       +-- StoreTarunaRequest.php
|   |       +-- UpdateTarunaRequest.php
|   |       +-- StorePemesananRequest.php
|   |       +-- StorePenerimaanRequest.php
|   +-- Models/
|   |   +-- User.php
|   |   +-- Taruna.php
|   |   +-- RekeningTaruna.php
|   |   +-- SenatAccount.php
|   |   +-- PenyediaMakan.php
|   |   +-- KontrakMakan.php
|   |   +-- SkPenerima.php
|   |   +-- JadwalMenu.php
|   |   +-- PemesananHarian.php
|   |   +-- BeritaAcaraPerubahan.php
|   |   +-- PenerimaanMakan.php
|   |   +-- MonitoringFoto.php
|   |   +-- RekapBulanan.php
|   |   +-- RekapBulananApproval.php
|   |   +-- PemblokiranUangMakan.php
|   |   +-- PengajuanPembayaran.php
|   |   +-- WorkflowPembayaran.php
|   |   +-- PaguAnggaran.php
|   |   +-- LaporanMontev.php
|   +-- Policies/
|   |   +-- TarunaPolicy.php
|   |   +-- PemesananHarianPolicy.php
|   |   +-- PengajuanPembayaranPolicy.php
|   +-- Services/
|   |   +-- RekapBulananService.php
|   |   +-- PembayaranWorkflowService.php
|   |   +-- EligibilitasService.php
|   |   +-- PaguAnggaranService.php
|   +-- Observers/
|       +-- TarunaObserver.php
|       +-- PemesananHarianObserver.php
+-- config/
|   +-- app.php
|   +-- simantap.php
+-- database/
|   +-- migrations/
|   |   +-- 0001_01_01_000000_create_users_table.php
|   |   +-- 0001_01_01_000001_create_cache_table.php
|   |   +-- 0001_01_01_000002_create_jobs_table.php
|   |   +-- 2025_01_01_000001_create_taruna_table.php
|   |   +-- 2025_01_01_000002_create_rekening_taruna_table.php
|   |   +-- 2025_01_01_000003_create_senat_accounts_table.php
|   |   +-- 2025_01_01_000004_create_penyedia_makan_table.php
|   |   +-- 2025_01_01_000005_create_kontrak_makan_table.php
|   |   +-- 2025_01_01_000006_create_sk_penerima_table.php
|   |   +-- 2025_01_01_000007_create_jadwal_menu_table.php
|   |   +-- 2025_01_01_000008_create_pemesanan_harian_table.php
|   |   +-- 2025_01_01_000009_create_berita_acara_perubahan_table.php
|   |   +-- 2025_01_01_000010_create_penerimaan_makan_table.php
|   |   +-- 2025_01_01_000011_create_monitoring_foto_table.php
|   |   +-- 2025_01_01_000012_create_rekap_bulanan_table.php
|   |   +-- 2025_01_01_000013_create_rekap_bulanan_approval_table.php
|   |   +-- 2025_01_01_000014_create_pemblokiran_uang_makan_table.php
|   |   +-- 2025_01_01_000015_create_pengajuan_pembayaran_table.php
|   |   +-- 2025_01_01_000016_create_workflow_pembayaran_table.php
|   |   +-- 2025_01_01_000017_create_pagu_anggaran_table.php
|   |   +-- 2025_01_01_000018_create_laporan_montev_table.php
|   +-- seeders/
|       +-- DatabaseSeeder.php
|       +-- RolePermissionSeeder.php
|       +-- UserSeeder.php
|       +-- SenatAccountSeeder.php
|       +-- PaguAnggaranSeeder.php
+-- resources/
|   +-- views/
|   |   +-- layouts/
|   |   +-- dashboard/
|   |   +-- taruna/
|   |   +-- pemesanan/
|   |   +-- penerimaan/
|   |   +-- rekap/
|   |   +-- pembayaran/
|   |   +-- laporan/
|   +-- js/
+-- routes/
|   +-- web.php
|   +-- api.php
+-- tests/
    +-- Feature/
    |   +-- TarunaTest.php
    |   +-- PemesananTest.php
    |   +-- RekapBulananTest.php
    +-- Unit/
        +-- EligibilitasServiceTest.php
        +-- RekapBulananServiceTest.php
```

---

## C. ROLE x PERMISSION MATRIX

### Roles:
- **super_admin** - Akses penuh semua fitur
- **kpa** - Kuasa Pengguna Anggaran: approve final, TTD rekap, setujui pembayaran
- **ppk** - Pejabat Pembuat Komitmen: proses pembayaran, TTD rekap, input SP2D
- **pembina_karakter** - Verifikasi pemesanan harian, TTD rekap
- **senat_taruna** - Input pemesanan harian, kelola rekening Senat
- **auditor** - Read-only access semua data + audit log
- **viewer** - Read-only terbatas

| Permission                    | super_admin | kpa | ppk | pembina_karakter | senat_taruna | auditor | viewer |
|-------------------------------|:-----------:|:---:|:---:|:----------------:|:------------:|:-------:|:------:|
| taruna.view                   | v | v | v | v | v | v | v |
| taruna.create                 | v | - | - | - | - | - | - |
| taruna.edit                   | v | - | - | - | - | - | - |
| taruna.delete                 | v | - | - | - | - | - | - |
| taruna.import                 | v | - | - | - | - | - | - |
| rekening.manage               | v | - | - | - | v | - | - |
| penyedia.manage               | v | - | v | - | - | - | - |
| kontrak.view                  | v | v | v | v | v | v | v |
| kontrak.create                | v | - | v | - | - | - | - |
| kontrak.setujui               | v | v | - | - | - | - | - |
| sk_penerima.manage            | v | v | - | - | - | - | - |
| jadwal_menu.manage            | v | - | - | - | v | - | - |
| pemesanan.create              | v | - | - | - | v | - | - |
| pemesanan.verifikasi          | v | - | - | v | - | - | - |
| pemesanan.kirim               | v | - | - | - | v | - | - |
| penerimaan.input              | v | - | - | - | v | - | - |
| penerimaan.geotagging         | v | - | - | - | v | - | - |
| rekap.hitung                  | v | - | v | - | - | - | - |
| rekap.tandatangani_pembina    | v | - | - | v | - | - | - |
| rekap.tandatangani_ppk        | v | - | v | - | - | - | - |
| rekap.tandatangani_kpa        | v | v | - | - | - | - | - |
| pemblokiran.usulkan           | v | - | v | - | - | - | - |
| pemblokiran.proses            | v | v | - | - | - | - | - |
| pembayaran.create             | v | - | v | - | - | - | - |
| pembayaran.proses_ppk         | v | - | v | - | - | - | - |
| pembayaran.setujui_kpa        | v | v | - | - | - | - | - |
| pembayaran.input_sp2d         | v | - | v | - | - | - | - |
| pembayaran.konfirmasi_transfer| v | - | v | - | - | - | - |
| laporan.view                  | v | v | v | v | v | v | v |
| laporan.export                | v | v | v | - | - | v | - |
| montev.manage                 | v | v | v | v | - | - | - |
| auditlog.view                 | v | - | - | - | - | v | - |
| pagu.manage                   | v | v | - | - | - | - | - |

---

## D. STATE MACHINE DIAGRAMS

### D.1 - Daily Order Cycle (pemesanan_harian.status)

```
                +------------------------------------------+
                |         DAILY ORDER CYCLE                |
                |      (pemesanan_harian.status)           |
                +------------------------------------------+

  [senat_taruna input]
         |
         v
    +---------+
    |  draft  |<----------------------------------------------------------+
    +---------+                                                            |
         |  senat_taruna submit untuk verifikasi                          |
         v                                                                 |
+-------------------------+                                                |
| diverifikasi_pembina    |<---- pembina_karakter revisi (kembali draft)   |
+-------------------------+                                                |
         |  pembina_karakter verifikasi OK                                |
         |                                                                 |
         +---- ada perubahan taruna hadir/menu --------------------------->+
         |
         v
+----------------------+
|  dikirim_penyedia    |  [senat kirim ke penyedia]
+----------------------+
         |
         +---- penyedia minta perubahan ---> +-----------+
         |                                   | perubahan |----->+
         |                                   +-----------+      |
         |                           (BA perubahan dibuat)      |
         |<------------------------------------------------------+
         |  (setelah BA disetujui, kembali ke dikirim_penyedia)
         v
  +--------------+
  |  disajikan   |  [penyedia konfirmasi makanan tersaji]
  +--------------+
         |
         v
   +---------+
   | selesai |  [penerimaan_makan diinput, monitoring_foto diupload]
   +---------+

  Transisi:
  draft                -> diverifikasi_pembina  : Actor = senat_taruna
  diverifikasi_pembina -> draft                 : Actor = pembina_karakter (revisi)
  diverifikasi_pembina -> dikirim_penyedia      : Actor = pembina_karakter (ACC)
  dikirim_penyedia     -> perubahan             : Actor = senat_taruna / penyedia
  perubahan            -> dikirim_penyedia      : Actor = senat_taruna (BA disetujui)
  dikirim_penyedia     -> disajikan             : Actor = senat_taruna / sistem
  disajikan            -> selesai               : Actor = senat_taruna
```

### D.2 - Monthly Payment Cycle (pengajuan_pembayaran.status)

```
         +-----------------------------------------------------------+
         |           MONTHLY PAYMENT CYCLE                          |
         |         (pengajuan_pembayaran.status)                    |
         +-----------------------------------------------------------+

FASE 1 - REKAP & PERSIAPAN
----------------------------
  [rekap_bulanan.status = final]
         |
         v
    +---------+
    |  draft  |  [PPK buat pengajuan dari rekap_bulanan]
    +---------+
         |  PPK submit
         v
+------------------+
|  diproses_ppk    |  [PPK review, hitung total, cek pagu]
+------------------+
         |  PPK submit ke KPA
         v
+------------------+
|  disetujui_kpa   |  [KPA review dan setujui]
+------------------+
         |  KPA kirim ke KPPN

FASE 2 - PROSES KPPN
----------------------
         v
+------------------+
| permohonan_kppn  |  [PPK input nomor permohonan KPPN]
+------------------+
         |  KPPN terbitkan SP2D
         v
    +------+
    | sp2d |  [PPK input nomor SP2D + tanggal]
    +------+
         |  KPPN mentransfer

FASE 3 - ALIRAN DANA
----------------------
         v
+------------------+
| transfer_kppn    |  [PPK input bukti transfer KPPN ke rekening Senat]
+------------------+
         |
         v
+------------------+
|   debit_bank     |  [PPK input bukti debit rekening Senat oleh bank]
+------------------+
         |
         v
+----------------------+
| transfer_penyedia    |  [PPK input bukti transfer Senat ke Penyedia]
+----------------------+
         |

FASE 4 - KONFIRMASI & PERTANGGUNGJAWABAN
-----------------------------------------
         v
+------------------------+
|  konfirmasi_penyedia   |  [Penyedia konfirmasi terima pembayaran + invoice]
+------------------------+
         |
         v
  +--------------+
  |   lpj_ppk    |  [PPK susun LPJ]
  +--------------+
         |
         v
  +--------------+
  |   lpj_kpa    |  [KPA review dan TTD LPJ]
  +--------------+
         |
         v
   +---------+
   | selesai |  [Proses pembayaran selesai, semua dokumen tersimpan]
   +---------+

  Log: setiap transisi dicatat di workflow_pembayaran (append-only)
  Pemblokiran uang makan: diproses paralel di fase debit_bank
  Setiap transisi diaudit ke activity_log via Spatie ActivityLog
```

---

## E. BUSINESS RULES SUMMARY

1. **Porsi per hari**: Setiap taruna berhak 3 porsi/hari (sarapan + makan siang + makan malam)
2. **Eligibilitas bantuan**: Taruna dengan status cuti, pesiar, sakit_di_rumah_keluarga, atau penundaan_studi TIDAK mendapat bantuan
3. **Status dapat bantuan**: aktif dan sakit_di_kampus
4. **Alur pembayaran**: PPK -> input SP2D -> KPPN (PPSPM/Bendahara tidak aktif di alur)
5. **Rekening Senat**: Berfungsi sebagai rekening penampungan sebelum transfer ke penyedia
6. **Kontrak makan**: Pihak pertama adalah "Senat Taruna" bukan institusi
7. **Pagu anggaran**: Dipantau aktif sistem; PPK harus cek sisa pagu sebelum buat pengajuan
8. **Rekap bulanan**: Memerlukan 3 TTD berurutan: Pembina Karakter -> PPK -> KPA
9. **Geotagging**: Wajib pada input penerimaan makan (lat/long tercatat)
10. **Foto monitoring**: Maks 5 foto per sesi dengan metadata geolokasi dan timestamp

---

*Dokumen ini adalah fase desain Tahap 1 SIMANTAP. Revisi mengikuti hasil review stakeholder.*
*Generated: 2026-06-11*
