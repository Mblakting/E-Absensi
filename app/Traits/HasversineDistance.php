<?php

namespace App\Traits;

use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Schema;

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

        $latUserRaw = trim($parts[0]);
        $lonUserRaw = trim($parts[1]);

        if (!is_numeric($latUserRaw) || !is_numeric($lonUserRaw)) {
            return ['valid' => false, 'message' => 'Koordinat GPS tidak valid.'];
        }

        $latUser = (float) $latUserRaw;
        $lonUser = (float) $lonUserRaw;

        if ($latUser < -90 || $latUser > 90 || $lonUser < -180 || $lonUser > 180) {
            return ['valid' => false, 'message' => 'Koordinat GPS tidak valid.'];
        }

        $sekolah = Schema::hasTable('school_settings')
            ? SchoolSetting::query()->first()
            : null;

        $latSekolah = $sekolah ? (float) $sekolah->latitude : (float) config('school.latitude', -7.671325);
        $lonSekolah = $sekolah ? (float) $sekolah->longitude : (float) config('school.longitude', 110.589607);
        $radiusMax  = $sekolah ? (int) $sekolah->radius_meters : (int) config('school.radius_meters', 100);

        $jarak  = $this->distance($latSekolah, $lonSekolah, $latUser, $lonUser);
        $radius = (int) round($jarak);

        if ($radius > $radiusMax) {
            return [
                'valid'   => false,
                'message' => 'Anda berada di luar radius sekolah. Jarak Anda ' . $radius . ' meter, maksimal ' . $radiusMax . ' meter.',
                'type'    => 'out',
                'jarak'   => $radius,
            ];
        }

        return ['valid' => true, 'jarak' => $radius];
    }
}
