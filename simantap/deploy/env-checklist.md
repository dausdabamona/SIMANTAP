# Checklist .env untuk VPS Jagoan Hosting — SIMANTAP

Salin `.env.example` ke `.env` lalu isi semua nilai di bawah ini sebelum deploy.

## Wajib diisi

```env
APP_NAME=SIMANTAP
APP_ENV=production
APP_KEY=                        # php artisan key:generate
APP_DEBUG=false                 # WAJIB false di production
APP_URL=https://simantap.poltekkpsorong.ac.id
APP_TIMEZONE=Asia/Makassar

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simantap_prod
DB_USERNAME=simantap_user
DB_PASSWORD=                    # Ganti dengan password kuat

# Session — pakai database untuk multi-server
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true            # Enkripsi session di production

# Queue — database (tanpa Redis)
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=file                # Atau database jika load tinggi

# Filesystem
FILESYSTEM_DISK=local

# Mail (untuk notifikasi — opsional)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@poltekkpsorong.ac.id
MAIL_FROM_NAME="SIMANTAP Poltek KP Sorong"
```

## Checklist setelah .env diisi

- [ ] `php artisan key:generate` sudah dijalankan
- [ ] Database sudah dibuat dan user memiliki hak CREATE/ALTER/DROP
- [ ] `php artisan migrate --force` berhasil
- [ ] `php artisan db:seed --force` berhasil (isi roles & permissions)
- [ ] `php artisan storage:link` berhasil
- [ ] File permission: `storage/` dan `bootstrap/cache/` writeable oleh www-data
- [ ] Nginx config sudah diaktifkan dan nginx reload
- [ ] Supervisor worker sudah running (`supervisorctl status`)
- [ ] SSL sertifikat terpasang (Let's Encrypt atau SSL dari hosting)
- [ ] `APP_DEBUG=false` dipastikan
- [ ] Cron job artisan schedule terpasang:
  ```
  * * * * * www-data php /var/www/simantap/artisan schedule:run >> /dev/null 2>&1
  ```

## Struktur folder VPS yang direkomendasikan

```
/var/www/simantap/          ← root aplikasi
/var/log/nginx/             ← log nginx
/var/log/supervisor/        ← log queue worker
/etc/nginx/sites-available/simantap
/etc/supervisor/conf.d/simantap-worker.conf
```
