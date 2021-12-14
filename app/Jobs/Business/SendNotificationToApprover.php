<?php namespace App\Jobs\Business;

use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;

class SendNotificationToApprover extends BusinessQueue
{
    /** @var ApprovalRequest $approvalRequest */
    private $approvalRequest;
    /** @var Member $member */
    private $member;
    /** @var Profile $profile */
    private $profile;

    /**
     * @param ApprovalRequest $approval_request
     * @param Profile $profile
     */
    public function __construct(ApprovalRequest $approval_request, Profile $profile)
    {
        $this->approvalRequest = $approval_request;
        $business_member = $this->approvalRequest->approver;
        $this->member = $business_member->member;
        $this->profile = $profile;

        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() > 1) return;

        $leave_applicant = $this->profile->name ? $this->profile->name : 'n/s';
        $title = "$leave_applicant requested for a leave";

        notify()->member($this->member)->send([
            'title' => $title,
            'type' => 'info',
            'event_type' => get_class($this->approvalRequest),
            'event_id' => $this->approvalRequest->id
        ]);
    }
}