<?php namespace Sheba\Business\Leave\Breakdown;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveBreakdown
{
    /**
     * @param $leaves
     * @return array
     */
    public function formatLeaveAsDateArray($leaves)
    {
        $business_member_leaves_date = [];
        $business_member_leaves_date_with_half_and_full_day = [];
        $leaves->each(function ($leave) use (&$business_member_leaves_date, &$business_member_leaves_date_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                array_push($business_member_leaves_date, $date->toDateString());
                $business_member_leaves_date_with_half_and_full_day[] = [
                    'date' => $date->toDateString(),
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });
        
        return [array_unique($business_member_leaves_date), $business_member_leaves_date_with_half_and_full_day];
    }

    /**
     * @param $leaves
     * @return array
     */
    private function formatLeaveAsDateArrayOld($leaves)
    {
        $business_member_leaves_date = [];
        $business_member_leaves_date_with_half_and_full_day = [];
        $leaves->each(function ($leave) use (&$business_member_leaves_date, &$business_member_leaves_date_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                array_push($business_member_leaves_date, $date->toDateString());
                $business_member_leaves_date_with_half_and_full_day[$date->toDateString()] = [
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });

        return [array_unique($business_member_leaves_date), $business_member_leaves_date_with_half_and_full_day];
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return int
     */
    public function isFullDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['is_half_day_leave'] == 0) return 1;
        }
        return 0;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return int
     */
    public function isHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
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
    public function whichHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['which_half_day'] == HalfDayType::FIRST_HALF) return HalfDayType::FIRST_HALF;
        }
        return HalfDayType::SECOND_HALF;
    }
}
