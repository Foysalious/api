<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use Sheba\Dal\Attendance\Statuses;
use Carbon\Carbon;

class ShiftCheckoutStatusCalculator extends ShiftStatusCalculator
{
    public function calculate()
    {
        $checkout_time = $this->businessMember->calculationTodayLastCheckOutTime($this->whichHalfDay, $this->shiftAssignment);
        if (is_null($checkout_time)) return Statuses::LEFT_TIMELY;
        $today_checkout_date = $this->newCheckoutTime ? Carbon::parse($this->attendance->date . ' ' . $this->newCheckoutTime): Carbon::now();
        $last_checkout_time = $this->checkoutTime ?: Carbon::parse($today_checkout_date->toDateString() . ' ' . $checkout_time);

        $today_checkout_date_without_second = Carbon::parse($today_checkout_date->format('Y-m-d H:i'));
        if ($today_checkout_date_without_second->lt($last_checkout_time)) return Statuses::LEFT_EARLY;
        return Statuses::LEFT_TIMELY;
    }
}
