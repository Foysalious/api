<?php namespace Sheba\Business\ApprovalRequest;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\ApprovalRequest\Type as ApprovalRequestType;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Status as LeaveStatus;
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
    /** @var Leave $requestable */
    private $requestable;
    private $requestableType;
    /** @var LeaveUpdater $leaveUpdater */
    private $leaveUpdater;

    /**
     * Updater constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     * @param LeaveUpdater $leave_updater
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo, LeaveUpdater $leave_updater)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->leaveUpdater = $leave_updater;
    }

    public function hasError()
    {
        if (!in_array($this->status, Status::get())) return "Invalid Status!";
        if ($this->approvalRequest->approver_id != $this->businessMember->id) return "You are not authorized to change the Status!";
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
        $this->member = $business_member->member;
        return $this;
    }

    public function setApprovalRequest(ApprovalRequest $approval)
    {
        $this->approvalRequest = $approval;
        $this->requestable = $this->approvalRequest->requestable;
        $this->requestableType = ApprovalRequestType::getByModel($this->requestable);

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

        if ($this->requestableType == ApprovalRequestType::LEAVE) {
            $leave = $this->requestable;
            if ($leave->status != LeaveStatus::REJECTED) {
                if ($this->status == Status::REJECTED)
                    $this->leaveUpdater->setLeave($leave)->setStatus(LeaveStatus::REJECTED)->setBusinessMember($this->businessMember)->updateStatus();

                if ($this->status == Status::ACCEPTED && $leave->isAllRequestAccepted())
                    $this->leaveUpdater->setLeave($leave)->setStatus(LeaveStatus::ACCEPTED)->setBusinessMember($this->businessMember)->updateStatus();
            }
        }
    }
}
