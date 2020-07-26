<?php namespace App\Sheba\Business\Leave;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Attachments\Attachments;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Sheba\Business\ApprovalRequest\Creator as ApprovalRequestCreator;
use Sheba\Dal\ApprovalFlow\Type;
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
    private $substitute;
    private $createdBy;
    /** @var UploadedFile[] */
    private $attachments = [];
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;

    /**
     * Creator constructor.
     * @param LeaveRepository $leave_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ApprovalRequestCreator $approval_request_creator
     * @param TimeFrame $time_frame
     * @param Attachments $attachment_manager
     * @param PushNotificationHandler $push_notification_handler
     */
    public function __construct(LeaveRepository $leave_repo, BusinessMemberRepositoryInterface $business_member_repo,
                                ApprovalRequestCreator $approval_request_creator,
                                TimeFrame $time_frame, Attachments $attachment_manager,
                                PushNotificationHandler $push_notification_handler)
    {
        $this->leaveRepository = $leave_repo;
        $this->businessMemberRepository = $business_member_repo;
        $this->approval_request_creator = $approval_request_creator;
        $this->timeFrame = $time_frame;
        $this->attachmentManager = $attachment_manager;
        $this->pushNotificationHandler = $push_notification_handler;
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
        $this->businessMember = $business_member;
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
        return $this->endDate->diffInDays($this->startDate) + 1;
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

    private function createAttachments(Leave $leave)
    {
        foreach ($this->attachments as $attachment) {
            $this->attachmentManager->setAttachableModel($leave)
                ->setCreatedBy($this->createdBy)
                ->setFile($attachment)
                ->store();
        }
    }

    public function sendPushToSubstitute(Leave $leave)
    {
        /** @var BusinessMember $business_member */
        $business_member = $leave->businessMember;
        /** @var BusinessMember $substitute_business_member */
        $substitute_business_member = BusinessMember::findOrFail($leave->substitute_id);
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $leave_applicant = $member->profile->name;
        $topic = config('sheba.push_notification_topic_name.employee') . (int)$substitute_business_member->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');

        $notification_data = [
            "title" => 'Substitute Setup',
            "message" => "$leave_applicant choose you a substitute.",
            "event_type" => 'leave',
            "event_id" => $leave->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        $this->pushNotificationHandler->send($notification_data, $topic, $channel);
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

