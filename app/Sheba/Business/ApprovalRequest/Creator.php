<?php namespace Sheba\Business\ApprovalRequest;

use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepo;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\ModificationFields;
use Exception;

class Creator
{
    use ModificationFields;

    private $approvalRequestRepo;
    private $approverId;
    private $requestableType;
    private $requestableId;
    private $member;
    private $isLeaveAdjustment;
    private $notification;
    private $profile;


    /**
     * @param ApprovalRequestRepo $approval_request_repo
     * @param Notification $notification
     */
    public function __construct(ApprovalRequestRepo $approval_request_repo, Notification $notification)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->notification = $notification;
    }

    public function setBusinessMember($business_member)
    {
        $this->member = $business_member->member;
        $this->profile = $this->member->profile;
        $this->setModifier($this->member);
        return $this;
    }

    /**
     * @param $requestable
     * @return $this
     */
    public function setRequestable($requestable)
    {
        $this->requestableType = get_class($requestable);
        $this->requestableId = $requestable->id;
        return $this;
    }

    /**
     * @param array $approver_id
     * @return $this
     */
    public function setApproverId(array $approver_id)
    {
        $this->approverId = $approver_id;
        return $this;
    }

    public function setIsLeaveAdjustment($is_leave_adjustment = false)
    {
        $this->isLeaveAdjustment = $is_leave_adjustment;
        return $this;
    }

    public function create()
    {
        $first_approver_key = key($this->approverId); // When Department Head not found order doesn't start from 1, so is notified becomes 0, to fix that issue this implementation
        foreach ($this->approverId as $order => $approver_id) {
            $data = $this->withCreateModificationField([
                'requestable_type' => $this->requestableType,
                'requestable_id' => $this->requestableId,
                'status' => $this->isLeaveAdjustment ? Status::ACCEPTED : Status::PENDING,
                'approver_id' => $approver_id,
                'order' => $order,
                'is_notified' => $first_approver_key == $order ? 1 : 0
            ]);

            $approval_request = $this->approvalRequestRepo->create($data);
            # First approver will get notification this the order
            if (!$this->isLeaveAdjustment && $first_approver_key == $order) {
                try {
                    $this->notification->sendPushToApprover($approval_request, $this->profile);
                    $this->notification->sendShebaNotificationToApprover($approval_request, $this->profile);
                } catch (Exception $e) {
                }
            }
        }
    }
}
