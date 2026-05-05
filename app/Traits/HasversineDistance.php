<?php

namespace App\Traits;

/**
 * Trait untuk menghitung jarak antara dua koordinat GPS
 * menggunakan formula Haversine.
 */
trait HasversineDistance
{
    /**
     * Hitung jarak antara dua koordinat GPS dalam meter.
     */
    protected function distance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Validasi apakah koordinat berada dalam radius sekolah.
     * Mengembalikan jarak dalam meter, atau null jika format tidak valid.
     */
    protected function validateSchoolRadius(string $lokasi): array
    {
        $parts = explode(',', $lokasi);
        if (count($parts) !== 2) {
            return ['valid' => false, 'message' => 'Format lokasi tidak valid.'];
        }

        $latUser = (float) trim($parts[0]);
        $lonUser = (float) trim($parts[1]);

        if ($latUser < -90 || $latUser > 90 || $lonUser < -180 || $lonUser > 180) {
            return ['valid' => false, 'message' => 'Koordinat GPS tidak valid.'];
        }

        $latSekolah = (float) config('school.latitude', -7.671325);
        $lonSekolah = (float) config('school.longitude', 110.589607);
        $radiusMax  = (int)   config('school.radius_meter', 500);

        $jarak  = $this->distance($latSekolah, $lonSekolah, $latUser, $lonUser);
        $radius = (int) round($jarak);

        if ($radius > $radiusMax) {
            return [
                'valid'   => false,
                'message' => 'Anda berada di luar area sekolah (' . $radius . ' meter dari sekolah)!',
                'type'    => 'out',
                'jarak'   => $radius,
            ];
        }

        return ['valid' => true, 'jarak' => $radius];
    }
}
