<?php namespace App\Http\Presenters;

use Sheba\Business\Employee\AttendanceActionChecker;

class EmployeeActionPresenter extends Presenter
{
    /*** @var AttendanceActionChecker */
    private $attendanceActionChecker;

    public function __construct(AttendanceActionChecker $attendance_action_checker)
    {
        $this->attendanceActionChecker = $attendance_action_checker;
        return $this;
    }

    public function toArray()
    {
        $is_remote_enable = $this->attendanceActionChecker->isRemoteAttendanceEnable();
        return [
            'can_checkin' => (int) $this->attendanceActionChecker->canCheckIn(),
            'can_checkout' => (int) $this->attendanceActionChecker->canCheckOut(),
            'checkin_time' => $this->attendanceActionChecker->getCheckInTime(),
            'checkout_time' => $this->attendanceActionChecker->getCheckOutTime(),
            'is_geo_required' => $is_remote_enable ? 1 : 0,
            'is_remote_enable' => $is_remote_enable,
            'is_geo_location_enable' => $this->attendanceActionChecker->isGeoLocationAttendanceEnable(),
            'is_live_track_enable' => (int) $this->attendanceActionChecker->isLiveTrackEnable()
        ];
    }
}
