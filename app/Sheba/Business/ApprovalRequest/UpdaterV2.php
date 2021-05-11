<?php namespace Sheba\Business\ApprovalRequest;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use Sheba\Business\ApprovalRequest\Creator as ApprovalRequestCreator;
use Sheba\Business\ApprovalSetting\FindApprovalSettings;
use Sheba\Business\ApprovalSetting\FindApprovers;
use Sheba\Business\LeaveRejection\Requester as LeaveRejectionRequester;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\ApprovalRequest\Type as ApprovalRequestType;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\Dal\Leave\Status as LeaveStatus;
use Sheba\ModificationFields;

class UpdaterV2
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

    /** @var FindApprovalSettings $findApprovalSetting */
    private $findApprovalSetting;
    /** @var FindApprovers $findApprovers */
    private $findApprovers;
    private $approvers = [];
    /** @var ApprovalRequestCreator approvalRequestCreator */
    private $approvalRequestCreator;
    /**
     * @var array
     */
    private $remainingApprovers;
    private $requestableBusinessMember;
    /** @var LeaveRejectionRequester $leaveRejectionRequester */
    private $leaveRejectionRequester;

    /**
     * Updater constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     * @param LeaveUpdater $leave_updater
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo, LeaveUpdater $leave_updater)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->leaveUpdater = $leave_updater;
        $this->findApprovalSetting = app(FindApprovalSettings::class);
        $this->findApprovers = app(FindApprovers::class);
        $this->approvalRequestCreator = app(ApprovalRequestCreator::class);
        $this->approvers = [];
        $this->remainingApprovers = [];
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

        /** @var BusinessMember */
        $this->requestableBusinessMember = $this->requestable->businessMember;
        $approval_setting = $this->findApprovalSetting->getApprovalSetting($this->requestableBusinessMember, $this->requestableType);
        $this->approvers = $this->findApprovers->calculateApprovers($approval_setting, $this->requestableBusinessMember);
        $requestable_approval_request_ids = $this->requestable->requests()->pluck('approver_id', 'id')->toArray();
        $this->remainingApprovers = array_diff($this->approvers, $requestable_approval_request_ids);
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setLeaveRejectionRequester(LeaveRejectionRequester $leave_rejection_requester)
    {
        $this->leaveRejectionRequester = $leave_rejection_requester;
        return $this;
    }

    public function change()
    {
        $this->setModifier($this->member);
        $data = ['status' => $this->status];
        $this->approvalRequestRepo->update($this->approvalRequest, $this->withUpdateModificationField($data));
        $this->approvalRequest->fresh();
        $leave = $this->requestable;

        if ($this->requestableType == ApprovalRequestType::LEAVE && $this->approvalRequest->status == Status::ACCEPTED) {

            if (count($this->remainingApprovers) > 0) {
                /** $first_approver */
                $first_approver = reset($this->remainingApprovers);
                $this->approvalRequestCreator->setBusinessMember($this->requestableBusinessMember)
                    ->setApprover($first_approver)
                    ->setRequestable($this->requestable)
                    ->create();
            }

            if (count($this->remainingApprovers) == 0 && $leave->status == LeaveStatus::PENDING) {
                $this->leaveUpdater->setLeave($leave)->setStatus(LeaveStatus::ACCEPTED)->setBusinessMember($this->businessMember)->updateStatus();
            }
        }

        if ($this->requestableType == ApprovalRequestType::LEAVE && $this->approvalRequest->status == Status::REJECTED && $leave->status == LeaveStatus::PENDING) {
            $this->leaveUpdater->setLeave($leave)
                ->setStatus(LeaveStatus::REJECTED)
                ->setBusinessMember($this->businessMember)
                ->setLeaveRejectionRequester($this->leaveRejectionRequester)
                ->updateStatus();
        }
    }
}

