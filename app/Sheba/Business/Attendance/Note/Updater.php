<?php namespace App\Sheba\Business\Attendance\Note;

use App\Models\BusinessMember;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;

class Updater
{
    /** @var BusinessMember */
    private $businessMember;
    private $attendanceActionLog;
    private $lastAttendance;
    private $lastAttendanceLog;
    private $action;
    private $date;
    private $note;

    public function __construct(AttendanceActionLog $attendance_action_log)
    {
        $this->attendanceActionLog = $attendance_action_log;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->setLastAttendance();
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function doAction()
    {
        if ($this->lastAttendance['date'] === $this->date && $this->lastAttendanceLog['action'] === $this->action) {
            $data = [];
            $data['note'] = $this->note;
            $this->attendanceActionLog->update($this->lastAttendanceLog, $data);
        }
    }

    private function setLastAttendance()
    {
        $last_attendance = $this->businessMember->lastAttendance();
        $last_attendance_log = $last_attendance ? $last_attendance->actions()->get()->sortByDesc('id')->first() : null;
        $this->lastAttendance = $last_attendance;
        $this->lastAttendanceLog = $last_attendance_log;
    }
}