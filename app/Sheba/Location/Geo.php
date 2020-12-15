<?php namespace Sheba\Location;


class Geo
{
    private $lat;
    private $lng;

    public function __construct($lat = null, $lng = null)
    {
        $this->setLat($lat);
        $this->setLng($lng);
    }

    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    public function getLat()
    {
        return $this->lat ? (double)$this->lat : null;
    }

    public function getLng()
    {
        return $this->lng ? (double)$this->lng : null;
    }

    /**
     * @return float[]|null[]
     */
    public function toArray()
    {
        return [
            'lat' => $this->getLat(),
            'lng' => $this->getLng()
        ];
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return \stdClass
     */
    public function toStdObject()
    {
        return json_decode($this->toJson());
    }
}
