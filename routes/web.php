<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PresensiController; 
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\JurusanController;

Route::middleware(['guest:siswa'])->group(function(){
    
    Route::get('/', function () {
        return view('auth.login');
    })->name('login'); 
    
    Route::post('/proseslogin', [AuthController::class, 'proseslogin'])->name('proseslogin');
});

Route::middleware(['guest:user'])->group(function(){
    
    Route::get('/panel', function () {
        return view('auth.loginadmin');
    })->name('loginadmin'); 

    Route::post('/prosesloginadmin', [AuthController::class, 'prosesloginadmin'])->name('prosesloginadmin');
});

Route::middleware(['auth:siswa'])->group(function(){
    
    //proses
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/proseslogout', [AuthController::class, 'proseslogout'])->name('proseslogout');

    //presensi
    Route::get('/presensi/create',[PresensiController::class, 'create']); 
    Route::post('/presensi/store', [PresensiController::class, 'store'])->name('presensi.store');

    //ubah profile
    Route::get('/presensi/editprofile',[PresensiController::class, 'editprofile'])->name('presensi.editprofile');
    Route::post('/presensi/{nisn}/updateprofile', [PresensiController::class, 'updateprofile'])->name('presensi.updateprofile');

    //histori presensi
    Route::get('/presensi/histori', [PresensiController::class, 'histori'])->name('presensi.histori');
    Route::post('/presensi/gethistori', [PresensiController::class, 'gethistori'])->name('presensi.gethistori');

    //izin presensi
    Route::get('presensi/izin', [PresensiController::class, 'izin'])->name('presensi.izin');
    Route::get('/presensi/buatizin', [PresensiController::class, 'buatizin'])->name('presensi.buatizin');
    Route::post('/presensi/storeizin', [PresensiController::class, 'storeizin'])->name('presensi.storeizin');

});

Route::middleware(['auth:user'])->group(function(){
    Route::get('/proseslogoutadmin', [AuthController::class, 'proseslogoutadmin'])->name('proseslogoutadmin');
    
    // Pastikan rute Dashboard Admin memiliki nama 'dashboardadmin'
    Route::get('/panel/dashboardadmin', [DashboardController::class, 'dashboardadmin'])->name('dashboardadmin'); 

    Route::get('/siswa', [SiswaController::class, 'index']);
    Route::post('/siswa/store',[SiswaController::class,'store']);
    Route::post('/siswa/edit', [SiswaController::class, 'edit']);
    Route::post('/siswa/{nisn}/update',[SiswaController::class,'update']);
    Route::post('/siswa/{nisn}/delete',[SiswaController::class,'delete']);

    //jurusan
    Route::get('/jurusan',[JurusanController::class,'index']);
    Route::post('/jurusan/store',[JurusanController::class,'store']);
    Route::post('/jurusan/edit',[JurusanController::class,'edit']);
    Route::post('/jurusan/{kode_jurusan}/update',[JurusanController::class,'update']);
    Route::post('/jurusan/{kode_jurusan}/delete',[JurusanController::class,'delete']);

    //presensi
    Route::get('/presensi/monitoring',[PresensiController::class,'monitoring']);
    Route::post('/presensi/getpresensi', [PresensiController::class, 'getPresensi'])->name('presensi.getpresensi');
    Route::post('/tampilkanpeta',[PresensiController::class,'tampilkanpeta']);

    Route::get('/presensi/izin-sakit', [PresensiController::class, 'izinSakit']);
    Route::post('/presensi/approve-izin-sakit', [PresensiController::class, 'approveIzinSakit']);
    Route::get('/presensi/batalkan-izin-sakit/{id}', [PresensiController::class, 'batalkanIzinSakit']);
});
