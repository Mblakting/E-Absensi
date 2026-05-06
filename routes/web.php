<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Authcontroller as AuthController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\LaporanController;

// ─── Guest: Siswa ─────────────────────────────────────────────────────────────
Route::middleware('guest:siswa')->group(function () {
    Route::get('/', fn () => view('auth.login'))->name('login');
    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])
        ->middleware('throttle:10,1')
        ->name('proseslogin');
    // Redirect GET requests to login page
    Route::get('/proseslogin', fn () => redirect()->route('login'));
});

// ─── Guest: Admin ─────────────────────────────────────────────────────────────
Route::middleware('guest:user')->group(function () {
    Route::get('/panel', fn () => view('auth.loginadmin'))->name('loginadmin');
    Route::post('/prosesloginadmin', [AuthController::class, 'prosesloginadmin'])
        ->middleware('throttle:10,1')
        ->name('prosesloginadmin');
    // Redirect GET requests to admin login page
    Route::get('/prosesloginadmin', fn () => redirect()->route('loginadmin'));
});

// ─── Auth: Siswa ──────────────────────────────────────────────────────────────
Route::middleware('auth:siswa')->group(function () {

    // Dashboard & Logout
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/proseslogout', [AuthController::class, 'proseslogout'])->name('proseslogout');

    // Presensi (GPS + Foto)
    Route::get('/presensi/create', [PresensiController::class, 'create'])->name('presensi.create');
    Route::post('/presensi/store', [PresensiController::class, 'store'])->name('presensi.store');

    // Presensi (Face Recognition)
    Route::get('/presensi/face', [FaceRecognitionController::class, 'absenFacePage'])->name('presensi.face');
    Route::post('/presensi/face/store', [FaceRecognitionController::class, 'storeAbsenFace'])->name('presensi.face.store');
    Route::get('/face/descriptors', [FaceRecognitionController::class, 'getDescriptors'])->name('face.descriptors');

    // Profil Siswa
    Route::get('/presensi/editprofile', [PresensiController::class, 'editprofile'])->name('presensi.editprofile');
    Route::post('/presensi/updateprofile', [PresensiController::class, 'updateprofile'])->name('presensi.updateprofile');

    // Histori Presensi
    Route::get('/presensi/histori', [PresensiController::class, 'histori'])->name('presensi.histori');
    Route::post('/presensi/gethistori', [PresensiController::class, 'gethistori'])->name('presensi.gethistori');

    // Pengajuan Izin
    Route::get('/presensi/izin', [PresensiController::class, 'izin'])->name('presensi.izin');
    Route::get('/presensi/buatizin', [PresensiController::class, 'buatizin'])->name('presensi.buatizin');
    Route::post('/presensi/storeizin', [PresensiController::class, 'storeizin'])->name('presensi.storeizin');
});

// ─── Auth: Admin ──────────────────────────────────────────────────────────────
Route::middleware('auth:user')->group(function () {

    // Dashboard & Logout
    Route::get('/panel/dashboardadmin', [DashboardController::class, 'dashboardadmin'])->name('dashboardadmin');
    Route::post('/proseslogoutadmin', [AuthController::class, 'proseslogoutadmin'])->name('proseslogoutadmin');

    // Manajemen Siswa
    Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
    Route::post('/siswa/store', [SiswaController::class, 'store'])->name('siswa.store');
    Route::post('/siswa/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::post('/siswa/{nisn}/update', [SiswaController::class, 'update'])->name('siswa.update');
    Route::post('/siswa/{nisn}/delete', [SiswaController::class, 'delete'])->name('siswa.delete');

    // Manajemen Jurusan
    Route::get('/jurusan', [JurusanController::class, 'index'])->name('jurusan.index');
    Route::post('/jurusan/store', [JurusanController::class, 'store'])->name('jurusan.store');
    Route::post('/jurusan/edit', [JurusanController::class, 'edit'])->name('jurusan.edit');
    Route::post('/jurusan/{kode_jurusan}/update', [JurusanController::class, 'update'])->name('jurusan.update');
    Route::post('/jurusan/{kode_jurusan}/delete', [JurusanController::class, 'delete'])->name('jurusan.delete');

    // Monitoring Presensi
    Route::get('/presensi/monitoring', [PresensiController::class, 'monitoring'])->name('presensi.monitoring');
    Route::get('/presensi/monitoring-count', [PresensiController::class, 'monitoringCount'])->name('presensi.monitoring.count');
    Route::post('/presensi/getpresensi', [PresensiController::class, 'getPresensi'])->name('presensi.getpresensi');
    Route::post('/tampilkanpeta', [PresensiController::class, 'tampilkanpeta'])->name('presensi.peta');

    // Manajemen Izin/Sakit
    Route::get('/presensi/izin-sakit', [PresensiController::class, 'izinSakit'])->name('presensi.izin-sakit');
    Route::post('/presensi/approve-izin-sakit', [PresensiController::class, 'approveIzinSakit'])->name('presensi.approve-izin');
    Route::get('/presensi/batalkan-izin-sakit/{id}', [PresensiController::class, 'batalkanIzinSakit'])->name('presensi.batalkan-izin');

    // Face Recognition — Enroll Wajah Siswa
    Route::get('/siswa/{nisn}/enroll-face', [FaceRecognitionController::class, 'enrollPage'])->name('face.enroll');
    Route::post('/face/save-descriptor', [FaceRecognitionController::class, 'saveDescriptor'])->name('face.save');
    Route::post('/face/delete-descriptor', [FaceRecognitionController::class, 'deleteDescriptor'])->name('face.delete');

    // Laporan & Rekap
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/rekap', [LaporanController::class, 'rekapSiswa'])->name('laporan.rekap');
    Route::get('/laporan/tren-harian', [LaporanController::class, 'trenHarian'])->name('laporan.tren');
    Route::get('/laporan/export-csv', [LaporanController::class, 'exportCsv'])->name('laporan.export');
});
