#!/bin/bash
set -e

echo "================================================"
echo "   E-Presensi - Railway Startup Script"
echo "================================================"

PORT=${PORT:-8080}

# 1. Regenerasi autoloader Composer
echo ""
echo "[1/6] Regenerasi autoloader Composer..."
composer dump-autoload --optimize
echo "      Autoloader selesai!"

# 2. Hapus SEMUA cache lama (penting!)
echo ""
echo "[2/6] Membersihkan cache lama..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan clear-compiled 2>/dev/null || true
echo "      Cache bersih!"

# 3. Jalankan migrasi database
echo ""
echo "[3/6] Menjalankan migrasi database..."
php artisan migrate --force
echo "      Migrasi selesai!"

# 4. Seed data awal
echo ""
echo "[4/6] Mengisi data awal..."
php artisan db:seed --force
echo "      Seeding selesai!"

# 5. Buat symlink storage
echo ""
echo "[5/6] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true
echo "      Storage link selesai!"

# 6. Set permission
echo ""
echo "[6/6] Set permission folder..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "      Permission selesai!"

echo ""
echo "================================================"
echo "   Semua setup selesai! Menjalankan server..."
echo "   Port: $PORT"
echo "================================================"

# Jalankan server TANPA cache (baca langsung dari file)
exec php artisan serve --host=0.0.0.0 --port=$PORT
