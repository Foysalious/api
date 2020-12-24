<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\Leave\Model as Leave;

class TimeByBusiness
{
    public function getOfficeStartTimeByBusiness()
    {
        $now = Carbon::now();
        /** @var Business $business */
        $business = $this->getBusiness();
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember();

        $business_member_is_on_leaves = $business_member->isOnLeaves($now);
        if ($business_member_is_on_leaves) {
            /** @var Leave $leave */
            $leave = $business_member->getLeaveOnASpecificDate($now);

            if ($leave->is_half_day) {
                if ($leave->half_day_configuration == HalfDayType::FIRST_HALF) {
                    return $business->halfDayStartTimeUsingWhichHalf(HalfDayType::SECOND_HALF);
                } else {
                    return $business->halfDayStartTimeUsingWhichHalf(HalfDayType::FIRST_HALF);
                }
            }
        }

        $business_hour = BusinessOfficeHour::where('business_id', $this->getBusiness()->id)->first();
        if (is_null($business_hour)) return null;
        return $business_hour->start_time;
    }

    public function getOfficeEndTimeByBusiness()
    {
        $now = Carbon::now();
        /** @var Business $business */
        $business = $this->getBusiness();
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember();

        $business_member_is_on_leaves = $business_member->isOnLeaves($now);
        if ($business_member_is_on_leaves) {
            /** @var Leave $leave */
            $leave = $business_member->getLeaveOnASpecificDate($now);

            if ($leave->is_half_day) {
                if ($leave->half_day_configuration == HalfDayType::FIRST_HALF) {
                    return $business->halfDayEndTimeUsingWhichHalf(HalfDayType::SECOND_HALF);
                } else {
                    return $business->halfDayEndTimeUsingWhichHalf(HalfDayType::FIRST_HALF);
                }
            }
        }

        $business_hour = BusinessOfficeHour::where('business_id', $this->getBusiness()->id)->first();

        if (is_null($business_hour)) return null;
        return $business_hour->end_time;
    }

    private function getBusiness()
    {
        $auth_info = \request()->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return Business::find($business_member['business_id']);
    }

    public function getBusinessMember()
    {
        $auth_info = \request()->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return BusinessMember::findOrFail($business_member['id']);
    }
}
