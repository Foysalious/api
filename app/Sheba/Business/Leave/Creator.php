<?php namespace App\Sheba\Business\Leave;

use Carbon\Carbon;
use Sheba\Dal\Leave\EloquentImplementation as LeaveRepository;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Leave\Model as Leave;

class Creator
{
    private $title;
    private $businessMember;
    private $leaveTypeId;
    private $leaveRepository;
    private $now;
    private $startDate;
    private $endDate;
    private $totalDays;

    public function __construct(LeaveRepository $leave_repo, BusinessMemberRepositoryInterface $business_member_repo)
    {
        $this->leaveRepository = $leave_repo;
        $this->businessMemberRepository = $business_member_repo;
        $this->now = Carbon::now();
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setBusinessMember($businessMember)
    {
        $this->businessMember = $businessMember;
        return $this;
    }

    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveTypeId = $leave_type_id;
        return $this;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function setTotalDays()
    {
        $start_date = new \DateTime($this->startDate);
        $end_date = new \DateTime($this->endDate);
        $total_days = $start_date->diff($end_date)->format("%r%a") + 1.0;
        $this->totalDays = $total_days;
        return $this;
    }

    public function create()
    {
        $leave = $this->leaveRepository->create([
            'title' => $this->title,
            'business_member_id' => $this->businessMember->id,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'total_days' => $this->totalDays
        ]);
        $this->notifySuperAdmins($leave);
        return $leave;
    }

    private function notifySuperAdmins(Leave $leave)
    {
        $super_admins = $this->businessMemberRepository->where('is_super',1)
            ->where('business_id', $this->businessMember->business_id)->get();
        foreach ($super_admins as $super_admin) {
            $title = $this->businessMember->member->profile->name . ' #' . $this->businessMember->member->id . ' has created a Leave Request';
            notify()->member($super_admin->member)->send([
                'title' => $title,
                'type' => 'Info',
                'event_type' => 'Sheba\Dal\Leave\Model',
                'event_id' => $leave->id
            ]);
        }
    }
}