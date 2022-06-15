<?php namespace Sheba\Business\Employee;

use App\Models\Business;
use App\Models\BusinessMember;
use Sheba\Business\ShiftAssignment\ShiftAssignmentFinder;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\ShiftAssignment\ShiftAssignment;

class AttendanceActionChecker
{
    /*** @var BusinessMember */
    private $businessMember;
    /*** @var Business */
    private $business;
    /*** @var ShiftAssignment | null */
    private $currentAssignment;
    /*** @var ShiftAssignmentFinder */
    private $shiftAssignmentFinder;
    /** @var Attendance */
    private $attendanceOfToday;
    /** @var Attendance */
    private $lastAttendance;

    public function __construct(ShiftAssignmentFinder $shift_assignment_finder)
    {
        $this->shiftAssignmentFinder = $shift_assignment_finder;
    }

    public function setBusinessMember(BusinessMember $business_member): AttendanceActionChecker
    {
        $this->businessMember = $business_member;
        $this->business = $this->businessMember->business;
        if ($this->business->isShiftEnabled()) $this->currentAssignment = $this->shiftAssignmentFinder->setBusinessMember($this->businessMember)->findCurrentAssignment();
        $this->attendanceOfToday = $this->businessMember->attendanceOfToday();
        $this->lastAttendance = $this->businessMember->lastAttendance();
        return $this;
    }

    public function getBusinessMember(): BusinessMember
    {
        return $this->businessMember;
    }

    public function canCheckIn(): bool
    {
        if (!$this->business->isShiftEnabled()) return $this->canCheckInForAttendance($this->attendanceOfToday);

        return $this->canCheckInForAttendance($this->currentAssignment->attendance);
    }

    /**
     * @param Attendance $attendance
     * @return bool
     */
    private function canCheckInForAttendance($attendance): bool
    {
        return !$attendance || $attendance->canTakeThisAction(Actions::CHECKIN);
    }

    /**
     * @param Attendance $attendance
     * @return bool
     */
    private function canCheckOutForAttendance($attendance): bool
    {
        return $attendance && $attendance->canTakeThisAction(Actions::CHECKOUT);
    }

    public function canCheckOut(): bool
    {
        if (!$this->business->isShiftEnabled()) return $this->canCheckOutForAttendance($this->attendanceOfToday);

        return $this->canCheckOutForAttendance($this->currentAssignment->attendance);
    }

    /**
     * @return ShiftAssignment | null
     */
    public function getCurrentAssignment()
    {
        return $this->currentAssignment;
    }

    public function getCheckInTime()
    {
        if ($this->business->isShiftEnabled() && $this->hasAttendance()) return $this->currentAssignment->attendance->getCheckInTime();
        else if ($this->hasAttendance()) return $this->attendanceOfToday->getCheckInTime();
        return null;
    }

    public function getCheckOutTime()
    {
        if ($this->business->isShiftEnabled() && $this->hasAttendance()) return $this->currentAssignment->attendance->getCheckOutTime();
        else if ($this->hasAttendance()) return $this->attendanceOfToday->getCheckOutTime();
        return null;
    }

    public function isRemoteAttendanceEnable(): bool
    {
        return $this->business->isRemoteAttendanceEnable($this->businessMember->id);
    }

    public function isGeoLocationAttendanceEnable(): bool
    {
        return $this->business->isGeoLocationAttendanceEnable();
    }

    public function isLiveTrackEnable(): bool
    {
        return $this->business->isLiveTrackEnabled();
    }

    public function hasAttendance(): bool
    {
        if ($this->business->isShiftEnabled() && $this->currentAssignment->attendance) return true;
        else if ($this->attendanceOfToday) return true;
        return false;
    }

/*
    public function hasLastAttendance(): bool
    {
        return !is_null($this->lastAttendance);
    }

    public function hasCheckIn()
    {
        if (!$this->business->isShiftEnabled()) return is_null($this->lastAttendance);

        return $this->canCheckInForAttendance($this->currentAssignment->attendance);
    }

    public function getLastAttendanceDate()
    {
        return $this->lastAttendance ? Carbon::parse($this->lastAttendance['date']): null;
    }*/
}
