# SIMANTAP - Sistem Informasi Manajemen Bantuan Makan Taruna
## Entity Relationship Diagram & Database Architecture

### ENTITAS DAN RELASI

```
USERS (1) ─────────────────────────────────────────── (N) AUDIT_TRAILS
  │
  ├── (N) model_has_roles ── (1) ROLES ── (N) role_has_permissions ── (1) PERMISSIONS
  │
TARUNA (1) ─── (1) REKENING_TARUNA
  │
  ├── (N) ABSENSI_TARUNA
  │
  └── (N) REKAP_BULANAN

PENYEDIA_MAKAN (1) ─── (N) KONTRAK_MAKAN (1) ─── (N) PEMESANAN_MAKAN
                                                          │
                                                   (N) MONITORING_MAKAN
                                                          │
                                                   (N) VERIFIKASI_PEMBINA

REKAP_BULANAN (N) ─── (1) PENGAJUAN_PEMBAYARAN
  │
  └── (N) WORKFLOW_PEMBAYARAN
```

---

## STRUKTUR TABEL LENGKAP

### 1. users
| Kolom           | Tipe           | Keterangan               |
|----------------|----------------|--------------------------|
| id              | bigint PK      | Primary key              |
| name            | varchar(255)   | Nama lengkap             |
| email           | varchar(255)   | Email unik               |
| password        | varchar(255)   | Hash password            |
| nip             | varchar(30)    | NIP pegawai              |
| jabatan         | varchar(100)   | Jabatan/posisi           |
| telepon         | varchar(20)    | No. telepon              |
| avatar          | varchar(255)   | Path foto profil         |
| is_active       | boolean        | Status aktif             |
| last_login_at   | timestamp      | Login terakhir           |
| email_verified_at | timestamp    |                          |
| remember_token  | varchar(100)   |                          |
| created_at      | timestamp      |                          |
| updated_at      | timestamp      |                          |
| deleted_at      | timestamp      | Soft delete              |

### 2. taruna
| Kolom            | Tipe           | Keterangan                |
|-----------------|----------------|---------------------------|
| id               | bigint PK      |                           |
| nit              | varchar(20)    | Nomor Induk Taruna, unik  |
| nama             | varchar(255)   | Nama lengkap              |
| nik              | varchar(20)    | NIK KTP                   |
| angkatan         | year           | Tahun angkatan            |
| prodi            | varchar(100)   | Program studi             |
| kelas            | varchar(10)    | Kelas                     |
| jenis_kelamin    | enum(L,P)      | L=Laki, P=Perempuan       |
| status_taruna    | enum           | aktif/cuti/pesiar/sakit/penundaan_studi |
| foto             | varchar(255)   | Path foto taruna          |
| created_by       | bigint FK      | users.id                  |
| updated_by       | bigint FK      | users.id                  |
| created_at       | timestamp      |                           |
| updated_at       | timestamp      |                           |
| deleted_at       | timestamp      | Soft delete               |

### 3. rekening_taruna
| Kolom          | Tipe           | Keterangan              |
|---------------|----------------|-------------------------|
| id             | bigint PK      |                         |
| taruna_id      | bigint FK      | taruna.id               |
| bank           | varchar(100)   | Nama bank               |
| nomor_rekening | varchar(50)    | Unik                    |
| nama_pemilik   | varchar(255)   | Nama sesuai rekening    |
| created_by     | bigint FK      | users.id                |
| created_at     | timestamp      |                         |
| updated_at     | timestamp      |                         |
| deleted_at     | timestamp      | Soft delete             |

### 4. penyedia_makan
| Kolom          | Tipe           | Keterangan              |
|---------------|----------------|-------------------------|
| id             | bigint PK      |                         |
| nama_penyedia  | varchar(255)   |                         |
| npwp           | varchar(30)    |                         |
| alamat         | text           |                         |
| telepon        | varchar(20)    |                         |
| email          | varchar(255)   |                         |
| bank           | varchar(100)   |                         |
| rekening       | varchar(50)    |                         |
| created_by     | bigint FK      | users.id                |
| created_at     | timestamp      |                         |
| updated_at     | timestamp      |                         |
| deleted_at     | timestamp      | Soft delete             |

### 5. kontrak_makan
| Kolom            | Tipe           | Keterangan              |
|-----------------|----------------|-------------------------|
| id               | bigint PK      |                         |
| nomor_kontrak    | varchar(100)   | Unik                    |
| tanggal_kontrak  | date           |                         |
| tanggal_mulai    | date           |                         |
| tanggal_selesai  | date           |                         |
| nilai_kontrak    | decimal(15,2)  |                         |
| harga_porsi      | decimal(10,2)  |                         |
| penyedia_id      | bigint FK      | penyedia_makan.id       |
| file_kontrak     | varchar(255)   | Path PDF kontrak        |
| file_addendum    | varchar(255)   | Path PDF addendum       |
| status           | enum           | aktif/berakhir          |
| catatan          | text           |                         |
| created_by       | bigint FK      | users.id                |
| created_at       | timestamp      |                         |
| updated_at       | timestamp      |                         |
| deleted_at       | timestamp      | Soft delete             |

