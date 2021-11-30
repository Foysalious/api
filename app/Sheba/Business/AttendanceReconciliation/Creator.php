<?php namespace App\Sheba\Business\AttendanceReconciliation;


use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckinStatusCalculator;
use Sheba\Business\AttendanceActionLog\StatusCalculator\CheckoutStatusCalculator;
use Sheba\Business\AttendanceActionLog\TimeByBusiness;
use Sheba\Business\Leave\HalfDay\HalfDayLeaveCheck;
use Sheba\Dal\Attendance\Contract as AttendanceRepo;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\AttendanceActionLog\Contract as AttendanceActionLogsRepo;
use Sheba\Dal\AttendanceOverrideLogs\AttendanceOverrideLogsRepo;
use Sheba\ModificationFields;

class Creator
{
    const ATTENDANCE_RECONCILED = 1;
    use ModificationFields;

    /*** @var Requester $reconciliation_requester */
    private $reconciliationRequester;
    /*** @var AttendanceRepo $attendanceRepo */
    private $attendanceRepo;
    /*** @var AttendanceActionLogsRepo $attendanceActionLogsRepo */
    private $attendanceActionLogsRepo;
    /*** @var AttendanceOverrideLogsRepo $attendanceOverrideLogsRepo */
    private $attendanceOverrideLogsRepo;
    private $businessMember;
    private $date;
    private $checkin;
    private $business;
    private $whichHalf;
    private $businessCheckinTime;
    private $businessCheckoutTime;
    private $checkout;
    private $attendance;

    public function __construct()
    {
        $this->attendanceRepo = app(AttendanceRepo::class);
        $this->attendanceActionLogsRepo = app(AttendanceActionLogsRepo::class);
        $this->attendanceOverrideLogsRepo = app(AttendanceOverrideLogsRepo::class);
    }

    public function setRequester(Requester $reconciliation_requester)
    {
        $this->reconciliationRequester = $reconciliation_requester;
        $this->businessMember = $this->reconciliationRequester->getBusinessMember();
        $this->business = $this->businessMember->business;
        $this->checkin = $this->reconciliationRequester->getCheckinTime();
        $this->checkout = $this->reconciliationRequester->getCheckoutTime();
        $this->date = $this->reconciliationRequester->getDate();
        $this->attendance = $this->attendanceRepo->where('business_member_id', $this->businessMember->id)->where('date', $this->date)->first();
        $this->whichHalf = (new HalfDayLeaveCheck())->setBusinessMember($this->businessMember)->checkHalfDayLeave();
        return $this;
    }

    public function create()
    {
        if ($this->checkin) $this->createOrUpdateCheckin();
        if ($this->checkout) $this->createOrUpdateCheckout();
    }

    private function createOrUpdateCheckin()
    {
        $this->businessCheckinTime = Carbon::parse($this->date . '' . (new TimeByBusiness())->getOfficeStartTimeByBusiness(Carbon::parse($this->date . ' ' . $this->checkin), $this->businessMember->business, $this->businessMember));
        $new_attendance = false;
        if (!$this->attendance) {
            $this->attendance = $this->createAttendance();
            $new_attendance = true;
        }
        $status = (new CheckinStatusCalculator)->setBusiness($this->business)
            ->setAction(Actions::CHECKIN)
            ->setAttendance($this->attendance)
            ->setCheckinTime($this->businessCheckinTime)
            ->setNewCheckInTime($this->checkin)
            ->setWhichHalfDay($this->whichHalf)
            ->calculate();
        if ($new_attendance) {
            $this->createAttendanceActionLogs(Actions::CHECKIN, $status);
            $this->attendanceOverrideLogsRepo->create([
                'attendance_id' => $this->attendance->id,
                'action' => Actions::CHECKIN,
                'new_time' => $this->checkin,
                'new_status' => $status,
                'log' => 'Checkin has been reconciled'
            ]);
        } else {
            $prev_time = $this->attendance->checkin_time;
            $prev_status = $this->attendanceActionLogsRepo->where('attendance_id', $this->attendance->id)->where('action',Actions::CHECKIN)->first()->status;
            $this->updateCheckinAttendance();
            $attendance_action_log = $this->getAttendanceActionLogs(Actions::CHECKIN);
            $this->updateAttendanceActionLogs($attendance_action_log, $status);
            $this->attendanceOverrideLogsRepo->create([
                'attendance_id' => $this->attendance->id,
                'action' => Actions::CHECKIN,
                'previous_time' => $prev_time,
                'previous_status' => $prev_status,
                'new_time' => $this->checkin,
                'new_status' => $status,
                'log' => 'Checkin has been reconciled'
            ]);
        }
    }

