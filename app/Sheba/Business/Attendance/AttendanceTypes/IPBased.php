<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceModeType;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionResult;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class IPBased extends AttendanceType
{
    private $business;
    private $ip;
    private $businessOfficeId;

    public function __construct($business, $ip)
    {
        $this->business = $business;
        $this->ip = $ip;
    }

    public function check()
    {
        $office_ip_count = $this->business->offices()->count();
        if ($office_ip_count > 0 ){
            $attendance_mode_type = new AttendanceModeType();
            if ($this->isInWifiArea()) $attendance_mode_type->setAttendanceModeType(AttendanceTypes::IP_BASED)->setBusinessOffice($this->businessOfficeId);
            $this->error->push(ActionResult::OUT_OF_WIFI_AREA);
        }
        return $this->next ? $this->next->check() : null;
    }

    private function isInWifiArea()
    {
        $ips = $this->business->offices->pluck('ip', 'id')->toArray();
        $this->businessOfficeId = array_search($this->ip, $ips);
        return in_array($this->ip, $ips);
    }
}
