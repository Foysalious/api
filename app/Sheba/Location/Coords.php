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

    public function isSameTo(Coords $target)
    {
        $this->stringify();
        $target->stringify();
        return $this->lat == $target->lat && $this->lng == $target->lng;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    private function round($precision = 8)
    {
        $this->lat = round($this->lat, $precision);
        $this->lng = round($this->lng, $precision);
    }

    private function stringify($precision = 8)
    {
        $this->round($precision);
        $this->lat = str_pad($this->lat, $precision + 3, '0');
        $this->lng = str_pad($this->lng, $precision + 3, '0');
    }
}