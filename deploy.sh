#!/usr/bin/env bash
# deploy.sh — Deploy SIMANTAP ke production
# Jalankan dari root project: bash deploy.sh
set -e

APP_DIR="/var/www/simantap"
PHP="php8.2"

echo "==> [1/9] Pull kode terbaru..."
cd "$APP_DIR"
git pull origin main

echo "==> [2/9] Install Composer dependencies (production)..."
$PHP $(which composer) install --no-dev --optimize-autoloader --no-interaction

echo "==> [3/9] Jalankan migrasi database..."
$PHP artisan migrate --force

echo "==> [4/9] Buat symlink storage..."
$PHP artisan storage:link || true   # ok jika sudah ada

echo "==> [5/9] Cache konfigurasi, route, dan view..."
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "==> [6/9] Restart queue workers..."
$PHP artisan queue:restart

echo "==> [7/9] Set permission direktori..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> [8/9] Reload supervisor workers..."
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart simantap-worker:*

echo "==> [9/9] Selesai!"
echo "    App URL: $(grep APP_URL .env | cut -d= -f2)"
