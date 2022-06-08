<?php namespace Sheba\Business\Employee;

use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use Carbon\Carbon;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
use Sheba\Business\CoWorker\ProfileCompletionCalculator;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\Dal\BusinessMemberBadge\BusinessMemberBadgeRepository;
use Sheba\Dal\Visit\VisitRepository;

class EmployeeDashboard
{

    /*** @var BusinessMember */
    private $businessMember;
    /*** @var Business */
    private $business;
    /*** @var Member */
    private $member;
    /*** @var ActionProcessor */
    private $actionProcessor;
    /*** @var ProfileCompletionCalculator */
    private $completionCalculator;
    /*** @var VisitRepository */
    private $visitRepository;
    /*** @var ApprovalRequestRepositoryInterface */
    private $approvalRequestRepo;
    /** @var BusinessMemberBadgeRepository  */
    private $badgeRepo;
    /** @var Attendance */
    private $attendanceOfToday;
    /** @var Attendance */
    private $lastAttendance;
    private $shift;

    public function __construct(ActionProcessor $action_processor, ProfileCompletionCalculator $completion_calculator,
                                VisitRepository $visit_repository, ApprovalRequestRepositoryInterface $approval_request_repository,
                                BusinessMemberBadgeRepository $badge_repo)
    {
        $this->actionProcessor = $action_processor;
        $this->completionCalculator = $completion_calculator;
        $this->visitRepository = $visit_repository;
        $this->approvalRequestRepo = $approval_request_repository;
        $this->badgeRepo = $badge_repo;
    }

    public function setBusinessMember(BusinessMember $business_member): EmployeeDashboard
    {
        $this->businessMember = $business_member;
        $this->business = $this->businessMember->business;
        $this->attendanceOfToday = $this->businessMember->attendanceOfToday();
        $this->lastAttendance = $this->businessMember->lastAttendance();
        $this->member = $this->businessMember->member;
        return $this;
    }

    public function getBusinessMember(): BusinessMember
    {
        return $this->businessMember;
    }

    public function getProfileCompletionScore(): int
    {
        return $this->completionCalculator->setBusinessMember($this->businessMember)->getDigiGoScore();
    }

    public function isBadgeSeen()
    {
        return $this->badgeRepo->isBadgeSeenOnCurrentMonth($this->businessMember->id);
    }

    public function getTodayVisitCount()
    {
        return $this->visitRepository->getTodayVisitCount($this->businessMember->id);
    }

    public function getPendingVisitCount()
    {
        return $this->visitRepository->getPendingVisitCount($this->businessMember->id);
    }

    public function getCurrentVisit()
    {
        return $this->visitRepository->getCurrentVisit($this->businessMember->id);
    }

    public function getSinglePendingVisit()
    {
        return $this->visitRepository->getFirstPendingVisit($this->businessMember->id);
    }

    public function getPendingApprovalRequestCount()
    {
        return $this->approvalRequestRepo->countPendingLeaveApprovalRequests($this->businessMember);
    }

    public function isApprovalRequestRequired(): bool
    {
        return $this->approvalRequestRepo->countApprovalRequestByBusinessMember($this->businessMember) > 0;
    }

    public function getAttendanceInfo(): array
    {
        $can_checkin = $this->calculateCanCheckin();
        $can_checkout = $this->calculateCanCheckout($can_checkin);

        return ['shift' => $this->getShift(), 'can_checkin' => $can_checkin, 'can_checkout' => $can_checkin];
    }

    public function canCheckIn(): bool
    {
        return !$this->attendanceOfToday || $this->attendanceOfToday->canTakeThisAction(Actions::CHECKIN);
    }

    public function canCheckOut(): bool
    {
        return $this->attendanceOfToday && $this->attendanceOfToday->canTakeThisAction(Actions::CHECKOUT);
    }

    public function hasLastAttendance(): bool
    {
        return !is_null($this->lastAttendance);
    }

    public function getLastAttendanceDate()
    {
        return $this->lastAttendance ? Carbon::parse($this->lastAttendance['date']): null;
    }

    public function getNoteAction()
    {
        $last_attendance_log = $this->lastAttendance ? $this->lastAttendance->actions()->get()->sortByDesc('id')->first() : null;

        if (!$last_attendance_log || $last_attendance_log['note']) return null;

        $checkin = $this->actionProcessor->setActionName(Actions::CHECKIN)->getAction();
        $checkout = $this->actionProcessor->setActionName(Actions::CHECKOUT)->getAction();

        if ($last_attendance_log['action'] == Actions::CHECKIN && $checkin->isLateNoteRequiredForSpecificDate($this->lastAttendance['date'], $this->lastAttendance['checkin_time'])) return Actions::CHECKIN;
        if ($last_attendance_log['action'] == Actions::CHECKOUT && $checkout->isLeftEarlyNoteRequiredForSpecificDate($this->lastAttendance['date'], $this->lastAttendance['checkout_time'])) return Actions::CHECKOUT;

        return null;
    }

