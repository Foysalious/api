<?php namespace Sheba\Business\AttendanceActionLog\ActionChecker;

use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Business\AttendanceActionLog\WeekendHolidayByBusiness;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Dal\Attendance\Model as Attendance;
use App\Models\BusinessMember;
use App\Models\Business;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
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
    protected $attendanceType = null;
    private $geoOffices;
    private $lat;
    private $lng;

    /**
     * @param Business $business
     * @return ActionChecker
     */
    public function setBusiness($business)
    {
        $this->business = $business;
        $this->geoOffices = $this->business->geoOffices()->get();
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

    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    public function setLng($lng)
    {
        $this->lng = $lng;
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

    public function getAttendanceType()
    {
        return $this->attendanceType;
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
        return $this->attendanceLogsOfToday && $this->attendanceLogsOfToday->filter(function ($log) {
                return $log->action == $this->getActionName();
            })->count() > 0;
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
        return $this->attendanceLogsOfToday && $this->attendanceLogsOfToday->filter(function ($log) {
                return $log->device_id != $this->deviceId;
            })->count() > 0;
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

        $isIpBasedAttendanceEnable = $this->business->isIpBasedAttendanceEnable();
        $isGeoLocationAttendanceEnable = $this->business->isGeoLocationAttendanceEnable();
        $isRemoteAttendanceEnable = $this->business->isRemoteAttendanceEnable($this->businessMember->id);

        if ($isIpBasedAttendanceEnable && $isGeoLocationAttendanceEnable && $isRemoteAttendanceEnable) {//WGR
            $this->attendanceType = ($this->isInWifiArea()) ? AttendanceTypes::IP_BASED : (($this->isInGeoLocation()) ? AttendanceTypes::GEO_LOCATION_BASED : AttendanceTypes::REMOTE);
            $this->setSuccessfulResponseMessage();
        } else if ($isIpBasedAttendanceEnable && !$isGeoLocationAttendanceEnable && !$isRemoteAttendanceEnable) {//W
            $office_ip_count = $this->business->offices()->count();
            if ($office_ip_count > 0 && !$this->isInWifiArea()) {
                $this->setResult(ActionResultCodes::OUT_OF_WIFI_AREA, ActionResultCodeMessages::OUT_OF_WIFI_AREA);
            }else{
                $this->attendanceType = AttendanceTypes::IP_BASED;
                $this->setSuccessfulResponseMessage();
            }
        }else if ($isGeoLocationAttendanceEnable && !$isIpBasedAttendanceEnable && !$isRemoteAttendanceEnable) {//G
            $office_geo_location_count = $this->business->geoOffices()->count();
            if ($office_geo_location_count > 0 && !$this->isInGeoLocation()) {
                $this->setResult(ActionResultCodes::OUT_OF_GEO_LOCATION, ActionResultCodeMessages::OUT_OF_GEO_LOCATION);
            }else{
                $this->attendanceType = AttendanceTypes::GEO_LOCATION_BASED;
                $this->setSuccessfulResponseMessage();
            }
        } else if ($isGeoLocationAttendanceEnable && $isIpBasedAttendanceEnable && !$isRemoteAttendanceEnable){//GW
            $is_in_wifi = $this->isInWifiArea();
            if (!$is_in_wifi && !$this->isInGeoLocation()) {
                $this->setResult(ActionResultCodes::OUT_OF_WIFI_GEO_LOCATION, ActionResultCodeMessages::OUT_OF_WIFI_GEO_LOCATION);
            } else {
                $this->attendanceType = ($is_in_wifi) ? AttendanceTypes::IP_BASED : AttendanceTypes::GEO_LOCATION_BASED;
                $this->setSuccessfulResponseMessage();
            }
        } else if($isGeoLocationAttendanceEnable && $isRemoteAttendanceEnable && !$isIpBasedAttendanceEnable) {//GR
            $this->attendanceType = $this->isInGeoLocation() ? AttendanceTypes::GEO_LOCATION_BASED : AttendanceTypes::REMOTE;
            $this->setSuccessfulResponseMessage();
        } else if ($isRemoteAttendanceEnable && $isIpBasedAttendanceEnable && !$isGeoLocationAttendanceEnable) {//RI
            $this->attendanceType = $this->isInWifiArea() ? AttendanceTypes::IP_BASED : AttendanceTypes::REMOTE;
            $this->setSuccessfulResponseMessage();
        } else {
            $this->attendanceType = AttendanceTypes::REMOTE;
            $this->setSuccessfulResponseMessage();
        }
    }

    private function isInWifiArea()
    {
        return in_array($this->ip, $this->business->offices->pluck('ip')->toArray());
    }

    private function isInGeoLocation()
    {
        $is_within = false;
        $from_coords = (new Coords(floatval($this->lat), floatval($this->lng)))->toRadians();

        foreach ($this->geoOffices as $geo_office){
            $geo = $geo_office->location;
            $to_coords = (new Coords(floatval($geo['lat']), floatval($geo['lng'])))->toRadians();
            $distance = (new Distance(DistanceStrategy::$VINCENTY))->linear();
            $is_within = $distance->to($to_coords)->from($from_coords)->isWithin(floatval($geo['radius']));
            if ($is_within) break;
        }
        return $is_within;
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
