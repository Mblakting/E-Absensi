<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Lokasi Sekolah
    |--------------------------------------------------------------------------
    |
    | Koordinat GPS sekolah dan radius maksimum (dalam meter) yang diizinkan
    | untuk melakukan presensi. Sesuaikan nilai ini dengan lokasi sekolah Anda.
    |
    */

    'latitude'     => env('SCHOOL_LATITUDE', -7.671325),
    'longitude'    => env('SCHOOL_LONGITUDE', 110.589607),

    /*
     * Radius maksimum dalam meter dari lokasi sekolah.
     * Siswa harus berada dalam radius ini untuk bisa absen.
     * Nilai default: 500 meter.
     */
    'radius_meter' => env('SCHOOL_RADIUS', 500),

    /*
     * Nama sekolah (ditampilkan di UI).
     */
    'nama' => env('SCHOOL_NAME', 'SMK Negeri 1'),
];
