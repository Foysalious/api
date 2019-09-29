<?php namespace Sheba\Location\Distance\Strategies;

use Sheba\Location\Distance\DistanceCalculator;

class Haversine extends DistanceCalculator
{
    public function distance()
    {
        $from_radian = $this->from->toRadians();
        $to_radian = $this->to->toRadians();

        $lat1 = $from_radian->lat;
        $lat2 = $to_radian->lat;
        $delta_lat = ($this->from->lat - $this->to->lat) * PI() / 180;
        $delta_lng = ($this->from->lng - $this->to->lng) * PI() / 180;

        $a = sin($delta_lat/2) * sin($delta_lat/2) + cos($lat1) * cos($lat2) * sin($delta_lng/2) * sin($delta_lng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $this->R * $c;
    }
}

/*
 *
        Haversine formula:
        ------------------
        a = sin²(Δφ/2) + cos φ1 ⋅ cos φ2 ⋅ sin²(Δλ/2)
        c = 2 ⋅ atan2( √a, √(1−a) )
        d = R ⋅ c

        where	φ is latitude, λ is longitude.
 *
 */