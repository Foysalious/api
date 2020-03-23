<?php namespace Sheba\Business\ApprovalRequest;

use Sheba\Dal\ApprovalRequest\ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Illuminate\Support\Facades\DB;
use Sheba\Helpers\ConstGetter;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;
    use ConstGetter;

    private $approvalRequestRepo;
    private $approvalRequest;
    private $member;
    private $businessMember;
    private $statuses;
    private $data;

    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo, Status $status)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->statuses = $status;
    }

    public function hasError()
    {
        if (!in_array($this->data['status'], $this->statuses)) return "Invalid Status!";
        if ($this->approvalRequest->business_member_id != $this->businessMember->id) return "You are not authorized to  change the Status!";
        return false;
    }

    public function setMember($member)
    {
        $this->member = $member;
        return $this;
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setApprovalRequest($approval)
    {
        $this->approvalRequest = ApprovalRequest::findOrFail($approval);
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function change()
    {
        $this->setModifier($this->member);
        $data = ['status' => $this->data['status']];
        $this->approvalRequestRepo->update($this->approvalRequest, $this->withUpdateModificationField($data));
    }
}
