<?php namespace App\Sheba\Business\Leave;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
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
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Leave\Model as Leave;
use DB;

class Creator
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $title;
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
    private $createdBy;
    /** @var UploadedFile[] */
    private $attachments = [];

    /**
     * Creator constructor.
     * @param LeaveRepository $leave_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ApprovalRequestCreator $approval_request_creator
     * @param TimeFrame $time_frame
     * @param Attachments $attachment_manager
     */
    public function __construct(LeaveRepository $leave_repo, BusinessMemberRepositoryInterface $business_member_repo,
                                ApprovalRequestCreator $approval_request_creator,
                                TimeFrame $time_frame, Attachments $attachment_manager)
    {
        $this->leaveRepository = $leave_repo;
        $this->businessMemberRepository = $business_member_repo;
        $this->approval_request_creator = $approval_request_creator;
        $this->timeFrame = $time_frame;
        $this->attachmentManager = $attachment_manager;
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

        /** @var BusinessDepartment $department */
        $department = $this->businessMember->department();
        if (!$department) {
            $this->setError(422, 'Department not set yet!');
            return $this;
        }

        $approval_flow = $department->approvalFlowBy(Type::LEAVE);
        if (!$approval_flow) {
            $this->setError(422, 'No Approver set yet!');
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
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'total_days' => $this->setTotalDays(),
            'left_days' => $this->getLeftDays()
        ];
        DB::transaction(function () use ($data) {
            $this->setModifier($this->businessMember->member);
            $leave = $this->leaveRepository->create($this->withCreateModificationField($data));
            $this->approval_request_creator->setBusinessMember($this->businessMember)
                ->setApproverId($this->approvers)
                ->setRequestable($leave)
                ->create();
            $this->createAttachments($leave);
            $this->notifySuperAdmins($leave);
            return $leave;
        });
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

    /**
     * @param Leave $leave
     * @throws Exception
     */
    private function notifySuperAdmins(Leave $leave)
    {
        $super_admins = $this->businessMemberRepository
            ->where('is_super', 1)
            ->where('business_id', $this->businessMember->business_id)
            ->get();

        foreach ($super_admins as $super_admin) {
            $title = $this->businessMember->member->profile->name . ' #' . $this->businessMember->member->id . ' has created a Leave Request';
            notify()->member($super_admin->member)->send([
                'title' => $title,
                'type' => 'Info',
                'event_type' => 'Sheba\Dal\Leave\Model',
                'event_id' => $leave->id
            ]);
        }
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

        $my_department_users = $department->businessRoles()->where('id', $this->businessMember->business_role_id)->first()->members()->pluck('id')->toArray();
        $other_departments_approver = array_diff($approvers, $my_department_users);

        return array_diff($approver_within_my_manager + $other_departments_approver, [$this->businessMember->id]);
    }

    private function getLeftDays()
    {
        /**
         * STATIC NOW, NEXT SPRINT COMES FROM DB
         */
        $business_fiscal_start_month = 7;
        $leave_lefts = 0;
        $this->timeFrame->forAFiscalYear(Carbon::now(), $business_fiscal_start_month);

        $leaves = $this->businessMember->leaves()->accepted()->between($this->timeFrame)->with('leaveType')->whereHas('leaveType', function ($leave_type) use (&$leave_lefts) {
            return $leave_type->where('id', $this->leaveTypeId);
        })->get();

        $business_total_leave_days_by_types = $this->businessMember->business->leaveTypes->where('id', $this->leaveTypeId)->first()->total_days;
        $leaves->each(function ($leave) use (&$leave_lefts) {
            $start_date = $leave->start_date->lt($this->timeFrame->start) ? $this->timeFrame->start : $leave->start_date;
            $end_date = $leave->end_date->gt($this->timeFrame->end) ? $this->timeFrame->end : $leave->end_date;

            $leave_lefts += $end_date->diffInDays($start_date) + 1;
        });

        return $business_total_leave_days_by_types - $leave_lefts;
    }
}

