<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengajuanIzinSeeder extends Seeder
{
    /**
     * Seed data pengajuan izin demo.
     * NISN harus sesuai dengan data di SiswaSeeder.
     */
    public function run(): void
    {
        DB::table('pengajuan_izin')->insertOrIgnore([
            // Ahmad Fauzi — izin hari ini (pending)
            [
                'nisn'            => '0034567890',
                'tgl_izin'        => now()->toDateString(),
                'status'          => 'i',
                'keterangan'      => 'Ada keperluan keluarga',
                'status_approved' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            // Siti Rahayu — sakit 2 hari lalu (disetujui)
            [
                'nisn'            => '0023456789',
                'tgl_izin'        => now()->subDays(2)->toDateString(),
                'status'          => 's',
                'keterangan'      => 'Demam dan flu',
                'status_approved' => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}
