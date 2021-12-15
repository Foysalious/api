<?php namespace Sheba\Business\Leave\HalfDay;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Sheba\Helpers\TimeFrame;

class HalfDayLeaveCheck
{
    /** BusinessMember $businessMember */
    private $businessMember;

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @return string|null
     */
    public function checkHalfDayLeave()
    {
        $which_half_day = null;
        $leaves_date_with_half_and_full_day = $this->formatLeaveAsDateArray();
        if ($this->isHalfDayLeave(Carbon::now(), $leaves_date_with_half_and_full_day)) {
            $which_half_day = $this->whichHalfDayLeave(Carbon::now(), $leaves_date_with_half_and_full_day);
        }
        return $which_half_day;
    }

    /**
     * @return array
     */
    private function formatLeaveAsDateArray()
    {
        $year = date('Y');
        $month = date('m');
        $time_frame = (new TimeFrame)->forAMonth($month, $year);
        $business_member_leave = $this->businessMember->leaves()->accepted()->between($time_frame)->get();

        $business_member_leaves_date_with_half_and_full_day = [];
        $business_member_leave->each(function ($leave) use (&$business_member_leaves_date_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                $business_member_leaves_date_with_half_and_full_day[$date->toDateString()] = [
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });

        return $business_member_leaves_date_with_half_and_full_day;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return int
     */
    private function isHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['is_half_day_leave'] == 1) return 1;
        }
        return 0;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return string
     */
    private function whichHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['which_half_day'] == HalfDayType::FIRST_HALF) return HalfDayType::FIRST_HALF;
        }
        return HalfDayType::SECOND_HALF;
    }

    /**
     * @return bool
     */
    public function checkFullDayLeave()
    {
        $leaves_date_with_half_and_full_day = $this->formatLeaveAsDateArray();
        return $this->isFullDayLeave(Carbon::now(), $leaves_date_with_half_and_full_day);
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return bool
     */
    private function isFullDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['is_half_day_leave'] == 0) return true;
        }
        return false;
    }
}