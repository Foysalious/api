<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Business\AttendanceActionLog\WeekendHolidayByBusiness;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\Attendance\Model as Attendance;
use App\Models\BusinessMember;
use App\Models\Business;
use Sheba\Location\Geo;
use Carbon\Carbon;

abstract class ActionChecker
{
    /** @var Geo */
    protected $geo;
    /** @var Attendance */
    protected $attendanceOfToday;
    /** @var AttendanceActionLog[] */
    protected $attendanceLogsOfToday;
    /** @var Business $business */
    protected $business;
    /** @var BusinessMember $businessMember */
    protected $businessMember;
    protected $ip;
    protected $deviceId;
    protected $resultCode;
    protected $resultMessage;
    protected $isRemote = 0;
    const BUSINESS_OFFICE_HOUR = 1;

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
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
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

    public function getIsRemote()
    {
        return $this->isRemote;
    }

    public function check()
    {
        $this->setAttendanceActionLogsOfToday();
        $this->checkAlreadyHasActionForToday();
        $this->checkDeviceId();
        $this->checkIpOrRemote();
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

    protected function checkIpOrRemote()
    {
        if (!$this->isSuccess()) return;
        if ($this->business->isIpBasedAttendanceEnable()) {
            $office_ip_count = $this->business->offices()->count();
            if ($office_ip_count > 0 && !$this->isInWifiArea()) {
                if ($this->business->isRemoteAttendanceEnable($this->businessMember->id)) {
                    $this->remoteAttendance();
                } else {
                    $this->setResult(ActionResultCodes::OUT_OF_WIFI_AREA, ActionResultCodeMessages::OUT_OF_WIFI_AREA);
                }
            } else {
                if ($office_ip_count < 1) $this->isRemote = 1;
                $this->setSuccessfulResponseMessage();
            }
        } else {
            $this->remoteAttendance();
        }
    }

    private function remoteAttendance()
    {
        if ($this->business->isRemoteAttendanceEnable($this->businessMember->id)) {
            $this->isRemote = 1;
            $this->setSuccessfulResponseMessage();
        } else {
            $this->setResult(ActionResultCodes::OUT_OF_WIFI_AREA, ActionResultCodeMessages::OUT_OF_WIFI_AREA);
        }
    }

    private function isInWifiArea()
    {
        return in_array($this->ip, $this->business->offices->pluck('ip')->toArray());
    }

    protected function setResult($result_code, $result_message)
    {
        $this->setResultCode($result_code)->setResultMessage($result_message);
    }

    public function isSuccess()
    {
        return $this->resultCode ? in_array($this->resultCode, [ActionResultCodes::SUCCESSFUL, ActionResultCodes::LATE_TODAY, ActionResultCodes::LEFT_EARLY_TODAY]) : true;
    }

    public function isLateNoteRequired()
    {
        $date = Carbon::now();
        $time = new TimeByBusiness();
        $weekendHoliday = new WeekendHolidayByBusiness();
        $checkin_time = $time->getOfficeStartTimeByBusiness();

        if (is_null($checkin_time)) return 0;
        if (!$weekendHoliday->isWeekendByBusiness($date) && !$weekendHoliday->isHolidayByBusiness($date)) {
            return Carbon::now()->gt(Carbon::parse($checkin_time)) ? 1 : 0;
        }
        return 0;
    }

    public function isLeftEarlyNoteRequired()
    {
        $date = Carbon::now();
        $time = new TimeByBusiness();
        $weekendHoliday = new WeekendHolidayByBusiness();
        $checkout_time = $time->getOfficeEndTimeByBusiness();

        if (is_null($checkout_time)) return 0;
        if (!$weekendHoliday->isWeekendByBusiness($date) && !$weekendHoliday->isHolidayByBusiness($date)) {
            return Carbon::now()->lt(Carbon::parse($checkout_time)) ? 1 : 0;
        }
        return 0;
    }

    public function isLateNoteRequiredForSpecificDate($date, $time)
    {
        $date_time = $date.' '.$time;
        $date_time = Carbon::parse($date_time);
        $business_time = new TimeByBusiness();
        $weekendHoliday = new WeekendHolidayByBusiness();
        $checkin_time = $date.' '.$business_time->getOfficeStartTimeByBusiness();

        if (is_null($checkin_time)) return 0;
        if (!$weekendHoliday->isWeekendByBusiness($date_time) && !$weekendHoliday->isHolidayByBusiness($date_time)) {
            return $date_time->gt(Carbon::parse($checkin_time)) ? 1 : 0;
        }
        return 0;
    }

    public function isLeftEarlyNoteRequiredForSpecificDate($date, $time)
    {
        $date_time = $date.' '.$time;
        $date_time = Carbon::parse($date_time);
        $business_time = new TimeByBusiness();
        $weekendHoliday = new WeekendHolidayByBusiness();
        $checkout_time = $date.' '.$business_time->getOfficeEndTimeByBusiness();

        if (is_null($checkout_time)) return 0;
        if (!$weekendHoliday->isWeekendByBusiness($date_time) && !$weekendHoliday->isHolidayByBusiness($date_time)) {
            return $date_time->lt(Carbon::parse($checkout_time)) ? 1 : 0;
        }
        return 0;
    }

    abstract protected function setSuccessfulResponseMessage();

    abstract protected function setAlreadyHasActionForTodayResponse();

    abstract protected function getActionName();
}
