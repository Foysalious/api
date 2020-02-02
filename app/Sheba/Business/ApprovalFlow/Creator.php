<?php namespace Sheba\Business\ApprovalFlow;

use Illuminate\Support\Facades\DB;
use Sheba\Dal\TripRequestApprovalFlow\TripRequestApprovalFlowRepositoryInterface;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;
    private $member;
    private $businessMembers;
    private $superAdmins;
    private $title;
    private $businessDepartmentId;
    private $businessMemberIds;
    private $data = [];
    private $approvalFlowRepo;

    public function __construct(TripRequestApprovalFlowRepositoryInterface $approval_flow_repo)
    {
        $this->approvalFlowRepo = $approval_flow_repo;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setBusinessDepartmentId($business_department_id)
    {
        $this->businessDepartmentId = $business_department_id;
        return $this;
    }

    public function setBusinessMemberIds(array $business_member_ids)
    {
        $this->businessMemberIds = $business_member_ids;
        return $this;
    }

    public function makeData()
    {
        $this->data = [
            'title' => $this->title,
            'business_department_id' => $this->businessDepartmentId
        ];
    }

    public function store()
    {
        $this->makeData();
        DB::beginTransaction();
        $this->setModifier($this->member);
        $approval_flow = $this->approvalFlowRepo->create($this->data);
        $approval_flow->approvers()->sync($this->businessMemberIds);
        DB::commit();
        return $approval_flow;

    }

}
