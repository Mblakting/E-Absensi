# 🚀 Panduan Deploy E-Presensi ke Railway (GRATIS)

> Estimasi waktu: **5-10 menit** (sudah dioptimasi!)  
> Tidak perlu pengalaman deploy sebelumnya. Ikuti langkah demi langkah!

## ✨ Fitur Deployment Terbaru

✅ **Build Cepat**: 5-8 menit (sebelumnya 15-20 menit)
✅ **Anti-Hang**: Menggunakan pre-compiled PHP extensions
✅ **Auto-Retry**: Otomatis retry jika ada kegagalan
✅ **Health Monitoring**: Build process monitoring otomatis
✅ **Rollback Ready**: Prosedur rollback tersedia jika diperlukan

---

## 📋 Yang Kamu Butuhkan
- Akun GitHub (sudah ada: `Mblakting/E-Presensi`)
- Email untuk daftar Railway
- Browser (Chrome/Firefox)

---

## BAGIAN 1 — Push Kode ke GitHub

> Lakukan ini di terminal/command prompt di folder proyek kamu.

### Langkah 1: Buka Terminal di folder proyek
Klik kanan di folder proyek → "Open in Terminal" atau "Git Bash Here"

### Langkah 2: Kode sudah siap di GitHub ✅
File konfigurasi deploy sudah otomatis di-push ke branch `face-recognition` di repo kamu.  
Tidak perlu melakukan apa-apa di sini. Langsung lanjut ke Bagian 2!

---

## BAGIAN 2 — Daftar & Setup Railway

### Langkah 3: Buka Railway
Buka browser, pergi ke: **https://railway.app**

### Langkah 4: Daftar akun Railway
1. Klik tombol **"Login"** di pojok kanan atas
2. Klik **"Login with GitHub"**
3. Klik **"Authorize Railway"** (izinkan Railway akses GitHub kamu)
4. Kamu akan masuk ke dashboard Railway

---

## BAGIAN 3 — Buat Project di Railway

### Langkah 5: Buat project baru
1. Di dashboard Railway, klik tombol **"New Project"** (tombol ungu)
2. Pilih **"Deploy from GitHub repo"**
3. Cari dan klik repo **"E-Presensi"** milikmu
4. Railway akan tanya branch mana — pilih **"face-recognition"** (ini branch yang paling lengkap)
5. Klik **"Deploy Now"**

> Railway akan mulai build otomatis. Ini akan **GAGAL dulu** — itu normal! Kita perlu tambahkan database dulu.

### Langkah 6: Tambahkan Database MySQL
1. Di halaman project kamu, klik tombol **"+ New"** (atau **"Add Service"**)
2. Pilih **"Database"**
3. Pilih **"MySQL"**
4. Tunggu beberapa detik sampai MySQL muncul di dashboard

---

## BAGIAN 4 — Konfigurasi Environment Variables

> Ini bagian terpenting! Jangan sampai salah.

### Langkah 7: Buka pengaturan aplikasi Laravel kamu
1. Di dashboard project, klik kotak/service yang bernama **"E-Presensi"** (bukan MySQL)
2. Klik tab **"Variables"** di menu atas

### Langkah 8: Tambahkan semua variabel berikut
Klik **"+ New Variable"** untuk setiap baris di bawah ini.  
Salin persis nama dan nilainya:

| Nama Variable | Nilai |
|---|---|
| `APP_NAME` | `E-Presensi` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | `base64:8d5TEvdcKYmK/qtdovrPPhE2yIMTwZuMY79yIG/+7F8=` |
| `APP_LOCALE` | `id` |
| `APP_FALLBACK_LOCALE` | `id` |
| `APP_FAKER_LOCALE` | `id_ID` |
| `LOG_LEVEL` | `error` |
| `SESSION_DRIVER` | `database` |
| `SESSION_LIFETIME` | `120` |
| `FILESYSTEM_DISK` | `public` |
| `QUEUE_CONNECTION` | `database` |
| `CACHE_STORE` | `database` |
| `BROADCAST_CONNECTION` | `log` |
| `MAIL_MAILER` | `log` |
| `SCHOOL_LATITUDE` | `-7.671325` |
| `SCHOOL_LONGITUDE` | `110.589607` |
| `SCHOOL_RADIUS` | `500` |
| `SCHOOL_NAME` | `SMK Negeri 1` |

### Langkah 9: Tambahkan variabel database dari MySQL Railway
1. Klik kotak **"MySQL"** di dashboard project kamu
2. Klik tab **"Variables"**
3. Kamu akan melihat variabel seperti `MYSQL_HOST`, `MYSQL_USER`, dll.
4. Kembali ke service **"E-Presensi"** → tab **"Variables"**
5. Tambahkan variabel berikut (salin nilainya dari MySQL service):

| Nama Variable | Ambil dari MySQL Variables |
|---|---|
| `DB_CONNECTION` | Isi manual: `mysql` |
| `DB_HOST` | Salin nilai `MYSQL_HOST` |
| `DB_PORT` | Salin nilai `MYSQL_PORT` |
| `DB_DATABASE` | Salin nilai `MYSQL_DATABASE` |
| `DB_USERNAME` | Salin nilai `MYSQL_USER` |
| `DB_PASSWORD` | Salin nilai `MYSQL_PASSWORD` |

> 💡 **Tips:** Railway punya fitur "Reference Variable". Kamu bisa klik "+ New Variable", 
> ketik nama (misal `DB_HOST`), lalu di kolom nilai klik ikon "$" untuk pilih dari MySQL service langsung.

