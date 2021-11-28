<?php namespace Sheba\Business\MyTeamDashboard;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Dal\Attendance\Contract as AttendanceRepositoryInterface;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\Leave\Contract as LeaveRepositoryInterface;
use Sheba\Helpers\TimeFrame;

class AttendanceSummary
{
    /** @var Business */
    private $business;
    /** @var Carbon */
    private $startDate;
    /** @var Carbon */
    private $endDate;
    private $selectedDate;
    /** @var AttendanceRepositoryInterface $attendanceRepositoryInterface */
    private $attendanceRepositoryInterface;
    /** @var LeaveRepositoryInterface $leaveRepositoryInterface */
    private $leaveRepositoryInterface;
    private $commonFunctions;
    private $usersWhoOnLeave = [];
    private $usersLeaveIds = [];
    private $myTeam;
    private $onTime = 0;
    private $late = 0;
    private $leftTimely = 0;
    private $leftEarly = 0;

    /**
     * @param AttendanceRepositoryInterface $attendance_repository_interface
     * @param LeaveRepositoryInterface $leave_repository_interface
     * @param CommonFunctions $common_functions
     */
    public function __construct(AttendanceRepositoryInterface $attendance_repository_interface,
                                LeaveRepositoryInterface      $leave_repository_interface,
                                CommonFunctions               $common_functions)
    {
        $this->attendanceRepositoryInterface = $attendance_repository_interface;
        $this->leaveRepositoryInterface = $leave_repository_interface;
        $this->commonFunctions = $common_functions;
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
     * @param TimeFrame $selected_date
     * @return $this
     */
    public function setSelectedDate(TimeFrame $selected_date)
    {
        $this->selectedDate = $selected_date;
        $this->startDate = $selected_date->start;
        $this->endDate = $selected_date->end;
        return $this;
    }

    /**
     * @param $my_team
     * @return $this
     */
    public function setMyTeam($my_team)
    {
        $this->myTeam = $my_team;
        return $this;
    }

    /**
     * @return array
     */
    public function getSummary()
    {
        $business_member_ids = array_column($this->myTeam, 'id');
        $this->getBusinessMemberWhoAreOnLeave($business_member_ids);
        $is_weekend_or_holiday = $this->commonFunctions->setBusiness($this->business)->setSelectedDate($this->selectedDate)->isWeekendHoliday();
        $attendances = $this->getAttendanceInfo($business_member_ids);
        $attendances = $attendances->get();
        $present = $attendances->count();
        if (!$is_weekend_or_holiday) {
            foreach ($attendances as $attendance) {
                $is_half_day_leave = false;
                $is_on_leave = $this->isOnLeave($attendance->businessMember->id);
                if ($is_on_leave) {
                    $is_half_day_leave = $this->checkHalfDayLeave($attendance->businessMember->id);
                }
                if (!$is_on_leave || $is_half_day_leave ) {
                    foreach ($attendance->actions as $action) {
                        $this->incrementStatus($action->status);
                    }
                }
            }
        }
        $on_leave_and_absent = $this->getOnLeaveAndAbsentCount($business_member_ids, $attendances);
        $absent = sizeof($business_member_ids) - ($present + $on_leave_and_absent);

        return [
            'present' => $this->formatCount($present) ,
            'on_time' => $this->formatCount($this->onTime),
            'late' => $this->formatCount($this->late),
            'left_timely' => $this->formatCount($this->leftTimely),
            'left_early' => $this->formatCount($this->leftEarly),
            'on_leave' => $this->formatCount(sizeof($this->usersWhoOnLeave)),
            'absent' => $this->formatCount($is_weekend_or_holiday ? 0 : $absent)
        ];
    }

    /**
     * @param $status
     */
    private function incrementStatus($status) {
        if (Statuses::ON_TIME === $status) $this->onTime++;
        if (Statuses::LATE === $status) $this->late++;
        if (Statuses::LEFT_TIMELY === $status) $this->leftTimely++;
        if (Statuses::LEFT_EARLY === $status) $this->leftEarly++;
    }

    /**
     * @param $business_member_ids
     * @param $attendances
     * @return int
     */
    private function getOnLeaveAndAbsentCount($business_member_ids, $attendances) {
        $present_ids = [];
        $counter = 0;
        foreach ($attendances as $attendance) {
            array_push($present_ids, $attendance->businessMember->id);
        }
        $non_present_ids = array_diff($business_member_ids, $present_ids);

        foreach ($this->usersWhoOnLeave as $user) {
            if (in_array($user, $non_present_ids)) {
                $is_half_day_leave = $this->checkHalfDayLeave($user);
                if (!$is_half_day_leave) {
                    $counter++;
                }
            }
        }
        return $counter;
    }

    /**
     * @param $business_member_ids
     * @return mixed
     */
    private function getAttendanceInfo($business_member_ids) {
        return $this->attendanceRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'checkin_time', 'checkout_time', 'status', 'date')
            ->whereIn('business_member_id', $business_member_ids)
            ->where('date', '>=', $this->startDate->toDateString())
            ->where('date', '<=', $this->endDate->toDateString())
            ->with([
                'actions' => function ($q) {
                    $q->select('id', 'attendance_id', 'status', 'created_at');
                },
                'businessMember' => function ($q) {
                    $q->select('id', 'member_id', 'business_role_id', 'employee_id');
                }]);
    }

    /**
     * @param $business_member_ids
     */
    private function getBusinessMemberWhoAreOnLeave($business_member_ids)
    {
        $leaves = $this->leaveRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'end_date', 'status', 'is_half_day', 'half_day_configuration')
            ->whereIn('business_member_id', $business_member_ids)
            ->accepted()
            ->where('start_date', '<=', $this->startDate->toDateString())->where('end_date', '>=', $this->endDate->toDateString())
            ->with(['businessMember' => function ($q) {
                $q->select('id', 'member_id', 'business_role_id', 'employee_id');
            }]);
        $leaves = $leaves->get();

        foreach ($leaves as $leave) {
            array_push($this->usersWhoOnLeave, $leave->businessMember->id);
            $this->usersLeaveIds[$leave->businessMember->id] = [
                'member_id' => $leave->businessMember->member->id,
                'business_member_id' => $leave->businessMember->id,
                'leave' => [
                    'id' => $leave->id,
                    'is_half_day_leave' => (int)$leave->is_half_day,
                    'which_half_day' => $leave->is_half_day ? $leave->half_day_configuration : null
                ]
            ];
        }
    }

    /**
     * @param $business_member_id
     * @return bool
     */
    private function isOnLeave($business_member_id)
    {
        return in_array($business_member_id, $this->usersWhoOnLeave);
    }

    /**
     * @param $business_member_id
     * @return mixed
     */
    private function checkHalfDayLeave($business_member_id)
    {
        $leave = $this->usersLeaveIds[$business_member_id];
        return $leave['leave']['is_half_day_leave'];
    }

    /**
     * @param $count
     * @return mixed|string
     */
    private function formatCount($count)
    {
        return $count < 10 ? '0'.$count : $count;
    }

}