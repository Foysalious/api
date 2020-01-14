<?php namespace Sheba\Business\AttendanceActionLog;


use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Business\AttendanceActionLog\Creator as AttendanceActionLogCreator;
use Sheba\Business\Attendance\Creator as AttendanceCreator;
use DB;

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
    private $attendanceCreator;
    private $attendanceActionLogCreator;
    private $action;

    public function __construct(EloquentImplementation $attendance_repository, AttendanceCreator $attendance_creator, AttendanceActionLogCreator $attendance_action_log_creator)
    {
        $this->today = Carbon::now();
        $this->attendanceRepository = $attendance_repository;
        $this->attendanceCreator = $attendance_creator;
        $this->attendanceActionLogCreator = $attendance_action_log_creator;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->setAttendance($this->businessMember->attendanceOfToday());
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function doAction()
    {
        if (!$this->canTakeThisAction()) return null;
        DB::transaction(function () {
            if (!$this->attendance) $this->createAttendance();
            $attendance_action_log = $this->attendanceActionLogCreator->setAction($this->action)->setAttendance($this->attendance)->create();
            $this->updateAttendance($attendance_action_log);
        });
        return true;
    }


    private function setAttendance($attendance)
    {
        $this->attendance = $attendance;
        return $this;
    }

    public function canTakeThisAction()
    {
        if ($this->attendance) return $this->action == Actions::CHECKIN ? 0 : 1;
        return $this->action == Actions::CHECKIN ? 1 : 0;
    }

    private function createAttendance()
    {
        $attendance = $this->attendanceCreator->setBusinessMemberId($this->businessMember->id)->setDate(Carbon::now()->toDateString())->create();
        $this->setAttendance($attendance);
    }

    private function updateAttendance(AttendanceActionLog $model)
    {
        $this->attendance->status = $model->status;
        if ($this->action == Actions::CHECKOUT) $this->attendance->checkout_time = $model->created_at->format('H:i:s');
        $this->attendance->update();
    }

}