    private function getShift()
    {
        return $this->shift ? [
            'id' => $this->shift->id,
            'title' => $this->shift->shift_title,
            'start_time' => $this->shift->start_time,
            'end_time' => $this->shift->end_time
        ] : null;
    }

    private function calculateCanCheckin(): bool
    {
        $knownDate = Carbon::create(2022, 6, 8, 21, 20);
        Carbon::setTestNow($knownDate);
        $can_checkin = 0;
        $shift = null;
        $yesterday_shift = $this->businessMember->shiftYesterday();
        $today_shift = $this->businessMember->shiftToday();
        $is_already_checked_in = $today_shift->start_time > $today_shift->end_time ? $this->businessMember->attendanceOfYesterday() : $this->businessMember->attendanceOfToday();
        if (!$is_already_checked_in) return 0;

        if ($today_shift) {
            if ($today_shift->is_general) {
                $can_checkin = 1;
                $shift = $today_shift;
            } else if ($today_shift->is_shift) {
                if (Carbon::now()->toTimeString() < $today_shift->start_time){
                    if ($yesterday_shift && $yesterday_shift->is_shift) {
                        $can_checkin = 1;
                        $shift = $yesterday_shift;
                    }
                } else {
                    $can_checkin = 1;
                    $shift = $today_shift;
                }
            }
        }
        if ($can_checkin){
            $next_shift = $this->businessMember->nextShift();
            $diff = 16;
            $shift_start_time = $shift->date.' '.$shift->start_time;
            $shift_end_time = $shift->start_time > $shift->end_time ? Carbon::parse($shift->date)->addDay()->toDateString().' '.$shift->end_time : $shift->date.' '.$shift->end_time;
            if ($next_shift) $diff = Carbon::parse($next_shift->date.' '.$next_shift->start_time)->diffInHours(Carbon::parse($shift_end_time));
            if ($next_shift && $diff >= 16){
                if (Carbon::now() > Carbon::parse($shift_end_time) || Carbon::now() < Carbon::parse($shift_start_time)->subHours(8)) {
                    $can_checkin = 0;
                    $shift = null;
                }
            } else if ($next_shift && $diff < 16){
                if (Carbon::now() > Carbon::parse($shift_end_time) || Carbon::now() < Carbon::parse($shift_start_time)->subHours($diff/2)) {
                    $can_checkin = 0;
                    $shift = null;
                }
            }
        }
        $this->shift = $shift;
        return $can_checkin;
    }

    private function calculateCanCheckout($can_checkin)
    {
        if (!$can_checkin) return 0;
        $can_checkout = 0;
        $yesterday_shift = $this->businessMember->shiftYesterday();
        $today_shift = $this->businessMember->shiftToday();
        $is_already_checked_in = $today_shift->start_time > $today_shift->end_time ? $this->businessMember->attendanceOfYesterday() : $this->businessMember->attendanceOfToday();
        if (!$is_already_checked_in) return 0;

        if ($today_shift) {
            if ($today_shift->is_general) {
                $can_checkout = 1;
                $shift = $today_shift;
            } else if ($today_shift->is_shift) {
                if (Carbon::now()->toTimeString() < $today_shift->start_time){
                    if ($yesterday_shift && $yesterday_shift->is_shift) {
                        $can_checkout = 1;
                        $shift = $yesterday_shift;
                    }
                } else {
                    $can_checkout = 1;
                    $shift = $today_shift;
                }
            }
        }
        if ($can_checkout){
            $next_shift = $this->businessMember->nextShift();
            $diff = 16;
            $shift_start_time = $shift->date.' '.$shift->start_time;
            $shift_end_time = $shift->start_time > $shift->end_time ? Carbon::parse($shift->date)->addDay()->toDateString().' '.$shift->end_time : $shift->date.' '.$shift->end_time;
            if ($next_shift) $diff = Carbon::parse($next_shift->date.' '.$next_shift->start_time)->diffInHours(Carbon::parse($shift_end_time));
            if ($next_shift && $diff >= 16){
                if (Carbon::now() > Carbon::parse($shift_end_time) || Carbon::now() < Carbon::parse($shift_start_time)->subHours(8)) {
                    $can_checkout = 0;
                    $shift = null;
                }
            } else if ($next_shift && $diff < 16){
                if (Carbon::now() > Carbon::parse($shift_end_time) || Carbon::now() < Carbon::parse($shift_start_time)->subHours($diff/2)) {
                    $can_checkout = 0;
                    $shift = null;
                }
            }
        }
    }
}
