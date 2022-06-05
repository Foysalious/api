<?php namespace Sheba\Business\Attendance;


use Carbon\Carbon;
use Sheba\Dal\Attendance\EloquentImplementation;

class Creator
{
    private $businessMemberId;
    private $date;
    /** @var EloquentImplementation */
    private $attendRepository;
    /** @var Carbon */
    private $now;
    private $shiftAssignmentId;

    public function __construct(EloquentImplementation $attend_repository)
    {
        $this->attendRepository = $attend_repository;
        $this->now = Carbon::now();
    }

    /**
     * @param mixed $businessMemberId
     * @return Creator
     */
    public function setBusinessMemberId($businessMemberId)
    {
        $this->businessMemberId = $businessMemberId;
        return $this;
    }

    /**
     * @param mixed $date
     * @return Creator
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setShiftAssignmentId($shift_assignment_id)
    {
        $this->shiftAssignmentId = $shift_assignment_id;
        return $this;
    }

    public function create()
    {
        return $this->attendRepository->create([
            'business_member_id' => $this->businessMemberId,
            'date' => $this->date,
            'shift_assignment_id' => $this->shiftAssignmentId,
            'checkin_time' => $this->now->format('H:i:s')
        ]);
    }
}