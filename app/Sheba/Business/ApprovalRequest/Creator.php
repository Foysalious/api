<?php namespace Sheba\Business\ApprovalRequest;

use App\Jobs\Business\SendLeavePushNotificationToEmployee;
use App\Models\BusinessMember;
use App\Models\Member;
use Exception;
use Illuminate\Database\QueryException;
use Sheba\Dal\ApprovalRequest\Contract as ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;

class Creator
{
    use ModificationFields;
    const WHICH_PERSON_GET_NOTIFICATION_IN_THE_ORDER = 1;

    /** @var ApprovalRequestRepositoryInterface $approvalRequestRepo */
    private $approvalRequestRepo;
    private $approverId;
    private $requestableType;
    private $requestableId;
    /** @var PushNotificationHandler $pushNotificationHandler */
    private $pushNotificationHandler;
    private $member;
    private $isLeaveAdjustment;
    private $createdBy;

    /**
     * Creator constructor.
     * @param ApprovalRequestRepositoryInterface $approval_request_repo
     * @param PushNotificationHandler $push_notification_handler
     */
    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo, PushNotificationHandler $push_notification_handler)
    {
        $this->approvalRequestRepo = $approval_request_repo;
        $this->pushNotificationHandler = $push_notification_handler;
    }

    public function setBusinessMember($business_member)
    {
        $this->member = $business_member->member;
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

    public function setCreatedBy($created_by)
    {
        $this->createdBy = $created_by;
        return $this;
    }

    public function create()
    {
        foreach ($this->approverId as $order => $approver_id) {
            $data = $this->withCreateModificationField([
                'requestable_type' => $this->requestableType,
                'requestable_id' => $this->requestableId,
                'status' => $this->isLeaveAdjustment ? Status::ACCEPTED : Status::PENDING,
                'approver_id' => $approver_id,
                'order' => $order,
                'is_notified' => $order == self::WHICH_PERSON_GET_NOTIFICATION_IN_THE_ORDER ? 1 : 0
            ]);

            $approval_request = $this->approvalRequestRepo->create($data);
            # First approver will get notification this the order
            if (!$this->isLeaveAdjustment && $order == self::WHICH_PERSON_GET_NOTIFICATION_IN_THE_ORDER) {
                try {
                    $this->sendPushToApprover($approval_request);
                    $this->sendShebaNotificationToApprover($approval_request);
                } catch (Exception $e) {
                }
            }
        }
    }

    /**
     * @param ApprovalRequest $approval_request
     */
    private function sendPushToApprover(ApprovalRequest $approval_request)
    {
        $leave_applicant = $this->member->profile->name;
        dispatch(new SendLeavePushNotificationToEmployee($approval_request, $leave_applicant));
    }

    /**
     * @param ApprovalRequest $approval_request
     * @throws Exception
     */
    private function sendShebaNotificationToApprover(ApprovalRequest $approval_request)
    {
        /** @var BusinessMember $business_member */
        $business_member = $approval_request->approver;
        /** @var Member $member */
        $member = $business_member->member;
        $leave_applicant = $this->member->profile->name;

        $title = "$leave_applicant requested for a leave";
        notify()->member($member)->send([
            'title' => $title,
            'type' => 'Info',
            'event_type' => get_class($approval_request),
            'event_id' => $approval_request->id
        ]);
    }

}