### Langkah 10: Tambahkan APP_URL
Setelah semua variable tersimpan:
1. Klik tab **"Settings"** di service E-Presensi
2. Scroll ke bagian **"Networking"** → **"Public Networking"**
3. Klik **"Generate Domain"**
4. Salin URL yang muncul (contoh: `https://e-presensi-production.up.railway.app`)
5. Kembali ke tab **"Variables"**
6. Tambahkan: `APP_URL` = URL yang baru kamu salin (dengan `https://`)

---

## BAGIAN 5 — Deploy!

### Langkah 11: Trigger deploy ulang
1. Klik tab **"Deployments"** di service E-Presensi
2. Klik tombol **"Redeploy"** pada deployment terakhir
3. Klik **"Deploy"** untuk konfirmasi

### Langkah 12: Pantau proses build
1. Klik pada deployment yang sedang berjalan
2. Kamu bisa lihat log build secara real-time
3. **Yang harus kamu lihat:**
   - ✅ `=== NIXPACKS BUILD: Setup Phase Started ===` (menggunakan nixpacks, bukan Docker)
   - ✅ `✅ nixpacks.toml found - PRIMARY strategy active`
   - ✅ `Pre-compiled PHP extensions (no compilation hangs)`
   - ✅ `✅ Setup phase completed`
   - ✅ `✅ Install phase completed`
   - ✅ `✅ Build phase completed`
   - ✅ `✅ Semua setup selesai! Menjalankan server...`
4. Status akan berubah menjadi **"Active"** (lingkaran hijau)

> ⏱️ Proses build sekarang hanya **5-8 menit** (sudah dioptimasi!)
> 
> ⚠️ **Jika build hang > 10 menit**: Ada masalah! Lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

---

## BAGIAN 6 — Verifikasi Aplikasi Berjalan

### Langkah 13: Buka aplikasi
1. Klik tab **"Settings"** → bagian **"Networking"**
2. Klik URL domain kamu
3. Harusnya muncul halaman login E-Presensi!

### Langkah 14: Test login
**Login Admin:**
- URL: `https://[domain-kamu]/panel`
- Email: `admin@sekolah.sch.id`
- Password: `password123`

**Login Siswa (demo):**
- URL: `https://[domain-kamu]/`
- NISN: `0012345678`
- Password: `12345`

---

## ❗ Troubleshooting (Jika Ada Error)

### Error: "No application encryption key has been specified"
→ Pastikan variable `APP_KEY` sudah diisi dengan benar di step 8.

### Error: "SQLSTATE: Connection refused"
→ Pastikan semua variable `DB_*` sudah diisi dari MySQL service (step 9).

### Build Hang/Timeout (> 10 menit)
→ **Seharusnya tidak terjadi lagi!** Jika masih terjadi:
1. Check apakah menggunakan nixpacks (lihat logs)
2. Pastikan tidak ada file `Dockerfile` di root (harus `Dockerfile.backup`)
3. Lihat panduan lengkap di [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

### Halaman muncul tapi foto tidak tampil
→ Ini normal untuk foto yang diupload sebelumnya. Foto baru yang diupload di Railway akan tampil normal.

### Error 500 setelah login
→ Buka tab "Deployments" → klik deployment aktif → lihat log untuk pesan error spesifik.

### Masalah Lainnya
→ Lihat panduan lengkap di [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
→ Prosedur rollback tersedia di [ROLLBACK-PROCEDURES.md](ROLLBACK-PROCEDURES.md)

---

## 📞 Akun Default Setelah Deploy

| Role | Username/Email | Password |
|---|---|---|
| Admin | admin@sekolah.sch.id | password123 |
| Siswa 1 | NISN: 0012345678 | 12345 |
| Siswa 2 | NISN: 0023456789 | 12345 |
| Siswa 3 | NISN: 0034567890 | 12345 |

> ⚠️ **PENTING:** Segera ganti password admin setelah pertama kali login!

---

## 🎉 Selesai!

Aplikasi E-Presensi kamu sudah live dan bisa diakses dari mana saja!

---

## 📚 Dokumentasi Tambahan

- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Panduan lengkap troubleshooting
- **[ROLLBACK-PROCEDURES.md](ROLLBACK-PROCEDURES.md)** - Prosedur rollback jika ada masalah
- **[DEPLOYMENT-STRATEGY-SELECTION.md](DEPLOYMENT-STRATEGY-SELECTION.md)** - Penjelasan teknis deployment strategy

## 🛠️ Testing Scripts (Opsional)

Jika kamu ingin test deployment secara lokal:

```bash
# Test nixpacks configuration
bash scripts/test-nixpacks.sh

# Test Docker build (fallback)
bash scripts/test-build.sh

# Monitor deployment
bash scripts/monitor-deployment.sh --url https://your-app.railway.app
```

## 🔍 Monitoring

Untuk monitoring deployment:

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login
railway login

# Link project
railway link

# View logs
railway logs --tail 100
```

## 💡 Tips Production

1. **Ganti Password Default** - Segera ganti semua password default!
2. **Setup Cloud Storage** - Untuk foto persistent, gunakan S3/Cloudinary
3. **Enable HTTPS** - Railway sudah provide HTTPS otomatis
4. **Monitor Logs** - Check logs secara berkala
5. **Backup Database** - Export database secara rutin

## 🚨 Jika Ada Masalah Serius

1. Check [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. Jika perlu rollback, ikuti [ROLLBACK-PROCEDURES.md](ROLLBACK-PROCEDURES.md)
3. Contact Railway Support: https://railway.app/help
