<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;

class SendNotificationToWebPortal extends BusinessQueue
{
    public function __construct(ApprovalRequest $approval_request, Profile $profile)
    {
        $this->approvalRequest = $approval_request;
        $this->profile = $profile;
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            /** @var BusinessMember $business_member */
            $business_member = $this->approvalRequest->approver;
            /** @var Member $member */
            $member = $business_member->member;
            $leave_applicant = $this->profile->name ? $this->profile->name : 'n/s';
            $title = "$leave_applicant requested for a leave";

            notify()->member($member)->send([
                'title' => $title,
                'type' => 'Info',
                'event_type' => get_class($this->approvalRequest),
                'event_id' => $this->approvalRequest->id
            ]);
        }
    }
}