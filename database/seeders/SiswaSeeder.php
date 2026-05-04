<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('siswa')->insertOrIgnore([
            [
                'nisn'         => '0012345678',
                'nama_lengkap' => 'Budi Santoso',
                'kelas'        => 'XII TKJ 1',
                'no_hp'        => '081234567890',
                'foto'         => '',
                'kode_jurusan' => 'TKJ',
                'password'     => Hash::make('12345'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nisn'         => '0023456789',
                'nama_lengkap' => 'Siti Rahayu',
                'kelas'        => 'XII RPL 1',
                'no_hp'        => '082345678901',
                'foto'         => '',
                'kode_jurusan' => 'RPL',
                'password'     => Hash::make('12345'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nisn'         => '0034567890',
                'nama_lengkap' => 'Ahmad Fauzi',
                'kelas'        => 'XI MM 1',
                'no_hp'        => '083456789012',
                'foto'         => '',
                'kode_jurusan' => 'MM',
                'password'     => Hash::make('12345'),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
