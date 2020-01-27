<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Carbon\Carbon;
use Sheba\Dal\Attendance\Statuses;

class CheckoutStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        /**
         * THIS LOGIC BELONG TO 9 HOURS RULES
         *
         * $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);
        $hour_spend_on_office = Carbon::now()->diffInHours($todays_checkin_time);
        if ($hour_spend_on_office < self::MINIMUM_HOURS_TO_STAY_IN_OFFICE)
            return Statuses::LEFT_EARLY;*/

        $todays_checkout_time = Carbon::now();
        $last_checkout_time = Carbon::parse($todays_checkout_time->toDateString() . ' ' . self::LAST_CHECKOUT_TIME);
        if ($todays_checkout_time->lt($last_checkout_time))
            return Statuses::LEFT_EARLY;

        return Statuses::ON_TIME;
    }
}
