<?php  namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceModeType;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionResult;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;

class GeoLocation extends AttendanceType
{
    private $business;
    private $coords;
    private $geoOffices;
    private $businessOfficeId;


    public function __construct($business, $coords)
    {
        $this->business = $business;
        $this->geoOffices = $this->business->geoOffices()->get();
        $this->coords = $coords;
    }
    public function check()
    {
        $office_geo_location_count = $this->business->geoOffices()->count();
        if ($office_geo_location_count > 0){
            $attendance_mode_type = new AttendanceModeType();
            $attendance_mode_type->setAttendanceModeType(AttendanceTypes::GEO_LOCATION_BASED)->setBusinessOffice($this->businessOfficeId);
            if ($this->isInGeoLocation()) return $attendance_mode_type->get();
            $this->error->push(ActionResult::OUT_OF_GEO_LOCATION);
        }
        return $this->next ? $this->next->check() : null;
    }

    private function isInGeoLocation(): bool
    {
        $is_within = false;
        foreach ($this->geoOffices as $geo_office){
            $geo = $geo_office->location;
            $to_coords = new Coords(floatval($geo['lat']), floatval($geo['lng']));
            $distance = (new Distance(DistanceStrategy::$VINCENTY))->linear();
            $is_within = $distance->to($to_coords)->from($this->coords)->isWithin(floatval($geo['radius']));
            if ($is_within) {
                $this->businessOfficeId = $geo_office->id;
                break;
            }
        }
        return $is_within;
    }
}
