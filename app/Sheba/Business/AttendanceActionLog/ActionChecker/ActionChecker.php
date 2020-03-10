<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\Time;
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


    public function check()
    {
        $this->setAttendanceActionLogsOfToday();
        $this->checkAlreadyHasActionForToday();
        $this->checkDeviceId();
        $this->checkIp();
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
            })->count() > 0 : false;
    }

    protected function checkDeviceId()
    {
        if (!$this->isSuccess()) return;
        if ($this->hasDifferentDeviceId()) {
            $this->setResult(ActionResultCodes::DEVICE_UNAUTHORIZED, ActionResultCodeMessages::DEVICE_UNAUTHORIZED);
        } elseif ($this->hasDeviceUsedInDifferentAccountToday()) {
            $this->setResult(ActionResultCodes::ALREADY_DEVICE_USED, ActionResultCodeMessages::ALREADY_DEVICE_USED);
        } else {
            $this->setSuccessfulResponseMessage();
        }
    }

    private function hasDifferentDeviceId()
    {
        return $this->attendanceLogsOfToday ? $this->attendanceLogsOfToday->filter(function ($log) {
                return $log->device_id != $this->deviceId;
            })->count() > 0 : false;
    }

    private function hasDeviceUsedInDifferentAccountToday()
    {
        $business_members = $this->business->members()->select('business_member.id');
        if ($this->attendanceOfToday) $business_members = $business_members->where('business_member.id', '<>', $this->attendanceOfToday->business_member_id);
        $business_members = $business_members->get();
        if (count($business_members) == 0) return 0;
        $attendances_count = Attendance::whereIn('business_member_id', $business_members->pluck('id'))->where('date', date('Y-m-d'))->whereHas('actions', function ($q) {
            $q->where('device_id', $this->deviceId);
        })->select('id')->count();
        return $attendances_count > 0 ? 1 : 0;
    }

    protected function checkIp()
    {
        if (!$this->isSuccess()) return;
        if ($this->business->offices()->count() > 0 && !in_array($this->ip, $this->business->offices()->select('ip')->get()->pluck('ip')->toArray())) {
            $this->setResult(ActionResultCodes::OUT_OF_WIFI_AREA, ActionResultCodeMessages::OUT_OF_WIFI_AREA);
        } else {
            $this->setSuccessfulResponseMessage();
        }
    }

    protected function setResult($result_code, $result_message)
    {
        $this->setResultCode($result_code)->setResultMessage($result_message);
    }

    public function isSuccess()
    {
        return $this->resultCode ? in_array($this->resultCode, [ActionResultCodes::SUCCESSFUL, ActionResultCodes::LATE_TODAY]) : true;
    }

    public function isNoteRequired()
    {
        return Carbon::now()->lt(Carbon::parse(Time::OFFICE_END_TIME)) ? 1 : 0;
    }

    abstract protected function setSuccessfulResponseMessage();

    abstract protected function setAlreadyHasActionForTodayResponse();

    abstract protected function getActionName();
}
