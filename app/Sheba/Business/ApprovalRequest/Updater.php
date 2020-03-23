<?php namespace Sheba\Business\ApprovalRequest;

use App\Models\BusinessMember;
use App\Models\Member;
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
    private $status;
    private $data;

    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo, Status $statuses)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        #$this->statuses = $statuses;
    }

    public function hasError()
    {
        #if (!in_array($this->status, $this->statuses)) return "Invalid Status!";
        if ($this->approvalRequest->approver_id != $this->businessMember->id) return "You are not authorized to  change the Status!";
        return false;
    }

    public function setMember($member)
    {
        $this->member = Member::findOrFail((int)$member);
        return $this;
    }

    public function setBusinessMember($business_member)
    {
        $this->businessMember = BusinessMember::findOrFail((int)$business_member);
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
