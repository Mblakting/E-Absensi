<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            // Menyimpan face descriptor (128-dimensi float array) sebagai JSON
            $table->longText('face_descriptor')->nullable()->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('face_descriptor');
        });
    }
};
