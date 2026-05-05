#!/bin/bash
set -e

echo "================================================"
echo "   E-Presensi - Railway Startup Script"
echo "================================================"

# 1. Cache config, route, view (dilakukan di runtime bukan build time)
echo ""
echo "[1/5] Caching konfigurasi..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "      Cache selesai!"

# 2. Jalankan migrasi database
echo ""
echo "[2/5] Menjalankan migrasi database..."
php artisan migrate --force
echo "      Migrasi selesai!"

# 3. Seed data awal (jurusan, siswa demo, admin)
echo ""
echo "[3/5] Mengisi data awal..."
php artisan db:seed --force
echo "      Seeding selesai!"

# 4. Buat symlink storage agar foto bisa diakses publik
echo ""
echo "[4/5] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true
echo "      Storage link selesai!"

# 5. Set permission folder storage
echo ""
echo "[5/5] Set permission folder..."
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
