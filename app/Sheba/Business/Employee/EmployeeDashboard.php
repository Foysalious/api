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
}
