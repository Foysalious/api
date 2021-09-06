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
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Http\UploadedFile;
use Sheba\Business\ApprovalRequest\Creator as ApprovalRequestCreator;
use Sheba\Business\ApprovalSetting\FindApprovalSettings;
use Sheba\Business\ApprovalSetting\FindApprovers;
use Sheba\Business\ApprovalSetting\MakeDefaultApprovalSetting;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\ApprovalSetting\ApprovalSetting;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingApprover\Types;
use Sheba\Dal\ApprovalSettingModule\Modules;
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
    private $approvers = [];
    /** @var TimeFrame $timeFrame */
    private $timeFrame;
    private $note;
    private $approverId;
    private $isLeaveAdjustment;
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
    /** @var FindApprovalSettings $findApprovalSetting */
    private $findApprovalSetting;
    /** @var FindApprovers $findApprovers */
    private $findApprovers;

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
        $this->findApprovalSetting = app(FindApprovalSettings::class);
        $this->findApprovers = app(FindApprovers::class);
        $this->approvers = [];
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
        if ($this->isLeaveAdjustment) return $this;

        if ($this->substitute == $this->businessMember->id) {
            $this->setError(422, 'You can\'t be your own substitute!');
            return $this;
        }
        /** @Var ApprovalSetting $approval_setting */
        $approval_setting = $this->findApprovalSetting->getApprovalSetting($this->businessMember, Modules::LEAVE);

        $this->approvers = $this->findApprovers->calculateApprovers($approval_setting, $this->businessMember);

        if (count($this->approvers) == 0) $this->setError(422, 'No approval flow is defined for you due to wrong approval flow setup.');
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

    public function setApprover($approver_id)
    {
        $this->approvers = $approver_id;
        return $this;
    }

    public function setIsLeaveAdjustment($is_leave_adjustment = false)
    {
        $this->isLeaveAdjustment = $is_leave_adjustment;
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
            'half_day_configuration' => $this->isHalfDay ? $this->halfDayConfigure : null,
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
                ->setIsLeaveAdjustment($this->isLeaveAdjustment)
                ->create();
            $this->createAttachments($leave);
        });

        if ($leave->substitute_id) $this->sendPushToSubstitute($leave);

        return $leave;
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

    private function getLeftDays()
    {
        $business_total_leave_days_by_types = $this->businessMember->getTotalLeaveDaysByLeaveTypes($this->leaveTypeId);
        $used_days = $this->businessMember->getCountOfUsedLeaveDaysByTypeOnAFiscalYear($this->leaveTypeId);
        return $business_total_leave_days_by_types - $used_days;
    }
}

