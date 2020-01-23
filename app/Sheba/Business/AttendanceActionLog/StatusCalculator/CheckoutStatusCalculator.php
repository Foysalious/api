<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Carbon\Carbon;
use Sheba\Dal\Attendance\Statuses;

class CheckoutStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);
        $hour_spend_on_office = Carbon::now()->diffInHours($todays_checkin_time);
        if ($hour_spend_on_office < self::MINIMUM_HOURS_TO_STAY_IN_OFFICE)
            return Statuses::LEFT_EARLY;

        return Statuses::ON_TIME;
    }
}
