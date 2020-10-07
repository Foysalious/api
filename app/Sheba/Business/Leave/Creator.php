<?php namespace App\Sheba\Business\Leave;

use App\Jobs\Business\SendLeaveSubstitutionPushNotificationToEmployee;
use App\Models\Business;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Attachments\Attachments;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\UploadedFile;
use Sheba\Business\ApprovalRequest\Creator as ApprovalRequestCreator;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\Leave\EloquentImplementation as LeaveRepository;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Leave\Model as Leave;
use DB;

class Creator
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $title;
    /** @var BusinessMember $businessMember */
    private $businessMember;
    private $leaveTypeId;
    private $leaveRepository;
    /** @var Attachments */
    private $attachmentManager;
    /** @var Carbon $startDate */
    private $startDate;
    /** @var Carbon $endDate */
    private $endDate;
    /** @var BusinessMemberRepositoryInterface $businessMemberRepository */
    private $businessMemberRepository;
    /** @var ApprovalRequestCreator $approval_request_creator */
    private $approval_request_creator;
    /** @var array $approvers */
    private $approvers;
    private $managers = [];
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $note;
    private $isHalfDay;
    private $halfDayConfigure;
    private $substitute;
    private $createdBy;
    /** @var UploadedFile[] */
    private $attachments = [];
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;
    /** @var Business $business */
    private $business;
    private $businessHoliday;
    private $businessWeekend;

    /**
     * Creator constructor.
     * @param LeaveRepository $leave_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ApprovalRequestCreator $approval_request_creator
     * @param TimeFrame $time_frame
     * @param Attachments $attachment_manager
     * @param PushNotificationHandler $push_notification_handler
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     */
    public function __construct(LeaveRepository $leave_repo, BusinessMemberRepositoryInterface $business_member_repo,
                                ApprovalRequestCreator $approval_request_creator, TimeFrame $time_frame,
                                Attachments $attachment_manager, PushNotificationHandler $push_notification_handler,
                                BusinessHolidayRepoInterface $business_holiday_repo,
                                BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $this->leaveRepository = $leave_repo;
        $this->businessMemberRepository = $business_member_repo;
        $this->approval_request_creator = $approval_request_creator;
        $this->timeFrame = $time_frame;
        $this->attachmentManager = $attachment_manager;
        $this->pushNotificationHandler = $push_notification_handler;
        $this->businessHoliday = $business_holiday_repo;
        $this->businessWeekend = $business_weekend_repo;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param BusinessMember $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member->load('member', 'business');
        $this->business = $this->businessMember->business;
        $this->getManager($this->businessMember);

        if ($this->substitute == $this->businessMember->id) {
            $this->setError(422, 'You can\'t be your own substitute!');
            return $this;
        }

        if (empty($this->managers)) {
            $this->setError(422, 'Manager not set yet!');
            return $this;
        }

        /** @var BusinessDepartment $department */
        $department = $this->businessMember->department();
        if (!$department) {
            $this->setError(422, 'Department not set yet!');
            return $this;
        }

        $approval_flow = $department->approvalFlowBy(Type::LEAVE);
        if (!$approval_flow) {
            $this->setError(422, 'Approval flow not set yet!');
            return $this;
        }

        $this->approvers = $this->calculateApprovers($approval_flow, $department);
        if (empty($this->approvers)) {
            $this->setError(422, 'No Approver set yet!');
            return $this;
        }

        return $this;
    }

    public function setLeaveTypeId($leave_type_id)
    {
        $this->leaveTypeId = (int)$leave_type_id;
        return $this;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = Carbon::parse($startDate);
        return $this;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = Carbon::parse($endDate)->endOfDay();
        return $this;
    }

    public function setIsHalfDay($is_half_day)
    {
        $this->isHalfDay = $is_half_day;
        return $this;
    }

    public function setHalfDayConfigure($half_day_configuration)
    {
        $this->halfDayConfigure = $half_day_configuration;
        return $this;
    }

    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    public function setCreatedBy($created_by)
    {
        $this->createdBy = $created_by;
        return $this;
    }

    /**
     * @param $attachments UploadedFile[]
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    private function setTotalDays()
    {
        $leave_day_into_holiday_or_weekend = 0;
        if (!$this->business->is_sandwich_leave_enable) {
            $business_holiday = $this->businessHoliday->getAllDateArrayByBusiness($this->business);
            $business_weekend = $this->businessWeekend->getAllByBusiness($this->business)->pluck('weekday_name')->toArray();

            $period = CarbonPeriod::create($this->startDate, $this->endDate);
            foreach ($period as $date) {
                $day_name_in_lower_case = strtolower($date->format('l'));
                if (in_array($day_name_in_lower_case, $business_weekend)) {
                    $leave_day_into_holiday_or_weekend++;
                    continue;
                }
                if (in_array($date->toDateString(), $business_holiday)) {
                    $leave_day_into_holiday_or_weekend++;
                    continue;
                }
            }
        }

        return $this->isHalfDay ?
            ($this->endDate->diffInDays($this->startDate) + 0.5) - $leave_day_into_holiday_or_weekend :
            ($this->endDate->diffInDays($this->startDate) + 1) - $leave_day_into_holiday_or_weekend;
    }

    public function setSubstitute($substitute_id)
    {
        $this->substitute = $substitute_id;
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        $data = [
            'title' => $this->title,
            'note' => $this->note,
            'business_member_id' => $this->businessMember->id,
            'substitute_id' => $this->substitute,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_half_day' => $this->isHalfDay,
            'half_day_configuration' => $this->halfDayConfigure,
            'total_days' => $this->setTotalDays(),
            'left_days' => $this->getLeftDays()
        ];
        $leave = null;
        DB::transaction(function () use ($data, &$leave) {
            $this->setModifier($this->businessMember->member);
            $leave = $this->leaveRepository->create($this->withCreateModificationField($data));
            $this->approval_request_creator->setBusinessMember($this->businessMember)
                ->setApproverId($this->approvers)
                ->setRequestable($leave)
                ->create();
            $this->createAttachments($leave);
        });

        if ($leave->substitute_id) $this->sendPushToSubstitute($leave);

        return $leave;
    }

    /**
     * @param Leave $leave
     */
    private function createAttachments(Leave $leave)
    {
        foreach ($this->attachments as $attachment) {
            $this->attachmentManager->setAttachableModel($leave)
                ->setCreatedBy($this->createdBy)
                ->setFile($attachment)
                ->store();
        }
    }

    /**
     * @param Leave $leave
     */
    private function sendPushToSubstitute(Leave $leave)
    {
        dispatch(new SendLeaveSubstitutionPushNotificationToEmployee($leave));
    }

    private function getManager($business_member)
    {
        $manager = $business_member->manager()->first();
        if ($manager) {
            array_push($this->managers, $manager->id);
            $this->getManager($manager);
        }
        return;
    }

    /**
     * @param $approval_flow
     * @param $department
     * @return array
     */
    private function calculateApprovers($approval_flow, $department)
    {
        $approvers = $approval_flow->approvers()->pluck('id')->toArray();
        $approver_within_my_manager = array_intersect($approvers, $this->managers);

        $my_department_users = [];
        BusinessRole::where('business_department_id', $department->id)->get()->each(function ($Business_role) use (&$my_department_users) {
            $my_department_users = array_merge($my_department_users, $Business_role->members()->pluck('id')->toArray());
        });
        $my_department_users = array_unique($my_department_users);
        $other_departments_approver = array_diff($approvers, $my_department_users);

        return array_diff($approver_within_my_manager + $other_departments_approver, [$this->businessMember->id]);
    }

    private function getLeftDays()
    {
        $business_total_leave_days_by_types = $this->businessMember->business->leaveTypes->where('id', $this->leaveTypeId)->first()->total_days;
        $used_days = $this->businessMember->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($this->leaveTypeId);
        return $business_total_leave_days_by_types - $used_days;
    }
}

