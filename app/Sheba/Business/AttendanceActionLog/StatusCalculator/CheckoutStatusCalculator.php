<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Sheba\Dal\Attendance\Statuses;
use Carbon\Carbon;

class CheckoutStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $checkout_time = $this->business->calculationTodayLastCheckOutTime($this->whichHalfDay);;
        if (is_null($checkout_time)) return Statuses::LEFT_TIMELY;

        $today_checkout_date = Carbon::now();
        $last_checkout_time = Carbon::parse($today_checkout_date->toDateString() . ' ' . $checkout_time);

        $today_checkout_date_without_second = Carbon::parse($today_checkout_date->format('Y-m-d H:i'));
        if ($today_checkout_date_without_second->lt($last_checkout_time)) return Statuses::LEFT_EARLY;
        return Statuses::LEFT_TIMELY;
    }
}
