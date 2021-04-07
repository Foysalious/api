<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Leave\Model as Leave;
use Sheba\PushNotificationHandler;

class SendLeaveSubstitutionPushNotificationToEmployee extends BusinessQueue
{
    private $pushNotification;
    /** @var Leave $leave */
    private $leave;

    /**
     * SendLeaveSubstitutionPushNotificationToEmployee constructor.
     * @param Leave $leave
     */
    public function __construct(Leave $leave)
    {
        $this->leave = $leave;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {
        if ($this->attempts() < 2) {
            /** @var BusinessMember $business_member */
            $business_member = $this->leave->businessMember;
            /** @var BusinessMember $substitute_business_member */
            $substitute_business_member = $this->leave->substitute;
            /** @var Member $member */
            $member = $business_member->member;
            $leave_applicant = $member->profile->name;

            $topic = config('sheba.push_notification_topic_name.employee') . (int)$substitute_business_member->member->id;
            $channel = config('sheba.push_notification_channel_name.employee');
            $sound = config('sheba.push_notification_sound.employee');
            $start_date = $this->leave->start_date->format('d/m/Y');
            $end_date = $this->leave->end_date->format('d/m/Y');
            $notification_data = [
                "title" => 'Leave substitute',
                "message" => "You have been chosen as $leave_applicant's substitute from $start_date to $end_date",
                "event_type" => 'substitute',
                "event_id" => $this->leave->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ];

            $this->pushNotification->send($notification_data, $topic, $channel, $sound);
        }
    }
}
