<?php namespace Sheba\Business\AttendanceActionLog;

use App\Sheba\Business\Attendance\HalfDaySetting\HalfDayType;
use Carbon\CarbonPeriod;
use Sheba\Business\AttendanceActionLog\Creator as AttendanceActionLogCreator;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
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
    private $note;
    private $deviceId;
    private $userAgent;
    private $lat;
    private $lng;
    private $isRemote;

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

    public function setNote($note)
    {
        $this->note = $note;
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
        $action->setAttendanceOfToday($this->attendance)->setIp($this->getIp())->setDeviceId($this->deviceId)->setBusiness($this->business);
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
            if ($this->action == Actions::CHECKOUT) $this->attendanceActionLogCreator->setNote($this->note);
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
        $which_half_day = null;
        $leaves_date_with_half_and_full_day = $this->formatLeaveAsDateArray();
        if ($this->isHalfDayLeave(Carbon::now(), $leaves_date_with_half_and_full_day)) {
            $which_half_day = $this->whichHalfDayLeave(Carbon::now(), $leaves_date_with_half_and_full_day);
        }
        return $which_half_day;
    }

    /**
     * @return array
     */
    private function formatLeaveAsDateArray()
    {
        $year = date('Y');
        $month = date('m');
        $time_frame = (new TimeFrame)->forAMonth($month, $year);
        $business_member_leave = $this->businessMember->leaves()->accepted()->between($time_frame)->get();

        $business_member_leaves_date_with_half_and_full_day = [];
        $business_member_leave->each(function ($leave) use (&$business_member_leaves_date_with_half_and_full_day) {
            $leave_period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($leave_period as $date) {
                $business_member_leaves_date_with_half_and_full_day[$date->toDateString()] = [
                    'is_half_day_leave' => $leave->is_half_day,
                    'which_half_day' => $leave->half_day_configuration,
                ];
            }
        });

        return $business_member_leaves_date_with_half_and_full_day;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return int
     */
    private function isHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['is_half_day_leave'] == 1) return 1;
        }
        return 0;
    }

    /**
     * @param Carbon $date
     * @param array $leaves_date_with_half_and_full_day
     * @return string
     */
    private function whichHalfDayLeave(Carbon $date, array $leaves_date_with_half_and_full_day)
    {
        if (array_key_exists($date->format('Y-m-d'), $leaves_date_with_half_and_full_day)) {
            if ($leaves_date_with_half_and_full_day[$date->format('Y-m-d')]['which_half_day'] == HalfDayType::FIRST_HALF) return HalfDayType::FIRST_HALF;
        }
        return HalfDayType::SECOND_HALF;
    }
}
