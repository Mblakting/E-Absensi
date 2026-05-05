<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan unique constraint: satu siswa hanya boleh punya
     * satu pengajuan izin per tanggal.
     */
    public function up(): void
    {
        Schema::table('pengajuan_izin', function (Blueprint $table) {
            $table->unique(['nisn', 'tgl_izin'], 'pengajuan_izin_nisn_tgl_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_izin', function (Blueprint $table) {
            $table->dropUnique('pengajuan_izin_nisn_tgl_unique');
        });
    }
};
