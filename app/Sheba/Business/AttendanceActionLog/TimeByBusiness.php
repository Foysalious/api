<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\BusinessOfficeHours\Model as BusinessOfficeHour;
use Sheba\Dal\Leave\Model as Leave;

class TimeByBusiness
{
    public function getOfficeStartTimeByBusiness($time = null, $business = null, $business_member = null)
    {
        $now = $time ? $time : Carbon::now();
        /** @var Business $business */
        $business = $business ? $business : $this->getBusiness();
        /** @var BusinessOfficeHour $office_hour */
        $office_hour = $business->officeHour;
        /** @var BusinessMember $business_member */
        $business_member = $business_member ? $business_member : $this->getBusinessMember();

        $business_member_is_on_leaves = $business_member->isOnLeaves($now);
        if ($business_member_is_on_leaves) {
            /** @var Leave $leave */
            $leave = $business_member->getLeaveOnASpecificDate($now);
            if ($leave->is_half_day) {
                if ($leave->half_day_configuration == HalfDayType::FIRST_HALF) {
                    $start_time = $business->halfDayStartTimeUsingWhichHalf(HalfDayType::SECOND_HALF);
                    if ($office_hour && $office_hour->is_start_grace_time_enable) {
                        return Carbon::parse($start_time)->addMinutes($office_hour->start_grace_time)->format('H:i:s');
                    }
                    return $start_time;
                } else {
                    $start_time = $business->halfDayStartTimeUsingWhichHalf(HalfDayType::FIRST_HALF);
                    if ($office_hour && $office_hour->is_start_grace_time_enable) {
                        return Carbon::parse($start_time)->addMinutes($office_hour->start_grace_time)->format('H:i:s');
                    }
                    return $start_time;
                }
            }
        }
        return $this->officeStartTime($office_hour);
    }

    private function officeStartTime($office_hour)
    {
        if (is_null($office_hour)) return null;
        if ($office_hour->is_start_grace_time_enable) return $this->officeStartTimeWithGraceTime($office_hour);
        return $office_hour->start_time;
    }

    /**
     * @param BusinessOfficeHour $business_hour
     * @return string
     */
    private function officeStartTimeWithGraceTime(BusinessOfficeHour $business_hour)
    {
        return Carbon::parse($business_hour->start_time)->addMinutes($business_hour->start_grace_time)->format('H:i:s');
    }

    public function getOfficeEndTimeByBusiness($time = null, $business = null, $business_member = null)
    {
        $now = $time ? $time : Carbon::now();
        /** @var Business $business */
        $business = $business ? $business : $this->getBusiness();
        /** @var BusinessOfficeHour $office_hour */
        $office_hour = $business->officeHour;
        /** @var BusinessMember $business_member */
        $business_member = $business_member ? $business_member : $this->getBusinessMember();

        $business_member_is_on_leaves = $business_member->isOnLeaves($now);
        if ($business_member_is_on_leaves) {
            /** @var Leave $leave */
            $leave = $business_member->getLeaveOnASpecificDate($now);

            if ($leave->is_half_day) {
                if ($leave->half_day_configuration == HalfDayType::FIRST_HALF) {
                    $end_time = $business->halfDayEndTimeUsingWhichHalf(HalfDayType::SECOND_HALF);
                    if ($office_hour->is_end_grace_time_enable) {
                        return Carbon::parse($end_time)->subMinutes($office_hour->end_grace_time)->format('H:i:s');
                    }
                    return $end_time;
                } else {
                    $end_time = $business->halfDayEndTimeUsingWhichHalf(HalfDayType::FIRST_HALF);
                    if ($office_hour->is_end_grace_time_enable) {
                        return Carbon::parse($end_time)->subMinutes($office_hour->end_grace_time)->format('H:i:s');
                    }
                    return $end_time;
                }
            }
        }
        return $this->officeEndTime($office_hour);
    }

    private function officeEndTime($office_hour)
    {
        if (is_null($office_hour)) return null;
        if ($office_hour->is_end_grace_time_enable) return $this->officeEndTimeWithGraceTime($office_hour);
        return $office_hour->end_time;
    }

    /**
     * @param BusinessOfficeHour $business_hour
     * @return string
     */
    private function officeEndTimeWithGraceTime(BusinessOfficeHour $business_hour)
    {
        return Carbon::parse($business_hour->end_time)->subMinutes($business_hour->end_grace_time)->format('H:i:s');
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
