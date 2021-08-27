<?php namespace Sheba\Business\AttendanceActionLog;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\CarbonPeriod;
use Sheba\Business\AttendanceActionLog\Creator as AttendanceActionLogCreator;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
use Sheba\Business\Leave\HalfDay\HalfDayLeaveCheck;
use Sheba\Dal\AttendanceActionLog\Model as AttendanceActionLog;
use Sheba\Business\Attendance\Creator as AttendanceCreator;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\Attendance\Statuses;
use App\Models\BusinessMember;
use App\Models\Business;
use Sheba\Helpers\TimeFrame;
use Sheba\Location\Geo;
use Carbon\Carbon;
use DB;

class AttendanceAction
{
    /** @var BusinessMember */
    private $businessMember;
    /** @var Business */
    private $business;
    /** @var Carbon */
    private $today;
    /** @var EloquentImplementation */
    private $attendanceRepository;
    /** @var Attendance */
    private $attendance;
    private $attendanceCreator;
    private $attendanceActionLogCreator;
    private $action;
    private $deviceId;
    private $userAgent;
    private $lat;
    private $lng;
    private $isRemote;
    private $remoteMode;

    /**
     * AttendanceAction constructor.
     * @param EloquentImplementation $attendance_repository
     * @param AttendanceCreator $attendance_creator
     * @param Creator $attendance_action_log_creator
     */
    public function __construct(EloquentImplementation $attendance_repository, AttendanceCreator $attendance_creator, AttendanceActionLogCreator $attendance_action_log_creator)
    {
        $this->today = Carbon::now();
        $this->attendanceRepository = $attendance_repository;
        $this->attendanceCreator = $attendance_creator;
        $this->attendanceActionLogCreator = $attendance_action_log_creator;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        $this->setAttendance($this->businessMember->attendanceOfToday());
        return $this;
    }

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setDeviceId($device_id)
    {
        $this->deviceId = $device_id;
        return $this;
    }

    private function getIp()
    {
        $ip_methods = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        foreach ($ip_methods as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); //just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return request()->ip();
    }

    /**
     * @param $attendance
     * @return $this
     */
    private function setAttendance($attendance)
    {
        $this->attendance = $attendance;
        return $this;
    }

    /**
     * @param mixed $lat
     * @return AttendanceAction
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @param mixed $lng
     * @return AttendanceAction
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    /**
     * @param $remote_mode
     * @return $this
     */
    public function setRemoteMode($remote_mode)
    {
        $this->remoteMode = $remote_mode;
        return $this;
    }

    public function doAction()
    {
        $action = $this->checkTheAction();
        if ($action->isSuccess()) $this->doDatabaseTransaction();
        return $action;
    }

    /**
     * @return ActionChecker\ActionChecker
     */
    public function checkTheAction()
    {
        $processor = new ActionProcessor();
        $action = $processor->setActionName($this->action)->getAction();
        $action->setAttendanceOfToday($this->attendance)->setIp($this->getIp())->setDeviceId($this->deviceId)->setBusiness($this->business)->setBusinessMember($this->businessMember);
        $action->check();
        $this->isRemote = $action->getIsRemote();
        return $action;
    }

    private function doDatabaseTransaction()
    {
        DB::transaction(function () {
            if (!$this->attendance) $this->createAttendance();
            $this->attendanceActionLogCreator
                ->setAction($this->action)
                ->setAttendance($this->attendance)
                ->setIp($this->getIp())
                ->setDeviceId($this->deviceId)
                ->setUserAgent($this->userAgent)
                ->setIsRemote($this->isRemote)
                ->setBusiness($this->business)
                ->setWhichHalfDay($this->checkHalfDayLeave());
            if ($geo = $this->getGeo()) $this->attendanceActionLogCreator->setGeo($geo);
            if ($this->isRemote) $this->attendanceActionLogCreator->setRemoteMode($this->remoteMode);
            $attendance_action_log = $this->attendanceActionLogCreator->create();
            $this->updateAttendance($attendance_action_log);
        });
    }


    private function createAttendance()
    {
        $attendance = $this->attendanceCreator
            ->setBusinessMemberId($this->businessMember->id)
            ->setDate(Carbon::now()->toDateString())
            ->create();

        $this->setAttendance($attendance);
    }

    /**
     * @param AttendanceActionLog $model
     */
    private function updateAttendance(AttendanceActionLog $model)
    {
        $data = [];
        $data['status'] = $model->status;
        if ($this->action == Actions::CHECKOUT) {
            $data['status'] = ($this->attendance->status == Statuses::LATE) ? Statuses::LATE : $model->status;
            $data['checkout_time'] = $model->created_at->format('H:i:s');
            $data['staying_time_in_minutes'] = $model->created_at->diffInMinutes($this->attendance->checkin_time);
            $data['overtime_in_minutes'] = $this->calculateOvertime($data['staying_time_in_minutes']);
        }
        $this->attendanceRepository->update($this->attendance, $data);
    }

    /**
     * @return Geo|null
     */
    private function getGeo()
    {
        if (!$this->lat || !$this->lng) return null;
        $geo = new Geo();
        return $geo->setLat($this->lat)->setLng($this->lng);
    }

    /**
     * @return string|null
     */
    private function checkHalfDayLeave()
    {
        return (new HalfDayLeaveCheck())->setBusinessMember($this->businessMember)->checkHalfDayLeave();
    }

    /**
     * @param $staying_time
     * @return int
     */
    private function calculateOvertime($staying_time)
    {
        $office_hour = $this->business->officeHour;
        $office_time_duration_in_minutes = Carbon::parse($office_hour->start_time)->diffInMinutes(Carbon::parse($office_hour->end_time)) + 1;

        if ($staying_time > $office_time_duration_in_minutes) {
            return $staying_time - $office_time_duration_in_minutes;
        } else {
            return 0;
        }
    }
}
