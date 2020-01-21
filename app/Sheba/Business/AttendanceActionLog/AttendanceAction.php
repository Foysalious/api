<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use App\Models\BusinessMember;
use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
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
    /** @var Business */
    private $business;
    /** @var Carbon */
    private $today;
    /** @var EloquentImplementation */
    private $attendanceRepository;
    /** @var Attendance */
    private $attendance;
    private $attendanceCreator;
    private $attendanceActionLogCreator;
    private $action;
    private $note;
    private $deviceId;
    private $userAgent;

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

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function setDeviceId($device_id)
    {
        $this->deviceId = $device_id;
        return $this;
    }

    private function getIp()
    {
        return request()->ip();
    }


    private function setAttendance($attendance)
    {
        $this->attendance = $attendance;
        return $this;
    }

    public function doAction()
    {
        /** @var ActionChecker\ActionChecker $action */
        $action = $this->checkTheAction();
        if ($action->isSuccess()) $this->doDatabaseTransaction();
        return $action;
    }


    /**
     * @return ActionChecker\ActionChecker
     */
    public function checkTheAction()
    {
        $processor = new ActionProcessor();
        $action = $processor->setActionName($this->action)->getAction();
        $action->setAttendanceOfToday($this->attendance)->setIp($this->getIp())->setDeviceId($this->deviceId)->setBusiness($this->business);
        $action->check();
        return $action;
    }

    private function doDatabaseTransaction()
    {
        DB::transaction(function () {
            if (!$this->attendance) $this->createAttendance();
            $this->attendanceActionLogCreator->setAction($this->action)->setAttendance($this->attendance)->setIp($this->getIp())
                ->setDeviceId($this->deviceId)->setUserAgent($this->userAgent);
            if ($this->action == Actions::CHECKOUT) $this->attendanceActionLogCreator->setNote($this->note);
            $attendance_action_log = $this->attendanceActionLogCreator->create();
            $this->updateAttendance($attendance_action_log);
        });
    }

    private function createAttendance()
    {
        $attendance = $this->attendanceCreator->setBusinessMemberId($this->businessMember->id)->setDate(Carbon::now()->toDateString())->create();
        $this->setAttendance($attendance);
    }

    private function updateAttendance(AttendanceActionLog $model)
    {
        $this->attendance->status = $model->status;
        if ($this->action == Actions::CHECKOUT) {
            $this->attendance->checkout_time = $model->created_at->format('H:i:s');
            $this->attendance->staying_time_in_minutes = $model->created_at->diffInMinutes($this->attendance->checkin_time);
        }
        $this->attendance->update();
    }


}