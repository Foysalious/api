<?php namespace Sheba\Business\ApprovalRequest;

use App\Models\BusinessMember;
use App\Models\Member;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    private $approvalRequestRepo;
    private $approvalRequest;
    private $member;
    private $businessMember;
    private $status;
    private $data;

    /**
     * Updater constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->approvalRequestRepo = $approval_request_repo;
    }

    public function hasError()
    {
        if (!in_array($this->status, Status::get())) return "Invalid Status!";
        if ($this->approvalRequest->approver_id != $this->businessMember->id) return "You are not authorized to  change the Status!";
        return false;
    }

    public function setMember(Member $member)
    {
        $this->member = $member;
        return $this;
    }

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setApprovalRequest(ApprovalRequest $approval)
    {
        $this->approvalRequest = $approval;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function change()
    {
        $this->setModifier($this->member);
        $data = ['status' => $this->status];
        $this->approvalRequestRepo->update($this->approvalRequest, $this->withUpdateModificationField($data));
    }
}
