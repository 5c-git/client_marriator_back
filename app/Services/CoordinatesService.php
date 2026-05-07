<?php

namespace App\Services;

use App\Models\User;

class CoordinatesService
{
    public const RADIUS_EARTH = 6371;

    public static function isPointInRadius(
        float $latitudeFirst,
        float $longitudeFirst,
        float $latitudeTwo,
        float $longitudeTwo,
        int $radius,
        int $radiusBid,
        ?User $user = null
    ): bool {
        $lat1_rad = deg2rad($latitudeFirst);
        $lon1_rad = deg2rad($longitudeFirst);
        $lat2_rad = deg2rad($latitudeTwo);
        $lon2_rad = deg2rad($longitudeTwo);

        $delta_lat = $lat2_rad - $lat1_rad;
        $delta_lon = $lon2_rad - $lon1_rad;

        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
            cos($lat1_rad) * cos($lat2_rad) *
            sin($delta_lon / 2) * sin($delta_lon / 2);

        $c        = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = self::RADIUS_EARTH * $c;

        if($user){
            $user->mapRadius = round($distance,2);
        }

        // Проверка попадания в радиус
        return $distance <= $radius && $distance <= $radiusBid;
    }


}
