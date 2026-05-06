#!/bin/bash
set -e

echo "================================================"
echo "   E-Presensi - Render Startup Script"
echo "================================================"

PORT=${PORT:-8080}

# 1. Bersihkan cache lama
echo ""
echo "[1/5] Membersihkan cache lama..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan clear-compiled 2>/dev/null || true
echo "      Cache bersih!"

# 2. Jalankan migrasi database
echo ""
echo "[2/5] Menjalankan migrasi database..."
php artisan migrate --force
echo "      Migrasi selesai!"

# 3. Seed data awal (firstOrCreate, aman dijalankan berulang)
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

exec php artisan serve --host=0.0.0.0 --port=$PORT
