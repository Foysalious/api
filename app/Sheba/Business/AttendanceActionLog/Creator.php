<?php namespace Sheba\Business\AttendanceActionLog;

use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckinStatusCalculator;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckoutStatusCalculator;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\AttendanceActionLog\EloquentImplementation as AttendanceActionLogRepositoryInterface;
use Sheba\Location\Geo;

class Creator
{
    private $attendanceActionLogRepository;
    private $action;
    private $deviceId;
    /** @var Attendance $attendance */
    private $attendance;
    /** @var Geo */
    private $geo;
    private $ip;
    private $userAgent;
    private $note;
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

    /**
     * @param mixed $action
     * @return Creator
     */
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

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function create()
    {
        if ($this->action == Actions::CHECKIN)
            $status = $this->checkinStatusCalculator->setAction($this->action)->setAttendance($this->attendance)->calculate();
        else
            $status = $this->checkoutStatusCalculator->setAction($this->action)->setAttendance($this->attendance)->calculate();

        $attendance_log_data = [
            'attendance_id' => $this->attendance->id,
            'action' => $this->action,
            'note' => $this->note,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'device_id' => $this->deviceId,
            'status' => $status
        ];
        if ($this->geo) $attendance_log_data['location'] = json_encode(['lat' => $this->geo->getLat(), 'lng' => $this->geo->getLng()]);
        return $this->attendanceActionLogRepository->create($attendance_log_data);
    }
}
