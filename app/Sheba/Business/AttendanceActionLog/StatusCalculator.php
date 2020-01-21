<?php namespace Sheba\Business\AttendanceActionLog;

use Carbon\Carbon;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\AttendanceActionLog\Actions;

class StatusCalculator
{
    /** @var Attendance $attendance */
    private $attendance;
    private $action;
    const LAST_CHECKIN_TIME = "9:30:00";
    const MINIMUM_HOURS_TO_STAY_IN_OFFICE = 9;

    /**
     * @param Attendance $attendance
     * @return $this
     */
    public function setAttendance(Attendance $attendance)
    {
        $this->attendance = $attendance;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function calculate()
    {
        if ($this->action == Actions::CHECKIN) {
            $todays_last_checkin_time = Carbon::parse(Carbon::now()->format('Y-m-d') . ' '. self::LAST_CHECKIN_TIME);
            $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);

            if ($todays_checkin_time->greaterThan($todays_last_checkin_time))
                return Statuses::LATE;

            return Statuses::ON_TIME;
        }

        if ($this->action == Actions::CHECKOUT) {
            $todays_checkin_time = Carbon::parse($this->attendance->date . ' ' . $this->attendance->checkin_time);
            $hour_spend_on_office = Carbon::now()->diffInHours($todays_checkin_time);
            if ($hour_spend_on_office < self::MINIMUM_HOURS_TO_STAY_IN_OFFICE)
                return Statuses::LEFT_EARLY;

            return Statuses::ON_TIME;
        }
    }
}
