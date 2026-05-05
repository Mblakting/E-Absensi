#!/bin/bash
set -e

echo "================================================"
echo "   E-Presensi - Railway Startup Script"
echo "================================================"

PORT=${PORT:-8080}

# 1. Clear semua cache lama & cache ulang
echo ""
echo "[1/5] Clear & rebuild cache..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "      Cache selesai!"

# 2. Jalankan migrasi database
echo ""
echo "[2/5] Menjalankan migrasi database..."
php artisan migrate --force
echo "      Migrasi selesai!"

# 3. Seed data awal
echo ""
echo "[3/5] Mengisi data awal..."
php artisan db:seed --force
echo "      Seeding selesai!"

# 4. Buat symlink storage
echo ""
echo "[4/5] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true
echo "      Storage link selesai!"

# 5. Set permission
echo ""
echo "[5/5] Set permission folder..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "      Permission selesai!"

echo ""
echo "================================================"
echo "   Semua setup selesai! Menjalankan server..."
echo "   Port: $PORT"
echo "================================================"

# Jalankan server
exec php artisan serve --host=0.0.0.0 --port=$PORT
