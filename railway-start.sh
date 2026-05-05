#!/bin/bash
set -e

echo "================================================"
echo "   E-Presensi - Railway Startup Script"
echo "================================================"

PORT=${PORT:-8080}

# Jalankan server Laravel di background DULU
# supaya healthcheck Railway bisa lewat
echo ""
echo "Menjalankan server di port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT &
SERVER_PID=$!

# Tunggu server benar-benar siap (maks 30 detik)
echo "Menunggu server siap..."
for i in $(seq 1 30); do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:$PORT/up | grep -q "200\|302"; then
        echo "Server siap!"
        break
    fi
    sleep 1
done

# Sekarang jalankan setup (server sudah aktif, healthcheck sudah lewat)
echo ""
echo "[1/4] Menjalankan migrasi database..."
php artisan migrate --force
echo "      Migrasi selesai!"

echo ""
echo "[2/4] Mengisi data awal..."
php artisan db:seed --force
echo "      Seeding selesai!"

echo ""
echo "[3/4] Membuat storage symlink..."
php artisan storage:link --force 2>/dev/null || true
echo "      Storage link selesai!"

echo ""
echo "[4/4] Set permission folder..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo "      Permission selesai!"

echo ""
echo "================================================"
echo "   Semua setup selesai! Aplikasi berjalan."
echo "================================================"

# Tunggu server (jangan exit)
wait $SERVER_PID
