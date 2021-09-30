<?php namespace Sheba\Business\ApprovalRequest;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\Leave\Updater as LeaveUpdater;
use Exception;
use Sheba\Business\LeaveRejection\Requester as LeaveRejectionRequester;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepo;
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
    /** @var LeaveRejectionRequester $leaveRejectionRequester */
    private $leaveRejectionRequester;
    private $notification;

    private $requestableBusinessMember;
    private $requestableMember;
    private $requestableProfile;


    public function __construct(ApprovalRequestRepo $approval_request_repo, LeaveUpdater $leave_updater, Notification $notification)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->leaveUpdater = $leave_updater;
        $this->notification = $notification;
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

        $this->requestableBusinessMember = $this->requestable->businessMember;
        $this->requestableMember = $this->requestableBusinessMember->member;
        $this->requestableProfile = $this->requestableMember->profile;
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

    /**
     * @throws Exception
     */
    public function change()
    {
        $this->setModifier($this->member);
        $data = ['status' => $this->status];
        $this->approvalRequestRepo->update($this->approvalRequest, $this->withUpdateModificationField($data));
        $this->approvalRequest->fresh();

        $leave = $this->requestable;

        if ($this->requestableType == ApprovalRequestType::LEAVE && $this->approvalRequest->status == Status::ACCEPTED) {
            $remaining_approval_request = $leave->requests()->orderBy('order', 'ASC')->where('is_notified', 0)->first();

            if ($remaining_approval_request) {
                $this->approvalRequestRepo->update($remaining_approval_request, $this->withUpdateModificationField(['is_notified' => 1]));
                try {
                    $this->notification->sendPushToApprover($remaining_approval_request, $this->requestableProfile);
                    $this->notification->sendShebaNotificationToApprover($remaining_approval_request, $this->requestableProfile);
                } catch (Exception $e) {
                }
            }
            if (!$remaining_approval_request && $leave->status == LeaveStatus::PENDING) {
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

