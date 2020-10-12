<?php namespace Sheba\Business\AttendanceActionLog\StatusCalculator;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\Time;
use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Dal\Attendance\Statuses;

class CheckoutStatusCalculator extends StatusCalculator
{
    public function calculate()
    {
        $checkout_time = null;
        if ($this->whichHalfDay) {
            if ($this->whichHalfDay == HalfDayType::FIRST_HALF) {
                $checkout_time = Carbon::parse($this->business->halfDayEndTimeUsingWhichHalf(HalfDayType::SECOND_HALF));
            }
            if ($this->whichHalfDay == HalfDayType::SECOND_HALF) {
                $checkout_time = Carbon::parse($this->business->halfDayEndTimeUsingWhichHalf(HalfDayType::FIRST_HALF));
            }
        } else {
            $checkout_time = app(new TimeByBusiness())->getOfficeEndTimeByBusiness();
            if (is_null($checkout_time)) return Statuses::LEFT_TIMELY;
        }

        $todays_checkout_date = Carbon::now();
        $last_checkout_time = Carbon::parse($todays_checkout_date->toDateString() . ' ' . $checkout_time);
        if ($todays_checkout_date->lt($last_checkout_time)) return Statuses::LEFT_EARLY;
        return Statuses::LEFT_TIMELY;
    }
}
