<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use Sheba\PushNotificationHandler;

class SendLeavePushNotificationToEmployee extends BusinessQueue
{
    private $pushNotification;
    /** @var ApprovalRequest $approvalRequest */
    private $approvalRequest;
    private $leaveApplicant;
    /** @var BusinessMember $businessMember */
    private $businessMember;

    /**
     * SendLeavePushNotificationToEmployee constructor.
     * @param ApprovalRequest $approval_request
     * @param $leave_applicant
     */
    public function __construct(ApprovalRequest $approval_request, $leave_applicant)
    {
        $this->approvalRequest  = $approval_request;
        $this->businessMember   = $approval_request->approver;
        $this->leaveApplicant   = $leave_applicant;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            $topic = config('sheba.push_notification_topic_name.employee') . (int)$this->businessMember->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound  = config('sheba.push_notification_sound.employee');
            $notification_data = [
                "title" => 'Leave request',
                "message" => "$this->leaveApplicant requested for a leave which needs your approval",
                "event_type" => 'leave_request',
                "event_id" => $this->approvalRequest->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            $this->pushNotification->send($notification_data, $topic, $channel, $sound);
        }
    }
}
