<?php namespace Sheba\Location\Distance\Strategies;

use Sheba\Location\Distance\DistanceCalculator;

class Cosine extends DistanceCalculator
{
    public function distance()
    {
        $from = $this->from->toRadians();
        $to = $this->to->toRadians();

        $lat_1 = $from->lat;
        $lat_2 = $to->lat;
        $lng_1 = $from->lng;
        $lng_2 = $to->lng;

        $c = ACOS(SIN($lat_1) * SIN($lat_2) + COS($lat_1) * COS($lat_2) * COS($lng_1 - $lng_2));

        return $this->R * $c;
    }
}

/*
 *
        Spherical Law of Cosines:
        -------------------------
        d = acos( sin φ1 ⋅ sin φ2 + cos φ1 ⋅ cos φ2 ⋅ cos Δλ ) ⋅ R

        where	φ is latitude, λ is longitude.
 *
 */