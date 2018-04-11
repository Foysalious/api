<?php namespace Sheba\Location;

class Coords
{
    private $lat;
    private $lng;
    private $id;

    public function __construct($lat, $lng, $id = null)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->id = $id;
    }

    public function toRadians()
    {
        $lat_radian = $this->lat * PI() / 180;
        $lng_radian = $this->lng * PI() / 180;
        return new Coords($lat_radian, $lng_radian, $this->id);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}