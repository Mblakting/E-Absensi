<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('jurusan')->insertOrIgnore([
            ['kode_jurusan' => 'TKJ',  'nama_jurusan' => 'Teknik Komputer dan Jaringan',    'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'RPL',  'nama_jurusan' => 'Rekayasa Perangkat Lunak',         'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'MM',   'nama_jurusan' => 'Multimedia',                        'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'AKL',  'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga',   'created_at' => now(), 'updated_at' => now()],
            ['kode_jurusan' => 'OTKP', 'nama_jurusan' => 'Otomatisasi Tata Kelola Perkantoran', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
