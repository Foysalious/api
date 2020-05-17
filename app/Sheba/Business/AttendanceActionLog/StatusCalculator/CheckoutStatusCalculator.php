<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\Time;
use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Dal\Attendance\Statuses;

class CheckoutStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $time = new TimeByBusiness();
        $checkout_time = $time->getOfficeEndTimeByBusiness();
        if (is_null($checkout_time)) return Statuses::ON_TIME;
        $todays_checkout_time = Carbon::now();
        $last_checkout_time = Carbon::parse($todays_checkout_time->toDateString() . ' ' . $checkout_time);
        if ($todays_checkout_time->lt($last_checkout_time))
            return Statuses::LEFT_EARLY;

        return Statuses::ON_TIME;
    }
}
