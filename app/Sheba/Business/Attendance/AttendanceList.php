<?php namespace Sheba\Business\Attendance;

use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Contract as AttendanceRepositoryInterface;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\AttendanceActionLog\Contract as AttendanceActionLogRepositoryInterface;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\Attendance\Model;
use Sheba\Dal\Attendance\Statuses;
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
    /** @var EloquentImplementation */
    private $attendRepository;

    /** @var AttendanceRepositoryInterface $attendanceRepositoryInterface */
    private $attendanceRepositoryInterface;
    /** @var AttendanceActionLogRepositoryInterface $attendanceActionLogRepositoryInterface */
    private $attendanceActionLogRepositoryInterface;

    /** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    private $businessDepartmentId;
    private $businessMemberId;
    private $status;
    /** @var BusinessDepartment[] */
    private $attendanceDepartments;

    public function __construct(EloquentImplementation $attend_repository,
                                AttendanceRepositoryInterface $attendance_repository_interface,
                                AttendanceActionLogRepositoryInterface $attendance_action_log_repository_interface,
                                BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->attendRepository = $attend_repository;
        $this->attendanceRepositoryInterface = $attendance_repository_interface;
        $this->attendanceActionLogRepositoryInterface = $attendance_action_log_repository_interface;
        $this->businessMemberRepository = $business_member_repository;
        $this->attendanceDepartments = collect();
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
     * @param Carbon $date
     * @return AttendanceList
     */
    public function setStartDate(Carbon $date)
    {
        $this->startDate = $date;
        return $this;
    }

    /**
     * @param Carbon $date
     * @return AttendanceList
     */
    public function setEndDate(Carbon $date)
    {
        $this->endDate = $date;
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
     * @return array
     */
    public function get()
    {
        $this->runAttendanceQueryV2();
        return $this->getDataV2();
    }


    private function runAttendanceQueryV2()
    {
        #dd($this->getStatus());
        $business_member_ids = [];
        if ($this->businessMemberId) $business_member_ids = [$this->businessMemberId];
        elseif ($this->business) $business_member_ids = $this->getBusinessMemberIds();
        $attendances = $this->attendRepository->builder()
            ->select('id', 'business_member_id', 'checkin_time', 'checkout_time', 'staying_time_in_minutes', 'status', 'date')
            ->whereIn('business_member_id', $business_member_ids)
            ->where('date', '>=', $this->startDate->toDateString())
            ->where('date', '<=', $this->endDate->toDateString())
            ->with([
                'actions' => function ($q) {
                    $q->select('id', 'attendance_id', 'note', 'action', 'status', 'is_remote', 'location', 'created_at');
                },
                'businessMember' => function ($q) {
                    $q->select('id', 'member_id', 'business_role_id')
                        ->with([
                            'member' => function ($q) {
                                $q->select('id', 'profile_id')
                                    ->with([
                                        'profile' => function ($q) {
                                            $q->select('id', 'name');
                                        }]);
                            }]);
                }]);

        if ($this->businessDepartmentId) {
            $role_ids = $this->getBusinessRoleIds();
            $attendances = $attendances->whereHas('businessMember', function ($q) use ($role_ids) {
                $q->whereIn('business_role_id', $role_ids);
            });
        }

        $this->attendances = $attendances->get();
    }

    private function runAttendanceQueryV1()
    {
        $business_member_ids = [];
        if ($this->businessMemberId) $business_member_ids = [$this->businessMemberId];
        elseif ($this->business) $business_member_ids = $this->getBusinessMemberIds();
        $attendances = $this->attendRepository->builder()
            ->select('id', 'business_member_id', 'checkin_time', 'checkout_time', 'staying_time_in_minutes', 'status', 'date')
            ->whereIn('business_member_id', $business_member_ids)
            ->where('date', '>=', $this->startDate->toDateString())
            ->where('date', '<=', $this->endDate->toDateString())
            ->with(['actions' => function ($q) {
                $q->select('id', 'attendance_id', 'note')->where('status', Statuses::LEFT_EARLY);
            }, 'businessMember' => function ($q) {
                $q->select('id', 'member_id', 'business_role_id')->with(['member' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }]);
            }])->whereIn('status', $this->getStatus());
        if ($this->businessDepartmentId) {
            $role_ids = $this->getBusinessRoleIds();
            $attendances = $attendances->whereHas('businessMember', function ($q) use ($role_ids) {
                $q->whereIn('business_role_id', $role_ids);
            });
        }

        $this->attendances = $attendances->get();
    }

    private function getBusinessMemberIds()
    {
        return $this->businessMemberRepository->where('business_id', $this->business->id)->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    private function getStatusV1()
    {
        if ($this->status) return [$this->status];
        return Statuses::get();
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
        if (count($this->attendances) == 0) return [];
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
            array_push($data, [
                'id' => $attendance->id,
                'member' => [
                    'id' => $attendance->businessMember->member->id,
                    'name' => $attendance->businessMember->member->profile->name
                ],
                'department' => $attendance->businessMember->role ? [
                    'id' => $attendance->businessMember->role->business_department_id,
                    'name' => $this->attendanceDepartments->where('id', $attendance->businessMember->role->business_department_id)->first()->name
                ] : null,
                'check_in' => $checkin_data,
                'check_out' => $checkout_data,
                'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                #'status' => $attendance->status,
                'date' => $attendance->date,
            ]);
        }
        return $data;
    }

    private function getDataV1()
    {
        if (count($this->attendances) == 0) return [];
        $data = [];
        $this->setDepartments();
        foreach ($this->attendances as $attendance) {
            $note = null;
            if ($attendance->status == Statuses::LEFT_EARLY) {
                $att = $attendance->actions->first();
                $note = $att ? $att->note : null;
            }
            array_push($data, [
                'id' => $attendance->id,
                'member' => [
                    'id' => $attendance->businessMember->member->id,
                    'name' => $attendance->businessMember->member->profile->name
                ],
                'department' => $attendance->businessMember->role ? [
                    'id' => $attendance->businessMember->role->business_department_id,
                    'name' => $this->attendanceDepartments->where('id', $attendance->businessMember->role->business_department_id)->first()->name
                ] : null,
                'date' => $attendance->date,
                'checkin_time' => Carbon::parse($attendance->date . ' ' . $attendance->checkin_time)->format('g:i a'),
                'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->date . ' ' . $attendance->checkout_time)->format('g:i a') : null,
                'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                'status' => $attendance->status,
                'note' => $note
            ]);
        }
        return $data;
    }

    private function setDepartments()
    {
        $roles = $this->attendances->pluck('businessMember.business_role_id');
        if (count($roles) == 0) return;
        $department_ids = BusinessRole::whereIn('id', $roles->toArray())->select('id', 'business_department_id')->get()->pluck('business_department_id')->toArray();
        $this->setAttendanceDepartments(BusinessDepartment::whereIn('id', $department_ids)->select('id', 'name')->get());
    }

    private function setAttendanceDepartments($departments)
    {
        $this->attendanceDepartments = $departments;
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