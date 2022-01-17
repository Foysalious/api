<?php namespace Sheba\Business\ApprovalRequest;

use App\Jobs\Business\SendLeavePushNotificationToEmployee;
use App\Jobs\Business\SendNotificationToApprover;
use Sheba\Dal\ApprovalRequest\Model as ApprovalRequest;
use App\Models\Profile;
use Exception;

class Notification
{
    /**
     * @param ApprovalRequest $approval_request
     * @param Profile $profile
     */
    public function sendPushToApprover(ApprovalRequest $approval_request, Profile $profile)
    {
        $leave_applicant = $profile->name ? $profile->name : 'n/s';
        dispatch(new SendLeavePushNotificationToEmployee($approval_request, $leave_applicant));
    }

    /**
     * @param ApprovalRequest $approval_request
     * @param Profile $profile
     * @throws Exception
     */
    public function sendShebaNotificationToApprover(ApprovalRequest $approval_request, Profile $profile)
    {
        dispatch(new SendNotificationToApprover($approval_request, $profile));
    }
}