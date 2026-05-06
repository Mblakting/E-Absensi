# Dokumen Persyaratan Bugfix

## Pendahuluan

Aplikasi Laravel E-Presensi gagal deploy di platform Railway karena file package-lock.json yang hilang dan masalah konfigurasi Docker build. Deployment gagal dalam hitungan detik selama proses Docker build, sehingga aplikasi tidak dapat di-deploy dengan sukses. Hal ini mempengaruhi kemampuan untuk deploy aplikasi ke production dan membuatnya tidak dapat diakses oleh pengguna.

## Analisis Bug

### Perilaku Saat Ini (Cacat)

1.1 KETIKA Railway mencoba build Docker image MAKA build gagal dengan error "failed to calculate checksum of ref: '/package-lock.json': not found"

1.2 KETIKA Dockerfile mencoba copy package-lock.json pada build step 7/13 MAKA proses build berhenti karena file tidak ada di project root

1.3 KETIKA proses Docker build berjalan dalam waktu lama MAKA build dibatalkan dengan exit code 137 karena timeout atau keterbatasan resource

1.4 KETIKA menggunakan npm dengan package.json saja (tanpa package-lock.json) MAKA build menjadi tidak reliable dan bisa install versi dependency yang berbeda

### Perilaku yang Diharapkan (Benar)

2.1 KETIKA Railway mencoba build Docker image MAKA build HARUS berhasil tanpa error file not found

2.2 KETIKA Dockerfile memproses Node.js dependencies MAKA HARUS menangani ketiadaan package-lock.json dengan baik atau generate otomatis

2.3 KETIKA proses Docker build berjalan MAKA HARUS selesai dalam batas waktu yang wajar tanpa dibatalkan

2.4 KETIKA install Node.js dependencies MAKA build HARUS menggunakan versi dependency yang konsisten dan selesai dengan reliable

2.5 KETIKA aplikasi berhasil deploy MAKA pengguna HARUS bisa login ke dashboard siswa tanpa error

2.6 KETIKA admin login ke dashboard MAKA admin HARUS bisa mengakses dan melihat data siswa dengan lengkap

### Perilaku yang Tidak Berubah (Pencegahan Regresi)

3.1 KETIKA aplikasi berhasil deploy MAKA HARUS TETAP menjalankan aplikasi Laravel dengan semua fungsi yang ada

3.2 KETIKA Docker container start MAKA HARUS TETAP menjalankan script railway-start.sh dengan benar

3.3 KETIKA aplikasi connect ke database MAKA HARUS TETAP menggunakan konfigurasi database dan environment variables yang ada

3.4 KETIKA pengguna mengakses aplikasi yang sudah deploy MAKA mereka HARUS TETAP melihat interface dan fitur E-Presensi yang sama

3.5 KETIKA build menggunakan PHP dependencies yang ada MAKA HARUS TETAP install dengan benar via Composer

3.6 KETIKA aplikasi serve static assets MAKA HARUS TETAP build dan serve dengan benar setelah npm build selesai

3.7 KETIKA siswa login dengan NISN dan password MAKA HARUS TETAP bisa akses dashboard siswa seperti sebelumnya

3.8 KETIKA admin login dengan email dan password MAKA HARUS TETAP bisa akses panel admin dan kelola data siswa