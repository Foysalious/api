<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Sheba\Dal\Attendance\Statuses;
use Carbon\Carbon;

class CheckinStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $today_last_checkin_time = $this->business->calculationTodayLastCheckInTime($this->whichHalfDay);
        dd($today_last_checkin_time, 'CheckinStatusCalculator');
        if (is_null($today_last_checkin_time)) return Statuses::ON_TIME;

        $today_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);
        $today_checkin_time_without_second = Carbon::parse($today_checkin_time->format('Y-m-d H:i'));
        if ($today_checkin_time_without_second->greaterThan($today_last_checkin_time)) return Statuses::LATE;
        return Statuses::ON_TIME;
    }
}
