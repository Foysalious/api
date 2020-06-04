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
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Contract as LeaveRepositoryInterface;
use Sheba\Dal\Attendance\Model;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\Leave\Status;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class AttendanceList
{
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
    private $businessMemberId;
    private $usersWhoGiveAttendance;
    /** @var Collection $departments */
    private $departments;

    const CHECKIN_TIME = 'checkin_time';
    const CHECKOUT_TIME = 'checkout_time';
    const STAYING_TIME = 'staying_time';

    public function __construct(AttendanceRepositoryInterface $attendance_repository_interface,
                                AttendanceActionLogRepositoryInterface $attendance_action_log_repository_interface,
                                BusinessMemberRepositoryInterface $business_member_repository,
                                LeaveRepositoryInterface $leave_repository_interface)
    {
        $this->attendanceRepositoryInterface = $attendance_repository_interface;
        $this->attendanceActionLogRepositoryInterface = $attendance_action_log_repository_interface;
        $this->businessMemberRepository = $business_member_repository;
        $this->leaveRepositoryInterface = $leave_repository_interface;
        $this->departments = collect();
        $this->usersWhoGiveAttendance = [];
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
                }]);
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

        foreach ($this->attendances as $attendance) {
            $checkin_data = $checkout_data = null;
            foreach ($attendance->actions as $action) {
                if ($action->action == Actions::CHECKIN) {
                    $checkin_data = collect([
                        'status' => $action->status,
                        'is_remote' => $action->is_remote ?: 0,
                        'address' => $action->is_remote ? json_decode($action->location)->address : null,
                        'checkin_time' => Carbon::parse($attendance->date . ' ' . $attendance->checkin_time)->format('g:i a'),
                    ]);
                }
                if ($action->action == Actions::CHECKOUT) {
                    $checkout_data = collect([
                        'status' => $action->status,
                        'note' => $action->note,
                        'is_remote' => $action->is_remote ?: 0,
                        'address' => $action->is_remote ? json_decode($action->location)->address : null,
                        'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->date . ' ' . $attendance->checkout_time)->format('g:i a') : null,
                    ]);
                }
            }
            array_push($this->usersWhoGiveAttendance, $attendance->businessMember->id);
            array_push($data, [
                'id' => $attendance->id,
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
                'is_absent' => $attendance->status == Statuses::ABSENT ? 1 : 0,
                'is_on_leave' => 0,
                'date' => $attendance->date
            ]);
        }

        $business_member_in_leave = [];
        if (!$this->checkinStatus && !$this->checkoutStatus) {
            $business_member_in_leave = $this->getBusinessMemberWhoAreOnLeave();
        }

        $final_data = array_merge($data, $business_member_in_leave);

        if ($this->search)
            $final_data = collect($this->searchWithEmployeeName($final_data))->values();

        return $final_data;
    }

    private function searchWithEmployeeName($final_data)
    {
        return array_where($final_data, function ($key, $value) {
            return str_contains(strtoupper($value['member']['name']), strtoupper($this->search));
        });
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
            ->with(['businessMember' => function ($q) {$this->withMembers($q);}])
            ->get();

        $data = [];

        foreach ($leaves as $leave) {
            if (in_array($leave->businessMember->id, $this->usersWhoGiveAttendance)) continue;
            array_push($data, [
                'id' => $leave->id,
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
                'is_absent' => 0,
                'is_on_leave' => 1,
                'date' => null
            ]);
        }

        return $data;
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
}
