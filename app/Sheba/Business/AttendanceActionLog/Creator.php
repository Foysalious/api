<?php namespace Sheba\Business\AttendanceActionLog;


use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\EloquentImplementation;

class Creator
{
    private $attendanceActionLogRepository;
    private $action;
    private $deviceId;
    /** @var Attendance */
    private $attendance;
    private $ip;
    private $userAgent;
    private $note;

    public function __construct(EloquentImplementation $attendance_action_log_repository)
    {
        $this->attendanceActionLogRepository = $attendance_action_log_repository;
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
        return $this->attendanceActionLogRepository->create([
            'attendance_id' => $this->attendance->id,
            'action' => $this->action,
            'note' => $this->note,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
        ]);
    }
}