### 6. absensi_taruna
| Kolom          | Tipe           | Keterangan              |
|---------------|----------------|-------------------------|
| id             | bigint PK      |                         |
| taruna_id      | bigint FK      | taruna.id               |
| tanggal        | date           |                         |
| status_hadir   | enum           | hadir/cuti/pesiar/sakit/penundaan_studi |
| keterangan     | varchar(255)   | Catatan tambahan        |
| created_by     | bigint FK      | users.id                |
| updated_by     | bigint FK      | users.id                |
| created_at     | timestamp      |                         |
| updated_at     | timestamp      |                         |
| INDEX          | (taruna_id, tanggal) UNIQUE |              |

### 7. pemesanan_makan
| Kolom              | Tipe           | Keterangan              |
|-------------------|----------------|-------------------------|
| id                 | bigint PK      |                         |
| tanggal            | date           | Unik per kontrak        |
| kontrak_id         | bigint FK      | kontrak_makan.id        |
| jumlah_taruna_hadir| integer        |                         |
| jumlah_porsi       | integer        | = jumlah_taruna_hadir   |
| harga_porsi        | decimal(10,2)  | Snapshot dari kontrak   |
| nilai_makan_harian | decimal(15,2)  | = porsi × harga_porsi   |
| status             | enum           | draft/verifikasi/disetujui |
| catatan            | text           |                         |
| created_by         | bigint FK      | users.id                |
| verified_by        | bigint FK      | users.id                |
| verified_at        | timestamp      |                         |
| created_at         | timestamp      |                         |
| updated_at         | timestamp      |                         |
| deleted_at         | timestamp      | Soft delete             |

### 8. monitoring_makan
| Kolom          | Tipe           | Keterangan              |
|---------------|----------------|-------------------------|
| id             | bigint PK      |                         |
| pemesanan_id   | bigint FK      | pemesanan_makan.id      |
| tanggal        | date           |                         |
| menu           | text           | Deskripsi menu          |
| jumlah_porsi   | integer        |                         |
| latitude       | decimal(10,7)  | GPS latitude            |
| longitude      | decimal(10,7)  | GPS longitude           |
| alamat_lokasi  | varchar(255)   | Reverse geocode         |
| catatan        | text           |                         |
| created_by     | bigint FK      | users.id                |
| created_at     | timestamp      |                         |
| updated_at     | timestamp      |                         |

### 9. monitoring_foto
| Kolom             | Tipe           | Keterangan              |
|------------------|----------------|-------------------------|
| id                | bigint PK      |                         |
| monitoring_id     | bigint FK      | monitoring_makan.id     |
| file_path         | varchar(255)   |                         |
| urutan            | tinyint        | 1-5                     |
| created_at        | timestamp      |                         |
| updated_at        | timestamp      |                         |

### 10. verifikasi_pembina
| Kolom          | Tipe           | Keterangan              |
|---------------|----------------|-------------------------|
| id             | bigint PK      |                         |
| pemesanan_id   | bigint FK      | pemesanan_makan.id      |
| user_id        | bigint FK      | users.id (Pembina)      |
| aksi           | enum           | setuju/tolak/revisi     |
| catatan        | text           |                         |
| tanggal        | date           |                         |
| jam            | time           |                         |
| created_at     | timestamp      |                         |
| updated_at     | timestamp      |                         |

### 11. rekap_bulanan
| Kolom             | Tipe           | Keterangan               |
|------------------|----------------|--------------------------|
| id                | bigint PK      |                          |
| taruna_id         | bigint FK      | taruna.id                |
| periode_bulan     | tinyint        | 1-12                     |
| periode_tahun     | smallint       |                          |
| jumlah_hari_hadir | integer        |                          |
| jumlah_hak_makan  | integer        | = jumlah_hari_hadir      |
| harga_porsi       | decimal(10,2)  | Snapshot                 |
| nilai_bantuan     | decimal(15,2)  | = hari_hadir × harga     |
| kontrak_id        | bigint FK      | kontrak_makan.id         |
| status            | enum           | draft/final              |
| created_by        | bigint FK      | users.id                 |
| created_at        | timestamp      |                          |
| updated_at        | timestamp      |                          |
| INDEX             | (taruna_id, periode_bulan, periode_tahun) UNIQUE |

