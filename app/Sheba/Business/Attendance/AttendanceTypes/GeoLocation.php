<?php  namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceSuccess;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionResult;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Location\Circle;

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

    /**
     * @return AttendanceSuccess | null
     */
    public function check()
    {
        $office_geo_location_count = $this->business->geoOffices()->count();
        if ($office_geo_location_count > 0) {
            if ($this->isInGeoLocation()) {
                return new AttendanceSuccess(AttendanceTypes::GEO_LOCATION_BASED, $this->businessOfficeId);
            }
            $this->errors->push(ActionResult::OUT_OF_GEO_LOCATION);
        }
        return $this->next ? $this->next->check() : null;
    }

    private function isInGeoLocation(): bool
    {
        foreach ($this->geoOffices as $geo_office) {
            /** @var Circle $circle */
            $circle = $geo_office->circle;
            if ($circle->contains($this->coords)) {
                $this->businessOfficeId = $geo_office->id;
                return true;
            }
        }
        return false;
    }
}
