#!/bin/bash
# deploy.sh — Script deploy SIMANTAP ke Jagoan Hosting VPS
# Jalankan dari: /var/www/simantap/
# Usage: bash deploy/deploy.sh

set -e  # Hentikan jika ada error

APP_DIR="/var/www/simantap"
PHP="php8.2"
COMPOSER="/usr/local/bin/composer"

echo "======================================"
echo "  SIMANTAP Deploy Script"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "======================================"

# 1. Masuk maintenance mode
echo "[1/10] Aktifkan maintenance mode..."
$PHP artisan down --retry=60

# 2. Pull kode terbaru
echo "[2/10] Pull kode terbaru dari Git..."
git pull origin main

# 3. Install/update dependencies
echo "[3/10] Install Composer dependencies..."
$COMPOSER install --optimize-autoloader --no-dev --no-interaction

# 4. Jalankan migration
echo "[4/10] Jalankan database migration..."
$PHP artisan migrate --force

# 5. Clear & cache config
echo "[5/10] Clear dan rebuild cache..."
$PHP artisan config:clear
$PHP artisan config:cache
$PHP artisan route:clear
$PHP artisan route:cache
$PHP artisan view:clear
$PHP artisan view:cache

# 6. Storage link
echo "[6/10] Buat storage symlink..."
$PHP artisan storage:link

# 7. Set permission
echo "[7/10] Set file permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR/storage
chmod -R 755 $APP_DIR/bootstrap/cache

# 8. Restart queue worker
echo "[8/10] Restart queue worker..."
$PHP artisan queue:restart
supervisorctl restart simantap-worker:*

# 9. Clear stale sessions (opsional)
echo "[9/10] Clear expired sessions..."
$PHP artisan session:clear 2>/dev/null || true

# 10. Nonaktifkan maintenance mode
echo "[10/10] Nonaktifkan maintenance mode..."
$PHP artisan up

echo ""
echo "======================================"
echo "  Deploy selesai!"
echo "======================================"