### 12. pengajuan_pembayaran
| Kolom              | Tipe           | Keterangan              |
|-------------------|----------------|-------------------------|
| id                 | bigint PK      |                         |
| nomor_pengajuan    | varchar(50)    | Unik, auto-generate     |
| periode_bulan      | tinyint        |                         |
| periode_tahun      | smallint       |                         |
| kontrak_id         | bigint FK      | kontrak_makan.id        |
| total_taruna       | integer        |                         |
| total_hari         | integer        |                         |
| total_nilai        | decimal(15,2)  |                         |
| status             | enum           | draft/verifikasi_ppk/persetujuan_kpa/proses_ppspm/sp2d/transfer/selesai |
| catatan            | text           |                         |
| created_by         | bigint FK      | users.id                |
| created_at         | timestamp      |                         |
| updated_at         | timestamp      |                         |
| deleted_at         | timestamp      | Soft delete             |

### 13. pengajuan_rekap (pivot)
| Kolom               | Tipe           | Keterangan              |
|--------------------|----------------|-------------------------|
| id                  | bigint PK      |                         |
| pengajuan_id        | bigint FK      | pengajuan_pembayaran.id |
| rekap_id            | bigint FK      | rekap_bulanan.id        |

### 14. workflow_pembayaran
| Kolom              | Tipe           | Keterangan              |
|-------------------|----------------|-------------------------|
| id                 | bigint PK      |                         |
| pengajuan_id       | bigint FK      | pengajuan_pembayaran.id |
| user_id            | bigint FK      | users.id                |
| status_dari        | varchar(50)    |                         |
| status_ke          | varchar(50)    |                         |
| aksi               | varchar(50)    | approve/reject/revisi   |
| catatan            | text           |                         |
| created_at         | timestamp      |                         |

### 15. audit_trails
| Kolom          | Tipe           | Keterangan              |
|---------------|----------------|-------------------------|
| id             | bigint PK      |                         |
| user_id        | bigint FK      | users.id (nullable)     |
| user_name      | varchar(255)   | Snapshot nama user      |
| model_type     | varchar(255)   | Nama class model        |
| model_id       | bigint         | ID record               |
| aksi           | varchar(50)    | created/updated/deleted/etc |
| data_lama      | json           |                         |
| data_baru      | json           |                         |
| ip_address     | varchar(45)    |                         |
| user_agent     | text           |                         |
| created_at     | timestamp      |                         |

---

## RELASI ELOQUENT

```
User          hasMany AuditTrail
User          belongsToMany Roles (via Spatie)

Taruna        hasOne  RekeningTaruna
Taruna        hasMany AbsensiTaruna
Taruna        hasMany RekapBulanan

PenyediaMakan hasMany KontrakMakan

KontrakMakan  belongsTo PenyediaMakan
KontrakMakan  hasMany   PemesananMakan
KontrakMakan  hasMany   RekapBulanan

PemesananMakan belongsTo KontrakMakan
PemesananMakan hasMany   MonitoringMakan
PemesananMakan hasMany   VerifikasiPembina

MonitoringMakan hasMany  MonitoringFoto

RekapBulanan  belongsTo Taruna
RekapBulanan  belongsTo KontrakMakan
RekapBulanan  belongsToMany PengajuanPembayaran

PengajuanPembayaran hasMany WorkflowPembayaran
PengajuanPembayaran belongsToMany RekapBulanan
```

---

## WORKFLOW STATUS

### Pemesanan Makan
```
[DRAFT] → [VERIFIKASI] → [DISETUJUI]
            ↓ (tolak)
          [REVISI] → [VERIFIKASI]
```

### Pengajuan Pembayaran (LS)
```
[DRAFT]
  ↓ submit oleh Bendahara
[VERIFIKASI_PPK]
  ↓ setuju oleh PPK
[PERSETUJUAN_KPA]
  ↓ setuju oleh KPA
[PROSES_PPSPM]
  ↓ diproses oleh PPSPM
[SP2D]
  ↓ SP2D terbit
[TRANSFER]
  ↓ transfer dilakukan
[SELESAI]

Setiap step: reject → kembali ke DRAFT dengan catatan
```

### Role dan Akses
```
Super Admin   → Full access semua menu
KPA           → Approval pengajuan, lihat laporan
PPK           → Verifikasi pengajuan, lihat laporan
Pembina Karakter → Verifikasi pemesanan, monitoring
Senat Taruna  → Input absensi, pemesanan
Bendahara     → Rekap, pengajuan pembayaran
PPSPM         → Proses SP2D
Auditor       → Read-only semua data + audit trail
Viewer        → Read-only dashboard + laporan
```
