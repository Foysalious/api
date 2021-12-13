<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;

class SendNotificationToApprover extends BusinessQueue
{
    public function __construct(ApprovalRequest $approval_request, Profile $profile)
    {
        $this->approvalRequest = $approval_request;
        /** @var BusinessMember $business_member */
        $business_member = $this->approvalRequest->approver;
        /**@var Member $member */
        $this->member = $business_member->member;
        $this->profile = $profile;
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            try {
                $leave_applicant = $this->profile->name ? $this->profile->name : 'n/s';
                $title = "$leave_applicant requested for a leave";

                notify()->member($this->member)->send([
                    'title' => $title,
                    'type' => 'info',
                    'event_type' => get_class($this->approvalRequest),
                    'event_id' => $this->approvalRequest->id
                ]);
            }catch (\Throwable $e){
                dd($e->getTraceAsString());
            }
        }
    }
}