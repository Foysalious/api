<?php namespace App\Sheba\Business\Attendance\AttendanceTypes;

use Sheba\Business\Attendance\AttendanceTypes\AttendanceSuccess;
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

    /**
     * @return AttendanceSuccess | null
     */
    public function check()
    {
        $office_ip_count = $this->business->offices()->count();
        if ($office_ip_count > 0 ) {
            if ($this->isInWifiArea()) return new AttendanceSuccess(AttendanceTypes::IP_BASED, $this->businessOfficeId);

            $this->errors->push(ActionResult::OUT_OF_WIFI_AREA);
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
