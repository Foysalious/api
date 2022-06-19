<?php namespace Sheba\Location\Distance;

use Sheba\Location\Coords;

abstract class DistanceCalculator
{
    /** @var Coords */
    protected $from;
    /** @var Coords */
    protected $to;

    protected $R = 6378137.0;

    public function from(Coords $from)
    {
        $this->from = $from;
        return $this;
    }

    public function to(Coords $to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return int
     */
    abstract public function distance();

    public function isWithin($meters)
    {
        return $meters >= $this->distance();
    }
}
