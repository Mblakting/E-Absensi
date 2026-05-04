<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengajuanIzinSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pengajuan_izin')->insert([
            [
                'nisn'            => '0034567890',
                'tgl_izin'        => date('Y-m-d'),
                'status'          => 'i',
                'keterangan'      => 'Ada keperluan keluarga',
                'status_approved' => 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'nisn'            => '0023456789',
                'tgl_izin'        => date('Y-m-d', strtotime('-2 days')),
                'status'          => 's',
                'keterangan'      => 'Demam dan flu',
                'status_approved' => 1,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);
    }
}
