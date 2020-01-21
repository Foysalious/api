<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

use App\Models\Business;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Location\Geo;

abstract class ActionChecker
{
    /** @var Geo */
    protected $geo;
    /** @var Attendance */
    protected $attendanceOfToday;
    /** @var AttendanceActionLog[] */
    protected $attendanceLogsOfToday;
    /** @var Business */
    protected $business;
    protected $ip;
    protected $deviceId;
    protected $resultCode;
    protected $resultMessage;


    /**
     * @param Business $business
     * @return ActionChecker
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param Geo $geo
     * @return $this
     */
    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }


    public function setAttendanceOfToday($attendance)
    {
        $this->attendanceOfToday = $attendance;
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


    protected function setResultCode($code)
    {
        $this->resultCode = $code;
        return $this;
    }

    protected function setResultMessage($result_message)
    {
        $this->resultMessage = $result_message;
        return $this;
    }

    public function getResultCode()
    {
        return $this->resultCode;
    }

    public function getResultMessage()
    {
        return $this->resultMessage;
    }


    protected function checkIp()
    {
//        $this->business->offices()->where
    }

    public function check()
    {
        $this->setAttendanceActionLogsOfToday();
        $this->checkAlreadyHasActionForToday();
        $this->checkDeviceId();
    }

    private function setAttendanceActionLogsOfToday()
    {
        if ($this->attendanceOfToday) $this->attendanceLogsOfToday = $this->attendanceOfToday->actions;
    }

    protected function checkAlreadyHasActionForToday()
    {
        if (!$this->isSuccess()) return;
        if ($this->hasSameActionForToday()) {
            $this->setAlreadyHasActionForTodayResponse();
        } else {
            $this->setSuccessfulResponseMessage();
        }
    }

    private function hasSameActionForToday()
    {
        return $this->attendanceLogsOfToday ? $this->attendanceLogsOfToday->filter(function ($log) {
                return $log->action == $this->getActionName();
            })->count() > 0 : 0;
    }

    protected function checkDeviceId()
    {
        if (!$this->isSuccess()) return;
        if ($this->hasDifferentDeviceId()) {
            $this->setResult(ActionResultCodes::DEVICE_UNAUTHORIZED, ActionResultCodeMessages::DEVICE_UNAUTHORIZED);
        } else {
            $this->setSuccessfulResponseMessage();
        }
    }

    private function hasDifferentDeviceId()
    {
        return $this->attendanceLogsOfToday ? $this->attendanceLogsOfToday->filter(function ($log) {
                return $log->device_id != $this->deviceId;
            })->count() > 0 : 0;
    }

    protected function setResult($result_code, $result_message)
    {
        $this->setResultCode($result_code)->setResultMessage($result_message);
    }

    protected function setSuccessfulResponseMessage()
    {
        $this->setResult(ActionResultCodes::SUCCESSFUL, ActionResultCodeMessages::SUCCESSFUL);
    }

    public function isSuccess()
    {
        return $this->resultCode ? $this->resultCode == 200 : true;
    }

    abstract protected function setAlreadyHasActionForTodayResponse();

    abstract protected function getActionName();

}