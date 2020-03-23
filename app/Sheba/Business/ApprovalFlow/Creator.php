<?php namespace Sheba\Business\ApprovalFlow;

use DB;
use Sheba\Dal\ApprovalFlow\Contract as ApprovalFlowRepositoryInterface;
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
    private $type;

    /**
     * Creator constructor.
     * @param ApprovalFlowRepositoryInterface $approval_flow_repo
     */
    public function __construct(ApprovalFlowRepositoryInterface $approval_flow_repo)
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

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param $business_department_id
     * @return $this
     */
    public function setBusinessDepartmentId($business_department_id)
    {
        $this->businessDepartmentId = $business_department_id;
        return $this;
    }

    /**
     * @param array $business_member_ids
     * @return $this
     */
    public function setBusinessMemberIds(array $business_member_ids)
    {
        $this->businessMemberIds = $business_member_ids;
        return $this;
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

    public function makeData()
    {
        $this->data = [
            'title' => $this->title,
            'type' => $this->type,
            'business_department_id' => $this->businessDepartmentId
        ];
    }
}
