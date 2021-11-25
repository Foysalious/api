<?php namespace App\Jobs\Business;

use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Leave\LeaveStatusPresenter as LeaveStatusPresenter;

class WebPushToLeaveCreator extends BusinessQueue
{
    public function __construct($leave)
    {
        $this->leave = $leave;
        parent::__construct();
    }

    public function handle()
    {
        $status = LeaveStatusPresenter::statuses()[$this->leave->status];
        $business_member = $this->leave->businessMember;

        $sheba_notification_data = [
            'title' => "Your leave request has been $status",
            'type' => 'Info',
            'event_type' => 'Sheba\Dal\Leave\Model',
            'event_id' => $this->leave->id,
            /*'link' => config('sheba.business_url') . '/dashboard/employee/leaves/'.$this->leave->id*/
        ];
        notify()->member($business_member->member)->send($sheba_notification_data);
    }
}