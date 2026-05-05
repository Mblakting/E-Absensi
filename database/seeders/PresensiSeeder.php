<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PresensiSeeder extends Seeder
{
    /**
     * Seed data presensi demo.
     * NISN harus sesuai dengan data di SiswaSeeder.
     */
    public function run(): void
    {
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        DB::table('presensi')->insertOrIgnore([
            // Budi Santoso — absen masuk hari ini (tepat waktu)
            [
                'nisn'         => '0012345678',
                'tgl_presensi' => $today,
                'jam_in'       => '06:55:00',
                'jam_out'      => null,
                'foto_in'      => '',
                'foto_out'     => null,
                'lokasi_in'    => '-7.671325,110.589607',
                'lokasi_out'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            // Siti Rahayu — absen masuk hari ini (terlambat)
            [
                'nisn'         => '0023456789',
                'tgl_presensi' => $today,
                'jam_in'       => '07:15:00',
                'jam_out'      => null,
                'foto_in'      => '',
                'foto_out'     => null,
                'lokasi_in'    => '-7.671325,110.589607',
                'lokasi_out'   => null,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            // Budi Santoso — absen lengkap kemarin
            [
                'nisn'         => '0012345678',
                'tgl_presensi' => $yesterday,
                'jam_in'       => '06:50:00',
                'jam_out'      => '14:30:00',
                'foto_in'      => '',
                'foto_out'     => '',
                'lokasi_in'    => '-7.671325,110.589607',
                'lokasi_out'   => '-7.671325,110.589607',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
