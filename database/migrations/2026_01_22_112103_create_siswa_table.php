<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. JURUSAN (harus dibuat dulu karena siswa FK ke sini)
        Schema::create('jurusan', function (Blueprint $table) {
            $table->string('kode_jurusan')->primary();
            $table->string('nama_jurusan');
            $table->timestamps();
        });

        // 2. SISWA (FK ke jurusan, dipakai sebagai auth guard 'siswa')
        Schema::create('siswa', function (Blueprint $table) {
            $table->string('nisn')->primary();
            $table->string('nama_lengkap');
            $table->string('kelas');
            $table->string('no_hp')->nullable();
            $table->string('foto')->nullable();
            $table->string('kode_jurusan');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('kode_jurusan')
                  ->references('kode_jurusan')
                  ->on('jurusan')
                  ->onDelete('restrict');
        });

        // 3. PRESENSI (FK ke siswa)
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->string('nisn');
            $table->date('tgl_presensi');
            $table->time('jam_in')->nullable();
            $table->time('jam_out')->nullable();
            $table->string('foto_in')->nullable();
            $table->string('foto_out')->nullable();
            $table->string('lokasi_in')->nullable();
            $table->string('lokasi_out')->nullable();
            $table->timestamps();

            $table->foreign('nisn')
                  ->references('nisn')
                  ->on('siswa')
                  ->onDelete('cascade');
        });

        // 4. PENGAJUAN IZIN (FK ke siswa)
        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->id();
            $table->string('nisn');
            $table->date('tgl_izin');
            $table->string('status', 1); // 'i' = izin, 's' = sakit
            $table->text('keterangan')->nullable();
            $table->tinyInteger('status_approved')->default(0); // 0=pending, 1=disetujui, 2=ditolak
            $table->timestamps();

            $table->foreign('nisn')
                  ->references('nisn')
                  ->on('siswa')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
        Schema::dropIfExists('presensi');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('jurusan');
    }
};
