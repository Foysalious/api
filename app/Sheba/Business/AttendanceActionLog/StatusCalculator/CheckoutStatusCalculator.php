<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\Time;
use Sheba\Dal\Attendance\Statuses;

class CheckoutStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $todays_checkout_time = Carbon::now();
        $last_checkout_time = Carbon::parse($todays_checkout_time->toDateString() . ' ' . Time::OFFICE_END_TIME);
        if ($todays_checkout_time->lt($last_checkout_time))
            return Statuses::LEFT_EARLY;

        return Statuses::ON_TIME;
    }
}
