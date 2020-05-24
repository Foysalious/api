<?php namespace Sheba\Map;


class Distance
{
    private $durationInMinutes;
    private $distanceInKms;

    /**
     * @return mixed
     */
    public function getDurationInMinutes()
    {
        return $this->durationInMinutes;
    }

    /**
     * @param mixed $duration
     * @return Distance
     */
    public function setDurationInMinutes($duration)
    {
        $this->durationInMinutes = $duration;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDistanceInKms()
    {
        return $this->distanceInKms;
    }

    /**
     * @param mixed $distance
     * @return Distance
     */
    public function setDistanceInKms($distance)
    {
        $this->distanceInKms = $distance;
        return $this;
    }

}