<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Location\Geo;

abstract class Action
{
    /** @var Geo */
    protected $geo;
    /** @var Attendance */
    protected $attendanceOfToday;
    protected $ip;
    protected $deviceId;
    /** @var ActionError */
    protected $actionError;

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function setDeviceId($device_id)
    {
        $this->deviceId = $device_id;
        return $this;
    }

    public function setAttendance($attendance)
    {
        $this->attendanceOfToday = $attendance;
        return $this;
    }

    /**
     * @return AttendanceActionLog|null
     */
    protected function getAttendanceActionLog()
    {
        return $this->attendanceOfToday->actions()->where('action', $this->getActionName())->first();
    }

    abstract protected function checkAlreadyHasActionForToday();


    protected function checkIp()
    {
    }

    protected function checkDeviceId()
    {

    }

    /**
     * @return ActionError
     */
    public function getError()
    {
        return $this->actionError;
    }

    abstract public function getActionName();

    abstract public function canTakeTheAction();

}