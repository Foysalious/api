<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Dal\Attendance\Statuses;
use Carbon\Carbon;

class CheckinStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $time = new TimeByBusiness();
        $last_checkin_time = $time->getOfficeStartTimeByBusiness();
        if (is_null($last_checkin_time)) return Statuses::ON_TIME;
        $todays_last_checkin_time = Carbon::parse($last_checkin_time);
        $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);
        if ($todays_checkin_time->greaterThan($todays_last_checkin_time))
            return Statuses::LATE;

        return Statuses::ON_TIME;
    }

}
