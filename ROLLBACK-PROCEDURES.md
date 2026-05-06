# 🔄 Rollback Procedures - E-Presensi Deployment

## Overview

Dokumen ini menjelaskan prosedur rollback untuk mengembalikan deployment ke versi sebelumnya jika terjadi masalah kritis.

## Kapan Perlu Rollback?

Lakukan rollback jika:
- ❌ Aplikasi tidak bisa diakses setelah deployment
- ❌ Error 500 yang tidak bisa diperbaiki cepat
- ❌ Database corruption atau data loss
- ❌ Build hang berulang kali
- ❌ Performance degradation signifikan
- ❌ Security vulnerability terdeteksi

## Jenis Rollback

### 1. Quick Rollback (Railway Dashboard)
**Waktu**: 2-5 menit
**Untuk**: Masalah deployment terbaru

### 2. Configuration Rollback
**Waktu**: 5-10 menit
**Untuk**: Masalah konfigurasi (nixpacks, environment variables)

### 3. Code Rollback (Git)
**Waktu**: 10-15 menit
**Untuk**: Masalah di kode aplikasi

### 4. Full Rollback (Database + Code)
**Waktu**: 15-30 menit
**Untuk**: Masalah kritis dengan data corruption

---

## Prosedur 1: Quick Rollback via Railway Dashboard

### Langkah-langkah:

1. **Login ke Railway Dashboard**
   - Buka https://railway.app
   - Login dengan akun GitHub

2. **Pilih Project**
   - Klik project E-Presensi
   - Klik service yang bermasalah

3. **Buka Deployments Tab**
   - Klik tab "Deployments" di menu atas
   - Lihat list semua deployment

4. **Identifikasi Deployment yang Working**
   - Cari deployment sebelumnya dengan status "Success" (hijau)
   - Check timestamp dan commit message
   - Pastikan itu deployment yang stabil

5. **Redeploy Versi Lama**
   - Klik deployment yang working
   - Klik tombol "Redeploy"
   - Konfirmasi dengan klik "Deploy"

6. **Monitor Rollback**
   - Tunggu proses redeploy selesai (5-8 menit)
   - Check logs untuk memastikan tidak ada error
   - Test aplikasi setelah deployment selesai

7. **Verifikasi**
   - Buka URL aplikasi
   - Test login admin dan siswa
   - Check fungsi utama (presensi, face recognition)

### Rollback via Railway CLI:

```bash
# Login ke Railway
railway login

# Link ke project
railway link

# List deployments
railway status

# Rollback ke deployment sebelumnya
railway rollback

# Monitor logs
railway logs --tail 100
```

---

## Prosedur 2: Configuration Rollback

### Scenario: Nixpacks Configuration Bermasalah

**Gejala:**
- Build hang setelah update nixpacks.toml
- PHP extensions tidak terload
- Build error di setup phase

**Rollback Steps:**

1. **Backup Current Configuration**
   ```bash
   cp nixpacks.toml nixpacks.toml.broken
   ```

2. **Restore Previous Configuration**
   ```bash
   # Jika ada backup di Git
   git checkout HEAD~1 nixpacks.toml
   
   # Atau restore dari commit tertentu
   git checkout <commit-hash> nixpacks.toml
   ```

3. **Commit dan Push**
   ```bash
   git add nixpacks.toml
   git commit -m "Rollback: Restore working nixpacks configuration"
   git push origin main
   ```

4. **Railway Auto-Deploy**
   - Railway akan otomatis detect push
   - Monitor build logs
   - Verify deployment success

### Scenario: Environment Variables Bermasalah

**Rollback Steps:**

1. **Document Current Variables**
   - Screenshot semua environment variables di Railway
   - Atau export via Railway CLI:
   ```bash
   railway variables > variables-backup.txt
   ```

2. **Restore Previous Variables**
   - Buka Railway Dashboard → Service → Variables
   - Update variable yang bermasalah ke nilai sebelumnya
   - Atau restore dari backup:
   ```bash
   # Edit variables-backup.txt dengan nilai yang benar
   railway variables --set-from-file variables-backup.txt
   ```

3. **Redeploy**
   - Klik "Redeploy" di Deployments tab
   - Monitor logs

---

## Prosedur 3: Code Rollback via Git

### Scenario: Bug di Kode Aplikasi

**Rollback Steps:**

1. **Identifikasi Commit yang Working**
   ```bash
   # Lihat history commit
   git log --oneline -10
   
   # Atau lihat di GitHub
   # https://github.com/Mblakting/E-Presensi/commits/main
   ```

2. **Create Rollback Branch**
   ```bash
   # Buat branch baru untuk rollback
   git checkout -b rollback-to-stable
   
   # Reset ke commit yang working
   git reset --hard <commit-hash>
   ```

3. **Push Rollback**
   ```bash
   # Force push ke branch main (HATI-HATI!)
   git push origin rollback-to-stable:main --force
   
   # Atau buat PR untuk review dulu (lebih aman)
   git push origin rollback-to-stable
   # Lalu buat Pull Request di GitHub
   ```

4. **Railway Auto-Deploy**
   - Railway detect push dan auto-deploy
   - Monitor build process
   - Verify application works

### Alternative: Revert Commit (Lebih Aman)

```bash
# Revert commit tertentu (buat commit baru)
git revert <commit-hash>

# Push revert
git push origin main

# Railway auto-deploy
```

