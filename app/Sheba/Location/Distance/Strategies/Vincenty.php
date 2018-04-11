<?php namespace Sheba\Location\Distance\Strategies;

use Sheba\Location\Distance\DistanceCalculator;

class Vincenty extends DistanceCalculator
{
    private $maxIteration = 1000;
    private $convergeAccuracy = 1e-12;

    public function distance()
    {
        $from = $this->from->toRadians();
        $to = $this->to->toRadians();

        $lat1 = $from->lat;
        $lng1 = $from->lng;
        $lat2 = $to->lat;
        $lng2 = $to->lng;

        $a = $this->R;
		$f = 1/298.257223563;
		$b = 6356752.314245;

		$U1 = atan((1-$f)*tan($lat1));
		$U2 = atan((1-$f)*tan($lat2));
		$cos_u2 = cos($U2);
		$cos_u1 = cos($U1);
		$sin_u2 = sin($U2);
		$sin_u1 = sin($U1);

		$L = $lng2 - $lng1;
		$lambda = $L;
        $anti_meridian = abs($L) > PI();

		$i = 0;
        do {
            $sin_lambda = sin($lambda);
            $cos_lambda = cos($lambda);
            $sin_sq_sigma = pow($cos_u2 * $sin_lambda, 2) + pow($cos_u1*$sin_u2 - $sin_u1*$cos_u2*$cos_lambda, 2);
            if ($sin_sq_sigma == 0) return 0; // co-incident points
            $sin_sigma = sqrt($sin_sq_sigma);
            $cos_sigma = $sin_u1*$sin_u2 + $cos_u1*$cos_u2*$cos_lambda;
            $sigma = atan2($sin_sigma, $cos_sigma);
            $sin_alpha = ($cos_u1*$cos_u2*$sin_lambda) / $sin_sigma;
            $cos_sq_alpha = 1 - $sin_alpha*$sin_alpha;
            $cos_2_sigma_m = ($cos_sq_alpha != 0) ? ($cos_sigma - 2*$sin_u1*$sin_u2/$cos_sq_alpha) : 0; // equatorial line: cosSqα=0 (§6)
            $cos_sq_2_sigma_m = $cos_2_sigma_m * $cos_2_sigma_m;
            $c = ($f/16) * $cos_sq_alpha * (4 + $f * (4-3*$cos_sq_alpha));
            $lambda_prime = $lambda;
            $lambda = $L + (1-$c) * $f * $sin_alpha * ($sigma + $c*$sin_sigma*($cos_2_sigma_m + $c*$cos_sigma*(-1+2*$cos_sq_2_sigma_m)));
            $iterationCheck = $anti_meridian ? abs($lambda)-PI() : abs($lambda);
            if ($iterationCheck > PI()) return null;
        } while (abs($lambda - $lambda_prime) > $this->convergeAccuracy && ++$i < $this->maxIteration);

        $u_sq = $cos_sq_alpha * ($a*$a - $b*$b) / ($b*$b);
        $A = 1 + $u_sq/16384 * ( 4096 + $u_sq * (-768 + $u_sq*(320-175*$u_sq)) );
        $B = $u_sq/1024 * ( 256 + $u_sq * (-128+$u_sq*(74-47*$u_sq)) );
        $delta_sigma = $B * $sin_sigma * (
            $cos_2_sigma_m + $B/4 * (
                ($cos_sigma * (-1+2*$cos_sq_2_sigma_m)) - ($B/6 * $cos_2_sigma_m * (-3+4*$sin_sigma*$sin_sigma) * (-3+4*$cos_sq_2_sigma_m))
            )
        );

        return $b * $A * ($sigma - $delta_sigma);
    }
}

/*
 * https://en.wikipedia.org/wiki/Vincenty%27s_formulae
 */