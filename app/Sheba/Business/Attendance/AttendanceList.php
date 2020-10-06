<?php namespace Sheba\Business\Attendance;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Contract as AttendanceRepositoryInterface;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\AttendanceActionLog\Contract as AttendanceActionLogRepositoryInterface;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Contract as LeaveRepositoryInterface;
use Sheba\Dal\Attendance\Model;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\Leave\Status;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class AttendanceList
{
    const PRESENT = 'present';
    const ABSENT = 'absent';
    const ON_LEAVE = 'on_leave';

    /** @var Business */
    private $business;
    /** @var Carbon */
    private $startDate;
    /** @var Carbon */
    private $endDate;
    /** @var Model[] */
    private $attendances;
    /** @var AttendanceRepositoryInterface $attendanceRepositoryInterface */
    private $attendanceRepositoryInterface;
    /** @var AttendanceActionLogRepositoryInterface $attendanceActionLogRepositoryInterface */
    private $attendanceActionLogRepositoryInterface;
    /** @var LeaveRepositoryInterface $leaveRepositoryInterface */
    private $leaveRepositoryInterface;
    /** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    private $businessDepartmentId;
    private $sort;
    private $sortColumn;
    private $search;
    private $checkinStatus;
    private $checkoutStatus;
    private $status;
    private $statusFilter;
    private $businessMemberId;
    private $usersWhoGiveAttendance;
    private $usersWhoOnLeave;
    /** @var Collection $departments */
    private $departments;
    /** @var BusinessHolidayRepoInterface $businessHoliday */
    private $businessHoliday;
    /** @var BusinessWeekendRepoInterface $businessWeekend */
    private $businessWeekend;

    const CHECKIN_TIME = 'checkin_time';
    const CHECKOUT_TIME = 'checkout_time';
    const STAYING_TIME = 'staying_time';

    /**
     * AttendanceList constructor.
     * @param AttendanceRepositoryInterface $attendance_repository_interface
     * @param AttendanceActionLogRepositoryInterface $attendance_action_log_repository_interface
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param LeaveRepositoryInterface $leave_repository_interface
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     */
    public function __construct(AttendanceRepositoryInterface $attendance_repository_interface,
                                AttendanceActionLogRepositoryInterface $attendance_action_log_repository_interface,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                LeaveRepositoryInterface $leave_repository_interface,
                                BusinessHolidayRepoInterface $business_holiday_repo,
                                BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $this->attendanceRepositoryInterface = $attendance_repository_interface;
        $this->attendanceActionLogRepositoryInterface = $attendance_action_log_repository_interface;
        $this->businessMemberRepository = $business_member_repository;
        $this->leaveRepositoryInterface = $leave_repository_interface;
        $this->departments = collect();
        $this->usersWhoGiveAttendance = [];
        $this->usersWhoOnLeave = [];
        $this->businessHoliday = $business_holiday_repo;
        $this->businessWeekend = $business_weekend_repo;
    }

    /**
     * @param Business $business
     * @return AttendanceList
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param TimeFrame $selected_date
     * @return AttendanceList
     */
    public function setSelectedDate(TimeFrame $selected_date)
    {
        $this->startDate = $selected_date->start;
        $this->endDate = $selected_date->end;
        return $this;
    }

    /**
     * @param $businessDepartmentId
     * @return AttendanceList
     */
    public function setBusinessDepartment($businessDepartmentId)
    {
        $this->businessDepartmentId = $businessDepartmentId;
        return $this;
    }

    /**
     * @param $sort
     * @return $this
     */
    public function setSortKey($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * @param $column
     * @return $this
     */
    public function setSortColumn($column)
    {
        $this->sortColumn = $column;
        return $this;
    }

    /**
     * @param $status_filter
     * @return $this
     */
    public function setStatusFilter($status_filter)
    {
        $this->statusFilter = $status_filter;
        return $this;
    }

    /**
     * @param $businessMemberId
     * @return AttendanceList
     */
    public function setBusinessMemberId($businessMemberId)
    {
        $this->businessMemberId = $businessMemberId;
        return $this;
    }

    /**
     * @param mixed $status
     * @return AttendanceList
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param $search
     * @return $this
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * @param $checkin_status
     * @return $this
     */
    public function setCheckinStatus($checkin_status)
    {
        $this->checkinStatus = $checkin_status;
        return $this;
    }

    /**
     * @param $checkout_status
     * @return $this
     */
    public function setCheckoutStatus($checkout_status)
    {
        $this->checkoutStatus = $checkout_status;
        return $this;
    }

    /**
     * @return array
     */
    public function get()
    {
        $this->runAttendanceQueryV2();
        return $this->getDataV2();
    }

    private function withMembers($query)
    {
        return $query->select('id', 'member_id', 'business_role_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'profile_id')
                        ->with([
                            'profile' => function ($q) {
                                $q->select('id', 'name');
                            }]);
                }, 'role']);
    }

    private function runAttendanceQueryV2()
    {
        $business_member_ids = [];
        if ($this->businessMemberId) $business_member_ids = [$this->businessMemberId];
        elseif ($this->business) $business_member_ids = $this->getBusinessMemberIds();
        $attendances = $this->attendanceRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'checkin_time', 'checkout_time', 'staying_time_in_minutes', 'status', 'date')
            ->whereIn('business_member_id', $business_member_ids)
            ->where('date', '>=', $this->startDate->toDateString())
            ->where('date', '<=', $this->endDate->toDateString())
            ->with([
                'actions' => function ($q) {
                    $q->select('id', 'attendance_id', 'note', 'action', 'status', 'is_remote', 'location', 'created_at');
                },
                'businessMember' => function ($q) {
                    $this->withMembers($q);
                }]);

        if ($this->businessDepartmentId) {
            $role_ids = $this->getBusinessRoleIds();
            $attendances = $attendances->whereHas('businessMember', function ($q) use ($role_ids) {
                $q->whereIn('business_role_id', $role_ids);
            });
        }

        if ($this->checkinStatus) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where('status', $this->checkinStatus);
            });
        }

        if ($this->checkoutStatus) {
            $attendances = $attendances->whereHas('actions', function ($q) {
                $q->where('status', $this->checkoutStatus);
            });
        }

        if ($this->sort && $this->sortColumn) {
            $sort_by = $this->sort === 'asc' ? 'ASC' : 'DESC';
            if ($this->sortColumn == self::CHECKIN_TIME) {
                $attendances = $attendances->orderByRaw("UNIX_TIMESTAMP(checkin_time) $sort_by");
            }
            if ($this->sortColumn == self::CHECKOUT_TIME) {
                $attendances = $attendances->orderByRaw("UNIX_TIMESTAMP(checkout_time) $sort_by");
            }
            if ($this->sortColumn == self::STAYING_TIME) {
                $attendances = $attendances->orderByRaw("staying_time_in_minutes $sort_by");
            }
        } else {
            $attendances = $attendances->orderByRaw('id desc');
        }

        $this->attendances = $attendances->get();
    }

    private function getBusinessMemberIds()
    {
        return $this->businessMemberRepository->where('business_id', $this->business->id)->pluck('id')->toArray();
    }

    /**
     * @return array|null
     */
    private function getBusinessRoleIds()
    {
        /** @var Collection $role_ids */
        $role_ids = BusinessRole::select('id', 'business_department_id')->whereHas('businessDepartment', function ($q) {
            $q->where([['business_id', $this->business->id], ['business_departments.id', $this->businessDepartmentId]]);
        })->get();
        return count($role_ids) > 0 ? $role_ids->pluck('id')->toArray() : [];
    }

    private function getDataV2()
    {
        $data = [];
        $this->setDepartments();

        $is_weekend_or_holiday = $this->isWeekendHolidayLeave();
        $business_members_in_leave = $this->getBusinessMemberWhoAreOnLeave();

        foreach ($this->attendances as $attendance) {
            $checkin_data = $checkout_data = null;
            $is_on_leave = $this->isOnLeave($attendance->businessMember->member->id);
            array_push($this->usersWhoGiveAttendance, $attendance->businessMember->member->id);
            if ($is_on_leave && (!!$this->checkinStatus || !!$this->checkoutStatus)) continue;

            foreach ($attendance->actions as $action) {
                if ($action->action == Actions::CHECKIN) {
                    $checkin_data = collect([
                        'status' => $is_weekend_or_holiday || $is_on_leave ? null : $action->status,
                        'is_remote' => $action->is_remote ?: 0,
                        'address' => $action->is_remote ? json_decode($action->location)->address : null,
                        'checkin_time' => Carbon::parse($attendance->date . ' ' . $attendance->checkin_time)->format('g:i a')
                    ]);
                }
                if ($action->action == Actions::CHECKOUT) {
                    $checkout_data = collect([
                        'status' => $is_weekend_or_holiday || $is_on_leave ? null : $action->status,
                        'is_remote' => $action->is_remote ?: 0,
                        'address' => $action->is_remote ? json_decode($action->location)->address : null,
                        'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->date . ' ' . $attendance->checkout_time)->format('g:i a') : null,
                        'note' => $action->note
                    ]);
                }
            }
            array_push($data, [
                'id' => $attendance->id,
                'business_member_id' => $attendance->businessMember->id,
                'member' => [
                    'id' => $attendance->businessMember->member->id,
                    'name' => $attendance->businessMember->member->profile->name
                ],
                'department' => $attendance->businessMember->role ? [
                    'id' => $attendance->businessMember->role->business_department_id,
                    'name' => $this->departments->where('id', $attendance->businessMember->role->business_department_id)->first()->name
                ] : null,
                'check_in' => $checkin_data,
                'check_out' => $checkout_data,
                'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                'is_half_day' => 0,
                'half_day_configuration' => null,
                'is_present' => $this->isPresent($attendance),
                'is_holiday' => $is_weekend_or_holiday ? 1 : 0,
                'is_absent' => 0,
                'is_on_leave' => 0,
                'date' => $attendance->date
            ]);
        }
        dd($business_members_in_leave);
        foreach ($business_members_in_leave as $index => $business_member_in_leave) {
            if (in_array($business_member_in_leave['member']['id'], $this->usersWhoGiveAttendance)) {
                unset($business_members_in_leave[$index]);
            }
        }

        $present_and_on_leave_business_members = array_merge($data, $business_members_in_leave);
        $business_members_in_absence = $this->getBusinessMemberWhoAreAbsence($present_and_on_leave_business_members);

        $final_data = array_merge($present_and_on_leave_business_members, $business_members_in_absence);
        if ($this->search)
            $final_data = collect($this->searchWithEmployeeName($final_data))->values();

        if ($this->statusFilter)
            $final_data = $this->filterWithStatus($final_data)->values();

        return $final_data;
    }

    private function getBusinessMemberWhoAreAbsence($present_and_on_leave_business_members)
    {
        $is_weekend_or_holiday = $this->isWeekendHolidayLeave();
        $business_member_ids = [];
        $present_and_on_leave_business_member_ids = array_map(function ($business_member) use ($business_member_ids) {
            return $business_member_ids[] = $business_member['business_member_id'];
        }, $present_and_on_leave_business_members);
        $business_members = $this->businessMemberRepository->builder()->select('id', 'member_id', 'business_role_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'profile_id')
                        ->with([
                            'profile' => function ($q) {
                                $q->select('id', 'name');
                            }]);
                }, 'role'])->where('business_id', $this->business->id)
            ->active()
            ->whereNotIn('id', $present_and_on_leave_business_member_ids)->get();

        $data = [];
        foreach ($business_members as $business_member) {
            array_push($data, [
                'id' => $business_member->id,
                'business_member_id' => $business_member->id,
                'member' => [
                    'id' => $business_member->member->id,
                    'name' => $business_member->member->profile->name
                ],
                'department' => $business_member->role ? [
                    'id' => $business_member->role->business_department_id,
                    'name' => $this->departments->where('id', $business_member->role->business_department_id)->first() ?
                        $this->departments->where('id', $business_member->role->business_department_id)->first()->name :
                        'n/s'
                ] : null,
                'check_in' => null,
                'check_out' => null,
                'active_hours' => null,
                'is_half_day' => 0,
                'half_day_configuration' => null,
                'is_present' => 0,
                'is_holiday' => $is_weekend_or_holiday ? 1 : 0,
                'is_absent' => 1,
                'is_on_leave' => 0,
                'date' => null
            ]);
        }
        return $data;
    }

    private function getBusinessMemberWhoAreOnLeave()
    {
        $business_member_ids = [];
        if ($this->businessMemberId) $business_member_ids = [$this->businessMemberId];
        elseif ($this->business) $business_member_ids = $this->getBusinessMemberIds();
        $leaves = $this->leaveRepositoryInterface->builder()
            ->select('id', 'business_member_id', 'end_date', 'status')
            ->whereIn('business_member_id', $business_member_ids)
            ->accepted()
            ->where('start_date', '<=', $this->startDate->toDateString())->where('end_date', '>=', $this->endDate->toDateString())
            ->with(['businessMember' => function ($q) {
                $this->withMembers($q);
            }])
            ->get();

        $data = [];

        foreach ($leaves as $leave) {
            array_push($this->usersWhoOnLeave, $leave->businessMember->member->id);
            if (!!$this->checkinStatus || !!$this->checkoutStatus) continue;
            array_push($data, [
                'id' => $leave->id,
                'business_member_id' => $leave->businessMember->id,
                'member' => [
                    'id' => $leave->businessMember->member->id,
                    'name' => $leave->businessMember->member->profile->name
                ],
                'department' => $leave->businessMember->role ? [
                    'id' => $leave->businessMember->role->business_department_id,
                    'name' => $this->departments->where('id', $leave->businessMember->role->business_department_id)->first()->name
                ] : null,
                'check_in' => null,
                'check_out' => null,
                'active_hours' => null,
                'is_half_day' => $leave->is_half_day ? 1 : 0,
                'half_day_configuration' => $leave->is_half_day ? $leave->half_day_configuration : null,
                'is_present' => 0,
                'is_holiday' => 0,
                'is_absent' => 0,
                'is_on_leave' => 1,
                'date' => null
            ]);
        }

        return $data;
    }

    private function isPresent($attendance)
    {
        $status = null;
        foreach ($attendance->actions as $action) {
            if ($action->action == Actions::CHECKIN) {
                $status = $action->status;
            }
        }
        if ($status == Statuses::ON_TIME) return 1;
        if ($status == Statuses::LATE) return 1;
        return 0;
    }

    private function searchWithEmployeeName($final_data)
    {
        return array_where($final_data, function ($key, $value) {
            return str_contains(strtoupper($value['member']['name']), strtoupper($this->search));
        });
    }

    private function setDepartments()
    {
        $this->departments = BusinessDepartment::where('business_id', $this->business->id)->select('id', 'name')->get();
        return $this;
    }

    private function formatMinute($minute)
    {
        if ($minute < 60) return "$minute min";
        $hour = $minute / 60;
        $intval_hr = intval($hour);
        $text = "$intval_hr hr ";
        if ($hour > $intval_hr) $text .= ($minute - (60 * intval($hour))) . " min";

        return $text;
    }

    private function isWeekendHolidayLeave()
    {
        $business_weekend = $this->businessWeekend->getAllByBusiness($this->business);
        $business_holiday = $this->businessHoliday->getAllByBusiness($this->business);

        $dates_of_holidays_formatted = [];
        $weekend_day = $business_weekend->pluck('weekday_name')->toArray();
        foreach ($business_holiday as $holiday) {
            $start_date = Carbon::parse($holiday->start_date);
            $end_date = Carbon::parse($holiday->end_date);
            for ($d = $start_date; $d->lte($end_date); $d->addDay()) {
                $dates_of_holidays_formatted[] = $d->format('Y-m-d');
            }
        }

        return $this->isWeekend($this->startDate, $weekend_day)
            || $this->isHoliday($this->startDate, $dates_of_holidays_formatted);
    }

    /**
     * @param Carbon $date
     * @param $weekend_day
     * @return bool
     */
    private function isWeekend(Carbon $date, $weekend_day)
    {
        return in_array(strtolower($date->format('l')), $weekend_day);
    }

    /**
     * @param Carbon $date
     * @param $holidays
     * @return bool
     */
    private function isHoliday(Carbon $date, $holidays)
    {
        return in_array($date->format('Y-m-d'), $holidays);
    }

    private function isOnLeave($member_id)
    {
        return in_array($member_id, $this->usersWhoOnLeave);
    }

    private function filterWithStatus($final_data)
    {
        if ($this->statusFilter == self::PRESENT) {
            $final_data = collect($final_data)->filter(function ($data) {
                return $data['is_present'] == 1;
            });
        }
        if ($this->statusFilter == self::ABSENT) {
            $final_data = collect($final_data)->filter(function ($data) {
                return $data['is_absent'] == 1;
            });
        }
        if ($this->statusFilter == self::ON_LEAVE) {
            $final_data = collect($final_data)->filter(function ($data) {
                return $data['is_on_leave'] == 1;
            });
        }
        return $final_data;
    }
}
