<?php namespace Sheba\Business\AttendanceActionLog;

use App\Models\Business;
use Sheba\Dal\AttendanceActionLog\EloquentImplementation as AttendanceActionLogRepositoryInterface;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckinStatusCalculator;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckoutStatusCalculator;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Map\Client\BarikoiClient;
use Sheba\Location\Geo;

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

    /**
     * Creator constructor.
     *
     * @param AttendanceActionLogRepositoryInterface $attendance_action_log_repository
     * @param CheckinStatusCalculator $checkin_status_calculator
     * @param CheckoutStatusCalculator $checkout_status_calculator
     */
    public function __construct(AttendanceActionLogRepositoryInterface $attendance_action_log_repository,
                                CheckinStatusCalculator $checkin_status_calculator,
                                CheckoutStatusCalculator $checkout_status_calculator)
    {
        $this->attendanceActionLogRepository = $attendance_action_log_repository;
        $this->checkinStatusCalculator = $checkin_status_calculator;
        $this->checkoutStatusCalculator = $checkout_status_calculator;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
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
     * @param $is_remote
     * @return $this
     */
    public function setIsRemote($is_remote)
    {
        $this->isRemote = $is_remote;
        return $this;
    }

    public function setRemoteMode($remoteMode)
    {
        $this->remoteMode = $remoteMode;
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

    /**
     * @return mixed
     */
    public function create()
    {
        if ($this->action == Actions::CHECKIN)
            $status = $this->checkinStatusCalculator->setBusiness($this->business)->setAction($this->action)->setAttendance($this->attendance)->setWhichHalfDay($this->whichHalfDay)->calculate();
        else
            $status = $this->checkoutStatusCalculator->setBusiness($this->business)->setAction($this->action)->setAttendance($this->attendance)->setWhichHalfDay($this->whichHalfDay)->calculate();

        $attendance_log_data = [
            'attendance_id' => $this->attendance->id,
            'action' => $this->action,
            'note' => $this->note,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'device_id' => $this->deviceId,
            'status' => $status,
            'is_remote' => $this->isRemote
        ];
        $this->address = $this->getAddress();

        if ($this->geo) $attendance_log_data['location'] = json_encode(['lat' => $this->geo->getLat(), 'lng' => $this->geo->getLng(), 'address' => $this->address]);
        if ($this->remoteMode) $attendance_log_data['remote_mode'] = $this->remoteMode;
        return $this->attendanceActionLogRepository->create($attendance_log_data);
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        try {
            return (new BarikoiClient)->getAddressFromGeo($this->geo)->getAddress();
        } catch (\Throwable $exception) {
            return "";
        }
    }
}
