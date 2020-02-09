<?php namespace App\Sheba\Leave;
use Carbon\Carbon;
use Sheba\Dal\Leave\EloquentImplementation as LeaveRepository;

class Creator
{
    private $title;
    private $businessMemberId;
    private $leaveTypeId;
    private $leaveRepository;
    private $now;

    public function __construct(LeaveRepository $leave_repo)
    {
        $this->leaveRepository = $leave_repo;
        $this->now = Carbon::now();
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setBusinessMemberId($businessMemberId)
    {
        $this->businessMemberId = $businessMemberId;
        return $this;
    }

    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveTypeId = $leave_type_id;
        return $this;
    }

    public function create()
    {
        return $this->leaveRepository->create([
            'title' => $this->title,
            'business_member_id' => $this->businessMemberId,
            'leave_type_id' => $this->leaveTypeId
        ]);
    }
}