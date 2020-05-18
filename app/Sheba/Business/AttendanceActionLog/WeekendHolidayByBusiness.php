<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\BusinessHoliday\Model as BusinessHolidayModel;
use Sheba\Dal\BusinessWeekend\Model as BusinessWeekendModel;

class WeekendHolidayByBusiness
{
    public function isHolidayByBusiness(Carbon $date)
    {
        $date = $date->format('Y-m-d');
        $holidays = BusinessHolidayModel::where('business_id', $this->getBusiness()->id)->get();
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
        $weekend_day = BusinessWeekendModel::where('business_id', $this->getBusiness()->id)->pluck('weekday_name')->toArray();
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