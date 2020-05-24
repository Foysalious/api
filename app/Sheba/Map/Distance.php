<?php namespace Sheba\Map;


class Distance
{
    private $duration;
    private $distance;

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     * @return Distance
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     * @return Distance
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;
        return $this;
    }

}