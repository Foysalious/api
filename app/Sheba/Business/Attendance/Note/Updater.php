<?php namespace App\Sheba\Business\Attendance\Note;

use App\Models\BusinessMember;
use Sheba\Dal\AttendanceActionLog\Contract as AttendanceActionLogRepositoryInterface;
use Sheba\ModificationFields;
use DB;

class Updater
{
    use ModificationFields;
    /** @var BusinessMember */
    private $businessMember;
    private $attendanceActionLogRepo;
    private $lastAttendance;
    private $lastAttendanceLog;
    private $action;
    private $note;
    private $member;


    /**
     * Updater constructor.
     * @param AttendanceActionLogRepositoryInterface $attendance_action_log_repo
     */
    public function __construct(AttendanceActionLogRepositoryInterface $attendance_action_log_repo)
    {
        $this->attendanceActionLogRepo = $attendance_action_log_repo;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->member = $business_member->member;
        $this->setLastAttendance();
        return $this;
    }

    /**
     * @param $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function updateNote()
    {
        $this->setModifier($this->member);
        if ($this->lastAttendanceLog['action'] === $this->action) {
            DB::transaction(function () {
                $data = [];
                $data['note'] = $this->note;
                $this->attendanceActionLogRepo->update($this->lastAttendanceLog, $this->withUpdateModificationField($data));
            });
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