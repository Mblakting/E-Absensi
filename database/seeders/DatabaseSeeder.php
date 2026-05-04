<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user — gunakan firstOrCreate agar tidak duplikat
        User::firstOrCreate(
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );

        // Jurusan sample data
        $jurusanData = [
            ['kode_jurusan' => 'TKJ',  'nama_jurusan' => 'Teknik Komputer dan Jaringan'],
            ['kode_jurusan' => 'RPL',  'nama_jurusan' => 'Rekayasa Perangkat Lunak'],
            ['kode_jurusan' => 'MM',   'nama_jurusan' => 'Multimedia'],
            ['kode_jurusan' => 'AKL',  'nama_jurusan' => 'Akuntansi dan Keuangan Lembaga'],
            ['kode_jurusan' => 'OTKP', 'nama_jurusan' => 'Otomatisasi dan Tata Kelola Perkantoran'],
        ];

        foreach ($jurusanData as $j) {
            DB::table('jurusan')->updateOrInsert(
                ['kode_jurusan' => $j['kode_jurusan']],
                array_merge($j, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Siswa sample data
        $siswaData = [
            [
                'nisn'         => '0012345678',
                'nama_lengkap' => 'Budi Santoso',
                'kelas'        => 'XI TKJ 1',
                'no_hp'        => '081234567890',
                'foto'         => '',
                'kode_jurusan' => 'TKJ',
                'password'     => Hash::make('12345'),
            ],
            [
                'nisn'         => '0012345679',
                'nama_lengkap' => 'Siti Rahayu',
                'kelas'        => 'XI RPL 1',
                'no_hp'        => '081234567891',
                'foto'         => '',
                'kode_jurusan' => 'RPL',
                'password'     => Hash::make('12345'),
            ],
            [
                'nisn'         => '0012345680',
                'nama_lengkap' => 'Ahmad Fauzi',
                'kelas'        => 'X MM 1',
                'no_hp'        => '081234567892',
                'foto'         => '',
                'kode_jurusan' => 'MM',
                'password'     => Hash::make('12345'),
            ],
        ];

        foreach ($siswaData as $s) {
            DB::table('siswa')->updateOrInsert(
                ['nisn' => $s['nisn']],
                array_merge($s, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
