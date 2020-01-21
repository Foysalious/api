<?php namespace Sheba\Business\AttendanceActionLog;

use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\EloquentImplementation as AttendanceActionLogRepositoryInterface;

class Creator
{
    private $attendanceActionLogRepository;
    private $action;
    private $deviceId;
    /** @var Attendance $attendance */
    private $attendance;
    private $ip;
    private $userAgent;
    private $note;
    /** @var StatusCalculator $calculator */
    private $calculator;

    /**
     * Creator constructor.
     *
     * @param AttendanceActionLogRepositoryInterface $attendance_action_log_repository
     * @param StatusCalculator $calculator
     */
    public function __construct(AttendanceActionLogRepositoryInterface $attendance_action_log_repository, StatusCalculator $calculator)
    {
        $this->attendanceActionLogRepository = $attendance_action_log_repository;
        $this->calculator = $calculator;
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

    public function create()
    {
        $status = $this->calculator->setAction($this->action)->setAttendance($this->attendance)->calculate();
        $attendance_log_data = [
            'attendance_id' => $this->attendance->id,
            'action' => $this->action,
            'note' => $this->note,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'device_id' => $this->deviceId,
            'status' => $status
        ];
        return $this->attendanceActionLogRepository->create($attendance_log_data);
    }
}
