<?php namespace Sheba\Location;

use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class Circle
{
    /** @var Coords */
    private $coords;

    /** @var float */
    private $radiusInMeter;

    public function __construct(Coords $coords, $radius_in_meter)
    {
        $this->coords = $coords;
        $this->radiusInMeter = $radius_in_meter;
    }

    /**
     * @param Coords $target
     * @return bool
     */
    public function contains(Coords $target): bool
    {
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->linear();
        return $distance->to($this->coords)->from($target)->isWithin($this->radiusInMeter);
    }
}
