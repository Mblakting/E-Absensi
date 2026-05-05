<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            JurusanSeeder::class,
            SiswaSeeder::class,
            // Uncomment baris di bawah untuk data demo presensi & izin:
            // PresensiSeeder::class,
            // PengajuanIzinSeeder::class,
        ]);

        // Buat akun admin default
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            ]
        );
    }
}
