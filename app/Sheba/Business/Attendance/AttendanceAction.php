<?php namespace Sheba\Business\Attendance;


use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;

class AttendanceAction
{
    /** @var BusinessMember */
    private $businessMember;
    /** @var Carbon */
    private $today;
    /** @var EloquentImplementation */
    private $attendanceRepository;
    /** @var Attendance */
    private $attendance;
    private $action;

    public function __construct(EloquentImplementation $attendance_repository)
    {
        $this->today = Carbon::now();
        $this->attendanceRepository = $attendance_repository;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function doAction()
    {
        $this->setAttendance();
        if ($this->canTakeThisAction()) {
          
        }
    }

    private function setAttendance()
    {
        $this->attendance = $this->businessMember->attendanceOfToday();

    }

    private function canTakeThisAction()
    {
        if ($this->attendance) return $this->action == Actions::CHECK_IN ? 0 : 1;
        return $this->action == Actions::CHECK_IN ? 1 : 0;
    }
}