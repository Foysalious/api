<?php  namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class GeoLocation implements CheckType
{
    /*** @var CheckType */
    private $type;

    public function __construct(CheckType $type)
    {
        $this->type = $type;
    }

    public function check(): string
    {
        return AttendanceTypes::GEO_LOCATION_BASED;
    }
}
