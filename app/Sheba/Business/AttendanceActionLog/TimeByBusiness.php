<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;

class TimeByBusiness
{
    public function getOfficeStartTimeByBusiness()
    {
        $business_hour = BusinessOfficeHour::where('business_id', $this->getBusiness()->id)->first();
        if (is_null($business_hour)) return null;
        return $business_hour->start_time;
    }

    public function getOfficeEndTimeByBusiness()
    {
        $business_hour = BusinessOfficeHour::where('business_id', $this->getBusiness()->id)->first();
        if (is_null($business_hour)) return null;
        return $business_hour->end_time;
    }

    private function getBusiness()
    {
        $auth_info = \request()->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return Business::find($business_member['business_id']);
    }
}