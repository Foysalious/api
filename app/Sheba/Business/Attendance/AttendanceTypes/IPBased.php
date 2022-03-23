<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\AttendanceActionLog\ActionChecker\ActionResultCodes;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class IPBased extends AttendanceType
{
    private $business;
    private $ip;
    private $error;

    public function __construct($business, $ip, $error)
    {
        $this->business = $business;
        $this->ip = $ip;
        $this->error = $error;
    }

    public function check()
    {
        if ($this->isInWifiArea()) return AttendanceTypes::IP_BASED;
        $this->error[] = ActionResultCodes::OUT_OF_WIFI_AREA;
        return $this->error;
    }

    private function isInWifiArea()
    {
        return in_array($this->ip, $this->business->offices->pluck('ip')->toArray());
    }
}
