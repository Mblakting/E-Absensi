# ⚡ Quick Start - Deploy E-Presensi ke Railway

## 🎯 Langkah Cepat (5-10 menit)

### 1. Push ke GitHub ✅
```bash
# Sudah selesai! File sudah di GitHub
```

### 2. Setup Railway (2 menit)
1. Buka https://railway.app
2. Login dengan GitHub
3. New Project → Deploy from GitHub repo
4. Pilih repo "E-Presensi"
5. Pilih branch "face-recognition"

### 3. Tambah MySQL (1 menit)
1. Klik "+ New" → Database → MySQL
2. Tunggu MySQL ready

### 4. Setup Environment Variables (3 menit)
Copy-paste variables ini ke Railway:

**Basic Config:**
```
APP_NAME=E-Presensi
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:8d5TEvdcKYmK/qtdovrPPhE2yIMTwZuMY79yIG/+7F8=
```

**Database (ambil dari MySQL service):**
```
DB_CONNECTION=mysql
DB_HOST=[dari MYSQL_HOST]
DB_PORT=[dari MYSQL_PORT]
DB_DATABASE=[dari MYSQL_DATABASE]
DB_USERNAME=[dari MYSQL_USER]
DB_PASSWORD=[dari MYSQL_PASSWORD]
```

**School Config:**
```
SCHOOL_NAME=SMK Negeri 1
SCHOOL_LATITUDE=-7.671325
SCHOOL_LONGITUDE=110.589607
SCHOOL_RADIUS=500
```

### 5. Generate Domain & Deploy (1 menit)
1. Settings → Networking → Generate Domain
2. Copy URL
3. Variables → Add `APP_URL=[URL yang di-copy]`
4. Deployments → Redeploy

### 6. Tunggu Build (5-8 menit)
Monitor logs, pastikan muncul:
- ✅ `=== NIXPACKS BUILD: Setup Phase Started ===`
- ✅ `✅ nixpacks.toml found - PRIMARY strategy active`
- ✅ `✅ Semua setup selesai! Menjalankan server...`

### 7. Test Aplikasi ✅
- Admin: `https://[domain]/panel` - admin@sekolah.sch.id / password123
- Siswa: `https://[domain]/` - NISN: 0012345678 / 12345

---

## 🚨 Jika Ada Masalah

**Build hang > 10 menit?**
→ Lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

**Error 500?**
→ Check logs di Railway Deployments

**Database error?**
→ Verify DB variables dari MySQL service

---

## 📚 Dokumentasi Lengkap

- **[PANDUAN-DEPLOY.md](PANDUAN-DEPLOY.md)** - Panduan lengkap step-by-step
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Solusi masalah umum
- **[ROLLBACK-PROCEDURES.md](ROLLBACK-PROCEDURES.md)** - Cara rollback jika ada masalah

---

## ✨ Fitur Deployment

✅ Build cepat (5-8 menit)
✅ Anti-hang (pre-compiled extensions)
✅ Auto-retry jika gagal
✅ Health monitoring otomatis
✅ Rollback ready

**SELAMAT! Aplikasi kamu sudah live! 🎉**
