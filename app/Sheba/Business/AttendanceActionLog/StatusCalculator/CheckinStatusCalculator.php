<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Dal\Attendance\Statuses;
use Carbon\Carbon;

class CheckinStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $todays_last_checkin_time = null;
        if ($this->whichHalfDay) {
            if ($this->whichHalfDay == HalfDayType::FIRST_HALF) {
                $todays_last_checkin_time = Carbon::parse($this->business->halfDayStartTimeUsingWhichHalf(HalfDayType::SECOND_HALF));
            }
            if ($this->whichHalfDay == HalfDayType::SECOND_HALF) {
                $todays_last_checkin_time = Carbon::parse($this->business->halfDayStartTimeUsingWhichHalf(HalfDayType::FIRST_HALF));
            }
        } else {
            $last_checkin_time = (new TimeByBusiness())->getOfficeStartTimeByBusiness();
            if (is_null($last_checkin_time)) return Statuses::ON_TIME;
            $todays_last_checkin_time = Carbon::parse($last_checkin_time);
        }

        $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);
        if ($todays_checkin_time->greaterThan($todays_last_checkin_time)) return Statuses::LATE;
        return Statuses::ON_TIME;
    }
}
