# Panduan Deployment SIMANTAP
## VPS Jagoan Hosting — Ubuntu 22.04 LTS (Galaxy: 4 core / 4 GB RAM / NVMe)

---

## 1. Persiapan Server (Fresh VPS)

### 1.1 Update sistem
```bash
apt update && apt upgrade -y
apt install -y curl wget git unzip
```

### 1.2 Install PHP 8.2 + ekstensi Laravel
```bash
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
    php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mbstring php8.3-xml php8.3-curl \
    php8.3-gd php8.3-zip php8.3-bcmath \
    php8.3-mysql php8.3-intl php8.3-tokenizer \
    php8.3-fileinfo php8.3-dom
```

Verifikasi:
```bash
php8.3 --version
```

### 1.3 Install MySQL 8
```bash
apt install -y mysql-server
mysql_secure_installation
```

Buat database dan user:
```sql
mysql -u root -p
CREATE DATABASE simantap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'simantap_user'@'localhost' IDENTIFIED BY 'password_kuat_di_sini';
GRANT ALL PRIVILEGES ON simantap.* TO 'simantap_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 1.4 Install Composer
```bash
curl -sS https://getcomposer.org/installer | php8.3
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
composer --version
```

### 1.5 Install Nginx
```bash
apt install -y nginx
systemctl enable nginx
systemctl start nginx
```

### 1.6 Install Supervisor
```bash
apt install -y supervisor
systemctl enable supervisor
systemctl start supervisor
```

---

## 2. Deploy Aplikasi

### 2.1 Clone repository
```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/dausdabamona/SIMANTAP.git simantap
cd simantap/simantap
```

### 2.2 Install dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 2.3 Konfigurasi environment
```bash
cp .env.example .env
nano .env
```

Sesuaikan nilai berikut:
- `APP_URL` → domain production Anda
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` → sesuai langkah 1.3
- `MAIL_*` → SMTP provider pilihan Anda

### 2.4 Generate application key
```bash
php8.3 artisan key:generate
```

### 2.5 Jalankan migrasi + seeder
```bash
php8.3 artisan migrate --force
php8.3 artisan db:seed --force
```

### 2.6 Buat symlink storage
```bash
php8.3 artisan storage:link
```

### 2.7 Set permission
```bash
chown -R www-data:www-data /var/www/simantap
chmod -R 755 /var/www/simantap
chmod -R 775 /var/www/simantap/simantap/storage
chmod -R 775 /var/www/simantap/simantap/bootstrap/cache
```

### 2.8 Cache konfigurasi
```bash
cd /var/www/simantap/simantap
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
```

---

## 3. Konfigurasi Nginx

### 3.1 Salin konfigurasi
```bash
cp /var/www/simantap/deploy/nginx-simantap.conf /etc/nginx/sites-available/simantap
```

Edit domain:
```bash
nano /etc/nginx/sites-available/simantap
# Ganti semua "domain-anda.com" dengan domain Anda
```

### 3.2 Aktifkan site
```bash
ln -s /etc/nginx/sites-available/simantap /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 4. SSL dengan Let's Encrypt (Certbot)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

Certbot akan otomatis mengisi blok SSL di konfigurasi Nginx. Auto-renew sudah aktif via systemd timer.

---

## 5. Konfigurasi Supervisor (Queue Worker)

### 5.1 Salin konfigurasi
```bash
cp /var/www/simantap/deploy/simantap-worker.conf /etc/supervisor/conf.d/simantap-worker.conf
```

### 5.2 Aktifkan worker
```bash
supervisorctl reread
supervisorctl update
supervisorctl start simantap-worker:*
supervisorctl status
```

Output yang diharapkan:
```
simantap-worker:simantap-worker_00   RUNNING   pid 1234, uptime 0:00:05
simantap-worker:simantap-worker_01   RUNNING   pid 1235, uptime 0:00:05
```

---

## 6. Deploy Selanjutnya (Update Kode)

Setiap kali ada update kode baru, jalankan dari root project:

```bash
cd /var/www/simantap
bash deploy.sh
```

Script `deploy.sh` akan:
1. `git pull` kode terbaru
2. `composer install --no-dev` (production)
3. Jalankan migrasi
4. Symlink storage
5. Cache ulang config, route, view
6. Restart queue workers
7. Set permission
8. Reload supervisor

---

## 7. Checklist Verifikasi Akhir (Go-Live)

Jalankan setiap poin sebelum meluncurkan ke pengguna:

- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_ENV=production` di `.env`
- [ ] `APP_KEY` sudah di-generate (`php8.3 artisan key:generate`)
- [ ] SSL aktif dan redirect HTTP → HTTPS berjalan
- [ ] Login berhasil, redirect ke dashboard
- [ ] Upload foto monitoring (test file 5MB) — cek `client_max_body_size`
- [ ] Generate PDF pemesanan, rekap, pembayaran
- [ ] Queue worker berjalan: `supervisorctl status`
- [ ] Storage symlink: akses `https://domain.com/storage/` harus 200
- [ ] Log tidak ada error: `tail -f /var/log/nginx/simantap-error.log`
- [ ] Log Laravel bersih: `tail -f /var/www/simantap/simantap/storage/logs/laravel.log`
- [ ] Timezone WIT benar: `php8.3 artisan tinker --execute="echo now()->format('Y-m-d H:i T');"`
- [ ] Import Excel Taruna berfungsi
- [ ] Cek semua role (super_admin, ppk, kpa, senat_taruna, pembina_karakter) bisa login

---

## 8. Maintenance

### Cek log aplikasi
```bash
tail -f /var/www/simantap/simantap/storage/logs/laravel.log
```

### Cek log queue worker
```bash
tail -f /var/log/supervisor/simantap-worker.log
```

### Mode maintenance
```bash
# Aktifkan
php8.3 artisan down --render="errors.503" --secret="token-rahasia"

# Nonaktifkan
php8.3 artisan up
```

### Backup database
```bash
mysqldump -u simantap_user -p simantap | gzip > /backup/simantap_$(date +%Y%m%d).sql.gz
```

---

## Referensi Cepat

| Layanan | Perintah |
|---|---|
| Nginx status | `systemctl status nginx` |
| PHP-FPM status | `systemctl status php8.3-fpm` |
| Supervisor status | `supervisorctl status` |
| MySQL status | `systemctl status mysql` |
| Restart semua | `systemctl restart nginx php8.3-fpm && supervisorctl restart all` |
| Log Nginx | `/var/log/nginx/simantap-error.log` |
| Log Laravel | `/var/www/simantap/simantap/storage/logs/laravel.log` |
| Log Worker | `/var/log/supervisor/simantap-worker.log` |
