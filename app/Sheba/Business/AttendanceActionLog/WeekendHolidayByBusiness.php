<?php namespace Sheba\Business\AttendanceActionLog;

use Sheba\Dal\BusinessHoliday\Model as BusinessHoliday;
use Sheba\Dal\BusinessWeekend\Model as BusinessWeekend;
use App\Models\Business;
use Carbon\Carbon;

class WeekendHolidayByBusiness
{
    public function isHolidayByBusiness(Carbon $date)
    {
        $date = $date->format('Y-m-d');
        $holidays = BusinessHoliday::where('business_id', $this->getBusiness()->id)->get();
        foreach ($holidays as $holiday) {
            if ($date >= Carbon::parse($holiday->start_date)->toDateString() && $date <= Carbon::parse($holiday->end_date)->toDateString()) {
                return true;
            }
        }
        return false;
    }

    public function isWeekendByBusiness(Carbon $date)
    {
        $date = $date->format('l');
        $weekend_day = BusinessWeekend::where('business_id', $this->getBusiness()->id)->pluck('weekday_name')->toArray();
        return in_array(strtolower($date), $weekend_day);
    }

    private function getBusiness()
    {
        $auth_info = \request()->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return Business::find($business_member['business_id']);
    }
}