<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Carbon\Carbon;
use Sheba\Dal\Attendance\Statuses;

class CheckinStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $todays_last_checkin_time = Carbon::parse(Carbon::now()->format('Y-m-d') . ' '. self::LAST_CHECKIN_TIME);
        $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);

        if ($todays_checkin_time->greaterThan($todays_last_checkin_time))
            return Statuses::LATE;

        return Statuses::ON_TIME;
    }
}