---

## Prosedur 4: Full Rollback (Database + Code)

### ⚠️ WARNING: Prosedur ini akan menghapus data!

**Hanya gunakan jika:**
- Database corruption parah
- Data migration gagal total
- Tidak ada cara lain

### Backup Database Dulu!

```bash
# Via Railway CLI
railway run mysqldump -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > backup.sql

# Atau via Railway Dashboard
# MySQL service → Data → Export
```

### Rollback Steps:

1. **Rollback Code** (Ikuti Prosedur 3)

2. **Reset Database**
   ```bash
   # Connect ke Railway MySQL
   railway run mysql -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE
   
   # Drop all tables
   DROP DATABASE IF EXISTS $DB_DATABASE;
   CREATE DATABASE $DB_DATABASE;
   ```

3. **Restore Database dari Backup**
   ```bash
   # Restore dari backup
   railway run mysql -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < backup.sql
   ```

4. **Atau Fresh Migration**
   ```bash
   # Jika tidak ada backup, fresh migration
   railway run php artisan migrate:fresh --seed --force
   ```

5. **Redeploy**
   - Trigger redeploy di Railway
   - Monitor logs
   - Verify data integrity

---

## Prosedur 5: Deployment Strategy Rollback

### Scenario: Nixpacks Bermasalah, Kembali ke Docker

**Rollback Steps:**

1. **Disable Nixpacks**
   ```bash
   # Rename nixpacks.toml
   mv nixpacks.toml nixpacks.toml.disabled
   ```

2. **Enable Docker**
   ```bash
   # Restore Dockerfile
   mv Dockerfile.backup Dockerfile
   ```

3. **Update .railwayignore**
   ```bash
   # Remove Dockerfile from .railwayignore
   # Edit .railwayignore dan hapus baris "Dockerfile"
   ```

4. **Commit dan Push**
   ```bash
   git add nixpacks.toml.disabled Dockerfile .railwayignore
   git commit -m "Rollback: Switch from nixpacks to Docker"
   git push origin main
   ```

5. **Monitor Docker Build**
   - Railway akan gunakan Docker
   - Build akan lebih lama (15-20 menit)
   - Tapi lebih stabil dengan timeout protection

---

## Post-Rollback Checklist

Setelah rollback, verify:

- [ ] Aplikasi bisa diakses via URL
- [ ] Login admin berhasil
- [ ] Login siswa berhasil
- [ ] Database connection OK
- [ ] Presensi bisa dilakukan
- [ ] Face recognition berfungsi
- [ ] Foto bisa diupload
- [ ] No error di logs
- [ ] Performance normal

---

## Automated Rollback Triggers

### Setup Automatic Rollback (Advanced)

Buat webhook untuk auto-rollback jika health check fail:

```bash
# scripts/auto-rollback.sh
#!/bin/bash

HEALTH_URL="https://your-app.railway.app"
MAX_FAILURES=3
FAILURE_COUNT=0

while true; do
    if ! curl -f -s "$HEALTH_URL" > /dev/null; then
        FAILURE_COUNT=$((FAILURE_COUNT + 1))
        echo "Health check failed: $FAILURE_COUNT/$MAX_FAILURES"
        
        if [ $FAILURE_COUNT -ge $MAX_FAILURES ]; then
            echo "Triggering automatic rollback..."
            railway rollback
            break
        fi
    else
        FAILURE_COUNT=0
    fi
    
    sleep 60
done
```

---

## Prevention Best Practices

Untuk menghindari perlu rollback:

1. **Test Lokal Dulu**
   ```bash
   bash scripts/test-build.sh
   bash scripts/test-nixpacks.sh
   ```

2. **Staging Environment**
   - Deploy ke staging dulu
   - Test thoroughly
   - Baru deploy ke production

3. **Gradual Rollout**
   - Deploy di off-peak hours
   - Monitor closely
   - Ready untuk rollback cepat

4. **Backup Regular**
   - Backup database sebelum deploy besar
   - Backup configuration files
   - Document semua changes

5. **Version Tagging**
   ```bash
   # Tag stable versions
   git tag -a v1.0.0 -m "Stable release"
   git push origin v1.0.0
   ```

---

## Emergency Contacts

Jika rollback gagal atau butuh bantuan:

1. **Railway Support**: https://railway.app/help
2. **Railway Discord**: https://discord.gg/railway
3. **Team Lead**: [Contact info]
4. **Database Admin**: [Contact info]

---

## Rollback Log Template

Document setiap rollback:

```
Date: 2026-05-07
Time: 14:30 WIB
Performed by: [Name]
Reason: Build hang after nixpacks update
Rollback Type: Configuration Rollback
From Version: commit abc123
To Version: commit def456
Duration: 8 minutes
Success: Yes
Issues: None
Notes: Nixpacks configuration had syntax error in setup phase
```

---

## Recovery After Rollback

Setelah rollback berhasil:

1. **Investigate Root Cause**
   - Analyze logs
   - Identify what went wrong
   - Document findings

2. **Fix Issues**
   - Fix bug/configuration
   - Test thoroughly locally
   - Review with team

3. **Plan Re-deployment**
   - Schedule deployment
   - Prepare rollback plan
   - Monitor closely

4. **Update Documentation**
   - Update troubleshooting guide
   - Add lessons learned
   - Improve procedures
