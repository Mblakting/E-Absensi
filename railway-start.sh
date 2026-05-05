#!/bin/bash
set -e

echo "================================================"
echo "   E-Presensi - Railway Startup Script"
echo "================================================"

# 1. Jalankan migrasi database
echo ""
echo "[1/4] Menjalankan migrasi database..."
php artisan migrate --force
echo "      Migrasi selesai!"

# 2. Seed data awal (jurusan, siswa demo, admin)
echo ""
echo "[2/4] Mengisi data awal (jurusan, siswa, admin)..."
php artisan db:seed --force
echo "      Seeding selesai!"

# 3. Buat symlink storage agar foto bisa diakses publik
echo ""
echo "[3/4] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true
echo "      Storage link selesai!"

# 4. Set permission folder storage
echo ""
echo "[4/4] Set permission folder..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "      Permission selesai!"

echo ""
echo "================================================"
echo "   Setup selesai! Menjalankan server..."
echo "   Port: ${PORT:-8080}"
echo "================================================"
echo ""

# Jalankan server Laravel
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
