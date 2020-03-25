<?php namespace App\Sheba\Business\Leave;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use Carbon\Carbon;
use Exception;
use Sheba\Business\ApprovalRequest\Creator as ApprovalRequestCreator;
use Sheba\Dal\ApprovalFlow\Type;
use Sheba\Dal\Leave\EloquentImplementation as LeaveRepository;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\ModificationFields;
use Sheba\PartnerOrderRequest\Validators\CreateValidator;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Dal\Leave\Model as Leave;

class Creator
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $title;
    private $businessMember;
    private $leaveTypeId;
    private $leaveRepository;
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

    /**
     * Creator constructor.
     * @param LeaveRepository $leave_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ApprovalRequestCreator $approval_request_creator
     */
    public function __construct(LeaveRepository $leave_repo, BusinessMemberRepositoryInterface $business_member_repo,
                                ApprovalRequestCreator $approval_request_creator)
    {
        $this->leaveRepository = $leave_repo;
        $this->businessMemberRepository = $business_member_repo;
        $this->approval_request_creator = $approval_request_creator;
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
        $this->leaveTypeId = $leave_type_id;
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
            'business_member_id' => $this->businessMember->id,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'total_days' => $this->setTotalDays()
        ];

        $this->setModifier($this->businessMember->member);
        $leave = $this->leaveRepository->create($this->withCreateModificationField($data));
        $this->approval_request_creator->setBusinessMember($this->businessMember)
            ->setApproverId($this->approvers)
            ->setRequestable($leave)
            ->create();

        $this->notifySuperAdmins($leave);

        return $leave;
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
}

