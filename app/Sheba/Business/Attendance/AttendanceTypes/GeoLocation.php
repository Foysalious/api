<?php  namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class GeoLocation extends AttendanceType
{
    private $business;
    private $lng;
    private $lat;
    private $geoOffices;

    public function __construct($business, $lat, $lng)
    {
        $this->business = $business;
        $this->geoOffices = $this->business->geoOffices()->get();
        $this->lng = $lat;
        $this->lat = $lng;
    }
    public function check(): string
    {
        if ($this->isInGeoLocation()) return AttendanceTypes::GEO_LOCATION_BASED;
    }

    private function isInGeoLocation(): bool
    {
        $is_within = false;
        $from_coords = new Coords(floatval($this->lat), floatval($this->lng));
        foreach ($this->geoOffices as $geo_office){
            $geo = $geo_office->location;
            $to_coords = new Coords(floatval($geo['lat']), floatval($geo['lng']));
            $distance = (new Distance(DistanceStrategy::$VINCENTY))->linear();
            $is_within = $distance->to($to_coords)->from($from_coords)->isWithin(floatval($geo['radius']));
            if ($is_within) break;
        }
        return $is_within;
    }
}