    private function createAttendance()
    {
        return $this->attendanceRepo->create($this->withCreateModificationField([
            'business_member_id' => $this->businessMember->id,
            'date' => $this->date,
            'checkin_time' => $this->checkin,
            'is_attendance_reconciled' => self::ATTENDANCE_RECONCILED
        ]));
    }

    private function updateCheckinAttendance()
    {
        $this->attendanceRepo->update($this->attendance, ['checkin_time' => $this->checkin]);
    }

    private function updateAttendanceActionLogs($attendance_action_log, $status)
    {
        $this->attendanceActionLogsRepo->update($attendance_action_log, ['status' => $status]);
    }

    private function createOrUpdateCheckout()
    {
        $this->businessCheckoutTime = Carbon::parse($this->date . '' . (new TimeByBusiness())->getOfficeEndTimeByBusiness(Carbon::parse($this->date . ' ' . $this->checkout), $this->businessMember->business, $this->businessMember));
        if (!$this->businessCheckinTime) $this->businessCheckinTime = Carbon::parse($this->date . ' ' .$this->attendance->checkin_time);
        
        $status = (new CheckoutStatusCalculator)->setBusiness($this->business)
            ->setAction(Actions::CHECKOUT)
            ->setAttendance($this->attendance)
            ->setCheckoutTime($this->businessCheckoutTime)
            ->setNewCheckOutTime($this->checkout)
            ->setWhichHalfDay($this->whichHalf)
            ->calculate();
        $prev_time = $this->attendance->checkout_time;
        $this->updateCheckoutAttendance();
        $attendance_action_log = $this->getAttendanceActionLogs(Actions::CHECKOUT);
        $prev_status = null;
        if (!$attendance_action_log) {
            $this->createAttendanceActionLogs(Actions::CHECKOUT, $status);
        } else {
            $prev_status = $attendance_action_log->status;
            $this->updateAttendanceActionLogs($attendance_action_log, $status);
        }
        $this->attendanceOverrideLogsRepo->create([
            'attendance_id' => $this->attendance->id,
            'action' => Actions::CHECKOUT,
            'previous_time' => $prev_status ? $prev_time : null,
            'previous_status' => $prev_status,
            'new_time' => $this->checkout,
            'new_status' => $status,
            'log' => 'Checkout has been reconciled'
        ]);
    }

    private function updateCheckoutAttendance()
    {
        $staying_time_in_minutes = Carbon::parse($this->checkout)->diffInMinutes(Carbon::parse($this->attendance->checkin_time)) + 1;
        $this->attendanceRepo->update($this->attendance, [
            'checkout_time' => $this->checkout,
            'staying_time_in_minutes' => $staying_time_in_minutes,
            'overtime_in_minutes' => $this->calculateOvertime($staying_time_in_minutes),
            'is_attendance_reconciled' => self::ATTENDANCE_RECONCILED
        ]);
    }

    private function calculateOvertime($staying_time_in_minutes)
    {
        $office_time_in_minutes = $this->businessCheckinTime->diffInMinutes($this->businessCheckoutTime) + 1;
        if ($staying_time_in_minutes > $office_time_in_minutes) return ($staying_time_in_minutes - $office_time_in_minutes);
        return 0;
    }

    private function createAttendanceActionLogs($action, $status)
    {
        $this->attendanceActionLogsRepo->create($this->withCreateModificationField([
            'attendance_id' => $this->attendance->id,
            'action' => $action,
            'status' => $status
        ]));
    }

    private function getAttendanceActionLogs($action)
    {
        return $this->attendanceActionLogsRepo->where('attendance_id', $this->attendance->id)->where('action', $action)->first();
    }

}