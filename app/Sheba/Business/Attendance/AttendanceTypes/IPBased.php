<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;

class IPBased extends AttendanceType
{
    private $business;
    private $ip;

    public function __construct($business, $ip)
    {
        $this->business = $business;
        $this->ip = $ip;
    }

    public function check(): string
    {
        if ($this->isInWifiArea()) return AttendanceTypes::IP_BASED;
    }

    private function isInWifiArea()
    {
        return in_array($this->ip, $this->business->offices->pluck('ip')->toArray());
    }
}
