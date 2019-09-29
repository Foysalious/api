<?php namespace Sheba\Location\Distance\Strategies;

use Sheba\Location\Distance\DistanceCalculator;

class SphericalPlane extends DistanceCalculator
{
    public function distance()
    {
        $from = $this->from->toRadians();
        $to = $this->to->toRadians();

        $lat1 = $from->lat;
        $lng1 = $from->lng;
        $lat2 = $to->lat;
        $lng2 = $to->lng;

        $x = ($lng2-$lng1) * cos(($lat1+$lat2)/2);
        $y = $lat2-$lat1;
        return sqrt($x*$x + $y*$y) * $this->R;
    }
}


/*
 *
        Equirectangular approximation(Spherical Earth projected to a plane):
        --------------------------------------------------------------------
        	x = Δλ ⋅ cos φm
            y = Δφ
            d = R ⋅ √(x² + y²)

        where, φ is latitude, λ is longitude.
 *
 */