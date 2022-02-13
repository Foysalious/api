<?php namespace App\Sheba\Business;

use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use App\Models\BusinessMember;
use Sheba\Helpers\TimeFrame;
use Carbon\CarbonPeriod;
use App\Models\Business;
use Carbon\Carbon;

class HolidayOrWeekendOrLeave
{

    private $businessWeekendRepo;
    private $businessHolidayRepo;
    private $businessMember;
    private $business;
    private $timeFrame;

    public function __construct()
    {
        $this->businessWeekendRepo = app(BusinessWeekendRepoInterface::class);
        $this->businessHolidayRepo = app(BusinessHolidayRepoInterface::class);
    }

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @param TimeFrame $time_frame
     * @return $this
     */
    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWeekendDays()
    {
        $business_weekend = $this->businessWeekendRepo->getAllByBusiness($this->business);
        return $business_weekend->pluck('weekday_name')->toArray();
    }

    /**
     * @return array
     */
    public function getDatesOfHolidays()
    {
        $business_holidays = $this->businessHolidayRepo->getAllByBusiness($this->business);
        $dates_of_holidays = [];
        foreach ($business_holidays as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $dates_of_holidays[] = $d->format('Y-m-d');
            }
        }
        return $dates_of_holidays;
    }

    /**
     * @return array
     */
    public function getDatesOfLeaveWithHalfDayLeavesInfo()
    {
        $leaves = $this->businessMember->leaves()->accepted()->startDateBetween($this->timeFrame)->endDateBetween($this->timeFrame)->get();

        $business_member_leaves_dates = [];
        $business_member_leaves_dates_with_half_and_full_day = [];
        $leaves->each(function ($leave) use (&$business_member_leaves_dates, &$business_member_leaves_dates_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                array_push($business_member_leaves_dates, $date->toDateString());
                $business_member_leaves_dates_with_half_and_full_day[$date->toDateString()] = [
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });

        return [array_unique($business_member_leaves_dates), $business_member_leaves_dates_with_half_and_full_day];
    }

    public function isWeekendHoliday($date, $weekend_days, $dates_of_holidays)
    {
        return $this->isWeekend($date, $weekend_days)
            || $this->isHoliday($date, $dates_of_holidays);

    }

    public function isOnLeave($date, $leaves)
    {
        return in_array($date->format('Y-m-d'), $leaves);
    }

    /**
     * @param Carbon $date
     * @param $weekend_days
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_days)
    {
        return in_array(strtolower($date->format('l')), $weekend_days);
    }

    /**
     * @param Carbon $date
     * @param $dates_of_holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $dates_of_holidays)
    {
        return in_array($date->format('Y-m-d'), $dates_of_holidays);
    }
}