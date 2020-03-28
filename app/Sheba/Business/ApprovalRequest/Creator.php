<?php namespace Sheba\Business\ApprovalRequest;

use Sheba\Dal\ApprovalRequest\ApprovalRequestRepositoryInterface;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\Dal\ApprovalRequest\Status;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;

class Creator
{
    use ModificationFields;

    /** @var ApprovalRequestRepositoryInterface $approvalRequestRepo */
    private $approvalRequestRepo;
    private $approverId;
    private $requestableType;
    private $requestableId;
    /** @var PushNotificationHandler $pushNotificationHandler*/
    private $pushNotificationHandler;
    private $member;

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

    public function create()
    {
        foreach ($this->approverId as $approver_id) {
            $data = $this->withCreateModificationField([
                'requestable_type' => $this->requestableType,
                'requestable_id' => $this->requestableId,
                'status' => Status::PENDING,
                'approver_id' => $approver_id
            ]);
            $approval_request = $this->approvalRequestRepo->create($data);
            $this->sendPushToApprover($approval_request);
        }
    }

    /**
     * @param ApprovalRequest $approval_request
     */
    public function sendPushToApprover(ApprovalRequest $approval_request)
    {
        $topic = config('sheba.push_notification_topic_name.employee') . (int)$this->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $notification_data = [
            "title" => 'New Leave Request Arrived',
            "message" => "Leave Request Arrived Message",
            "event_type" => 'leave_request',
            "event_id" => $approval_request->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];
        $this->pushNotificationHandler->send($notification_data, $topic, $channel);
    }
}
