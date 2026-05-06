# 🔧 Troubleshooting Guide - E-Presensi Railway Deployment

## Masalah Umum dan Solusinya

### 1. Build Hang/Timeout saat Deploy

**Gejala:**
- Build process terhenti di "Installing PHP extensions"
- Tidak ada progress selama > 10 menit
- Railway menampilkan timeout error

**Penyebab:**
- Kompilasi PHP extension (PDO, PDO MySQL) hang
- Resource Railway terbatas

**Solusi:**
✅ **Sudah diperbaiki!** Aplikasi sekarang menggunakan nixpacks dengan pre-compiled extensions.

**Jika masih terjadi:**
1. Pastikan file `nixpacks.toml` ada di root project
2. Pastikan TIDAK ada file `Dockerfile` di root (harus `Dockerfile.backup`)
3. Check `.railwayignore` berisi `Dockerfile`
4. Redeploy dari Railway dashboard

### 2. Error: "No application encryption key"

**Gejala:**
```
RuntimeException: No application encryption key has been specified.
```

**Solusi:**
1. Buka Railway dashboard → Service E-Presensi → Variables
2. Tambahkan variable: `APP_KEY` = `base64:8d5TEvdcKYmK/qtdovrPPhE2yIMTwZuMY79yIG/+7F8=`
3. Redeploy

### 3. Error: "SQLSTATE: Connection refused"

**Gejala:**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solusi:**
1. Pastikan MySQL service sudah dibuat di Railway
2. Check variables database sudah benar:
   - `DB_HOST` → dari `MYSQL_HOST`
   - `DB_PORT` → dari `MYSQL_PORT`
   - `DB_DATABASE` → dari `MYSQL_DATABASE`
   - `DB_USERNAME` → dari `MYSQL_USER`
   - `DB_PASSWORD` → dari `MYSQL_PASSWORD`
3. Redeploy

### 4. Build Berhasil tapi Aplikasi Error 500

**Gejala:**
- Build selesai dengan status "Active"
- Tapi saat dibuka muncul error 500

**Solusi:**
1. Check logs di Railway: Deployments → klik deployment → View Logs
2. Cari error message spesifik
3. Kemungkinan penyebab:
   - Migration gagal → Check DB connection
   - Permission error → Sudah diatasi di `railway-start.sh`
   - Missing environment variable → Check semua variable sudah diisi

### 5. Foto/Gambar Tidak Tampil

**Gejala:**
- Aplikasi jalan normal
- Tapi foto profil/presensi tidak muncul

**Solusi:**
1. Ini normal untuk foto yang diupload sebelum deploy
2. Railway menggunakan ephemeral storage
3. Foto baru yang diupload setelah deploy akan tampil normal
4. Untuk production, gunakan cloud storage (S3, Cloudinary, dll)

### 6. Build Terlalu Lama (> 10 menit)

**Gejala:**
- Build tidak hang, tapi sangat lambat
- Melebihi 10 menit

**Diagnosis:**
Check di logs bagian mana yang lambat:

**Jika lambat di "Installing Node.js dependencies":**
```bash
# Pastikan package-lock.json ada dan valid
npm ci --no-audit --no-fund
```

**Jika lambat di "Installing PHP dependencies":**
```bash
# Pastikan composer.lock ada dan valid
composer install --no-dev --optimize-autoloader
```

**Solusi:**
1. Commit `package-lock.json` dan `composer.lock` ke Git
2. Pastikan tidak ada dependency yang bermasalah
3. Check Railway status page untuk masalah platform

### 7. Migration Error

**Gejala:**
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solusi:**
1. Database sudah ada isinya dari deploy sebelumnya
2. Opsi 1: Drop database dan buat baru (DATA HILANG!)
3. Opsi 2: Skip migration yang sudah jalan:
   - Edit `railway-start.sh`
   - Ubah `php artisan migrate --force` jadi `php artisan migrate --force || true`

### 8. Seeding Error

**Gejala:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
```

**Solusi:**
1. Data seed sudah ada dari deploy sebelumnya
2. Edit `railway-start.sh`:
   ```bash
   # Ubah baris seeding jadi:
   php artisan db:seed --force 2>/dev/null || true
   ```

### 9. Deployment Strategy Conflict

**Gejala:**
- Build menggunakan Docker padahal seharusnya nixpacks
- Build hang seperti masalah lama

**Diagnosis:**
Check di build logs baris pertama:
- ✅ Benar: `=== NIXPACKS BUILD: Setup Phase Started ===`
- ❌ Salah: `=== BUILD MONITORING: PHP Extensions Stage Started ===`

**Solusi:**
1. Pastikan `nixpacks.toml` ada di root
2. Hapus/rename `Dockerfile` jadi `Dockerfile.backup`
3. Update `.railwayignore`:
   ```
   Dockerfile
   node_modules
   .git
   ```
4. Redeploy

### 10. Railway CLI Issues

**Gejala:**
- `railway` command not found
- Railway CLI error

**Solusi:**
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link project
railway link

# Check status
railway status
```

## Monitoring dan Debugging

### Check Build Logs
```bash
# Via Railway CLI
railway logs

# Via Dashboard
Deployments → Click deployment → View Logs
```

### Check Application Logs
```bash
# Via Railway CLI
railway logs --tail 100

# Via Dashboard
Service → Logs tab
```

### Test Deployment Locally
```bash
# Test nixpacks configuration
bash scripts/test-nixpacks.sh

# Test Docker build (fallback)
bash scripts/test-build.sh
```

### Monitor Deployment
```bash
# Monitor deployment process
bash scripts/monitor-deployment.sh --url https://your-app.railway.app
```

## Emergency Rollback

Jika deployment baru bermasalah dan perlu rollback:

### Via Railway Dashboard:
1. Deployments tab
2. Pilih deployment sebelumnya yang working
3. Klik "Redeploy"

### Via Railway CLI:
```bash
# List deployments
railway status

# Rollback to previous
railway rollback
```

## Kontak Support

Jika masalah masih berlanjut:

1. **Railway Support**: https://railway.app/help
2. **Railway Discord**: https://discord.gg/railway
3. **GitHub Issues**: Buat issue di repository dengan:
   - Screenshot error
   - Build logs
   - Environment variables (tanpa password!)

## Checklist Deployment Berhasil

✅ Build selesai dalam < 10 menit
✅ Status deployment "Active" (hijau)
✅ Aplikasi bisa diakses via URL
✅ Login admin berhasil
✅ Login siswa berhasil
✅ Database terkoneksi
✅ Foto bisa diupload (foto baru)
✅ Presensi bisa dilakukan
✅ Face recognition berfungsi

## Tips Mencegah Masalah

1. **Selalu commit lock files**: `package-lock.json`, `composer.lock`
2. **Test lokal dulu**: Gunakan script testing sebelum deploy
3. **Monitor build logs**: Jangan tinggalkan saat deploy
4. **Backup database**: Export data penting sebelum deploy besar
5. **Gunakan staging**: Test di environment terpisah dulu
6. **Document changes**: Catat setiap perubahan konfigurasi
