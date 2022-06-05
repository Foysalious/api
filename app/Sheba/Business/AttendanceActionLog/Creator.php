<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use App\Models\BusinessMember;
use Illuminate\Support\Facades\Log;
use Sheba\Business\Attendance\AttendanceTypes\AttendanceSuccess;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckinStatusCalculator;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckoutStatusCalculator;
use Sheba\Business\AttendanceActionLog\StatusCalculator\ShiftCheckinStatusCalculator;
use Sheba\Business\AttendanceActionLog\StatusCalculator\ShiftCheckoutStatusCalculator;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\AttendanceActionLog\EloquentImplementation as AttendanceActionLogRepositoryInterface;
use Sheba\Dal\BusinessAttendanceTypes\AttendanceTypes;
use Sheba\Location\Geo;
use Sheba\Map\Client\BarikoiClient;
use Throwable;

class Creator
{
    private $attendanceActionLogRepository;
    private $action;
    private $business;
    private $deviceId;
    /** @var Attendance $attendance */
    private $attendance;
    /** @var Geo */
    private $geo;
    private $ip;
    private $userAgent;
    private $note;
    private $isRemote;
    private $whichHalfDay;
    private $address;
    private $remoteMode;
    /** @var CheckinStatusCalculator $checkinStatusCalculator */
    private $checkinStatusCalculator;
    /** @var CheckoutStatusCalculator $checkoutStatusCalculator */
    private $checkoutStatusCalculator;
    /** @var AttendanceSuccess */
    private $attendanceSuccess;
    private $shiftAssignment;
    /*** @var ShiftCheckinStatusCalculator */
    private $shiftCheckinStatusCalculator;
    /** * @var ShiftCheckoutStatusCalculator  */
    private $shiftCheckoutStatusCalculator;
    /*** @var BusinessMember */
    private $businessMember;

    /**
     * Creator constructor.
     *
     * @param AttendanceActionLogRepositoryInterface $attendance_action_log_repository
     * @param CheckinStatusCalculator $checkin_status_calculator
     * @param CheckoutStatusCalculator $checkout_status_calculator
     * @param ShiftCheckinStatusCalculator $shift_checkin_status_calculator
     * @param ShiftCheckoutStatusCalculator $shift_checkout_status_calculator
     */
    public function __construct(AttendanceActionLogRepositoryInterface $attendance_action_log_repository,
                                CheckinStatusCalculator $checkin_status_calculator, CheckoutStatusCalculator $checkout_status_calculator,
                                ShiftCheckinStatusCalculator $shift_checkin_status_calculator, ShiftCheckoutStatusCalculator $shift_checkout_status_calculator)
    {
        $this->attendanceActionLogRepository = $attendance_action_log_repository;
        $this->checkinStatusCalculator = $checkin_status_calculator;
        $this->checkoutStatusCalculator = $checkout_status_calculator;
        $this->shiftCheckinStatusCalculator = $shift_checkin_status_calculator;
        $this->shiftCheckoutStatusCalculator = $shift_checkout_status_calculator;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    /**
     * @param mixed $action
     * @return Creator
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param mixed $deviceId
     * @return Creator
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    /**
     * @param mixed $ip
     * @return Creator
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @param mixed $userAgent
     * @return Creator
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @param Attendance $attendance
     * @return Creator
     */
    public function setAttendance($attendance)
    {
        $this->attendance = $attendance;
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

    /**
     * @param $remoteMode
     * @return $this
     */
    public function setRemoteMode($remoteMode)
    {
        $this->remoteMode = $remoteMode;
        return $this;
    }

    public function setAttendanceSuccess(AttendanceSuccess $success)
    {
        $this->attendanceSuccess = $success;
        return $this;
    }

    /**
     * @param $which_half
     * @return $this
     */
    public function setWhichHalfDay($which_half)
    {
        $this->whichHalfDay = $which_half;
        return $this;
    }

    public function setShiftAssignment($shift_assignment)
    {
        $this->shiftAssignment = $shift_assignment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function create()
    {
        if ($this->action == Actions::CHECKIN){
            if (!$this->shiftAssignment  || $this->shiftAssignment && $this->shiftAssignment->is_general) $status = $this->checkinStatusCalculator->setBusiness($this->business)->setAction($this->action)->setAttendance($this->attendance)->setWhichHalfDay($this->whichHalfDay)->calculate();
            else $status = $this->shiftCheckinStatusCalculator->setBusinessMember($this->businessMember)->setShiftAssignment($this->shiftAssignment)->setAction($this->action)->setAttendance($this->attendance)->setWhichHalfDay($this->whichHalfDay)->calculate();
        }
        else
            $status = $this->checkoutStatusCalculator->setBusiness($this->business)->setAction($this->action)->setAttendance($this->attendance)->setWhichHalfDay($this->whichHalfDay)->calculate();

        $attendance_log_data = [
            'attendance_id' => $this->attendance->id,
            'action' => $this->action,
            'note' => $this->note,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'device_id' => $this->deviceId,
            'status' => $status
        ];

        if ($this->attendanceSuccess->getAttendanceType() === AttendanceTypes::IP_BASED) {
            $attendance_log_data['is_in_wifi'] = 1;
            $attendance_log_data['is_remote'] = 0;
        }
        else if ($this->attendanceSuccess->getAttendanceType() === AttendanceTypes::GEO_LOCATION_BASED) {
            $attendance_log_data['is_geo_location'] = 1;
            $attendance_log_data['is_remote'] = 0;
        }
        else if ($this->attendanceSuccess->getAttendanceType() === AttendanceTypes::REMOTE) $attendance_log_data['is_remote'] = 1;
        $attendance_log_data['business_office_id'] = $this->attendanceSuccess->getBusinessOfficeId();

        $this->address = $this->getAddress();
        if ($this->geo) $attendance_log_data['location'] = json_encode(['lat' => $this->geo->getLat(), 'lng' => $this->geo->getLng(), 'address' => $this->address]);
        if ($this->remoteMode) $attendance_log_data['remote_mode'] = $this->remoteMode;

        Log::info("Attendance Log for Employee#" . json_encode($attendance_log_data));

        return $this->attendanceActionLogRepository->create($attendance_log_data);
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        try {
            return (new BarikoiClient)->getAddressFromGeo($this->geo)->getAddress();
        } catch (Throwable $exception) {
            return "";
        }
    }
}
