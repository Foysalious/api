<?php namespace Sheba\Business\Attendance;


use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Model;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class DailyStat
{
    /** @var Business */
    private $business;
    /** @var Carbon */
    private $date;
    /** @var Model[] */
    private $attendances;
    /** @var EloquentImplementation */
    private $attendRepository;
    /** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepository;
    private $businessDepartmentId;
    private $status;
    /** @var BusinessDepartment[] */
    private $attendanceDepartments;

    public function __construct(EloquentImplementation $attend_repository, BusinessMemberRepositoryInterface $business_member_repository)
    {
        $this->attendRepository = $attend_repository;
        $this->businessMemberRepository = $business_member_repository;
        $this->attendanceDepartments = collect();
    }

    /**
     * @param Business $business
     * @return DailyStat
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param Carbon $date
     * @return DailyStat
     */
    public function setDate(Carbon $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @param $businessDepartmentId
     * @return DailyStat
     */
    public function setBusinessDepartment($businessDepartmentId)
    {
        $this->businessDepartmentId = $businessDepartmentId;
        return $this;
    }

    /**
     * @param mixed $status
     * @return DailyStat
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
        $this->runAttendanceQuery();
        return $this->getData();
    }

    private function runAttendanceQuery()
    {
        $business_member_ids = $this->getBusinessMemberIds();
        $attendances = $this->attendRepository->where('date', $this->date->toDateString())->whereIn('business_member_id', $business_member_ids)
            ->select('id', 'business_member_id', 'checkin_time', 'checkout_time', 'staying_time_in_minutes', 'status')
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
            if ($role_ids = $this->getBusinessRoleIds()) {
                $attendances = $attendances->whereHas('businessMember', function ($q) use ($role_ids) {
                    $q->whereIn('business_role_id', $role_ids->toArray());
                });
            }
        }
        $this->attendances = $attendances->get();
    }

    private function getBusinessMemberIds()
    {
        return $this->businessMemberRepository->where('business_id', $this->business->id)->select('id')->get()->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    private function getStatus()
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
        $role_ids = BusinessRole::select('id', 'business_department_id')->whereHas('businessDepartmentId', function ($q) {
            $q->where([['business_id', $this->business->id], ['business_departments.id', $this->businessDepartmentId]]);
        })->get();
        return count($role_ids) > 0 ? $role_ids->pluck('id')->toArray() : null;
    }

    private function getData()
    {
        if (count($this->attendances) == 0) return [];
        $data = [];
        $this->setDepartments();
        foreach ($this->attendances as $attendance) {
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
                'checkin_time' => Carbon::parse($attendance->date . ' ' . $attendance->checkin_time)->format('g:i a'),
                'checkout_time' => $attendance->checkout_time ? Carbon::parse($attendance->date . ' ' . $attendance->checkout_time)->format('g:i a') : null,
                'active_hours' => $attendance->staying_time_in_minutes ? $this->formatMinute($attendance->staying_time_in_minutes) : null,
                'status' => $attendance->status,
                'note' => $attendance->status == Statuses::LEFT_EARLY ? $attendance->actions->first()->note : null
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