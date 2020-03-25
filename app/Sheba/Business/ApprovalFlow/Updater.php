<?php namespace Sheba\Business\ApprovalFlow;

use Sheba\Dal\ApprovalFlow\Contract as ApprovalFlowRepositoryInterface;
use DB;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;
    private $member;
    private $businessMembers;
    private $superAdmins;
    private $approval;
    private $title;
    private $businessDepartmentId;
    private $businessMemberIds;
    private $data = [];
    private $approvalFlowRepo;

    /**
     * Updater constructor.
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

    public function setApproval($approval)
    {
        $this->approval = $this->approvalFlowRepo->find($approval);
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setBusinessMemberIds(array $business_member_ids)
    {
        $this->businessMemberIds = $business_member_ids;
        return $this;
    }

    public function makeData()
    {
        $this->data = ['title' => $this->title];
    }

    public function update()
    {
        $this->makeData();
        DB::beginTransaction();
        $this->setModifier($this->member);
        $this->approvalFlowRepo->update($this->approval, $this->data);
        $this->approval->approvers()->sync($this->businessMemberIds);
        DB::commit();

        return $this->approval;
    }
}
