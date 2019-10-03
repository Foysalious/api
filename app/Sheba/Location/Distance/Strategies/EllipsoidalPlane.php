<?php namespace Sheba\Location\Distance\Strategies;

use Sheba\Location\Distance\DistanceCalculator;

class EllipsoidalPlane extends DistanceCalculator
{
    public function distance()
    {
        $delta_lat = $this->from->lat - $this->to->lat;
        $delta_lng = $this->from->lng - $this->to->lng;
        $from = $this->from->toRadians();
        $to = $this->to->toRadians();
        $lat_m = ($from->lat - $to->lat) / 2;
        $k1 = 111.13209 - 0.56605 * cos(2 * $lat_m) + 0.00120 * cos(4 * $lat_m);
        $k2 = 111.41513 * cos($lat_m) - 0.09455 * cos(3 * $lat_m) + 0.00012 * cos(5 * $lat_m);

        return sqrt(pow($k1 * $delta_lat, 2) + pow($k2 * $delta_lng, 2)) * 1000;
    }
}