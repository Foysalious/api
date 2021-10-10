<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Payslip\Payslip;
use Sheba\PushNotificationHandler;

class SendPayslipDisbursePushNotificationToEmployee extends BusinessQueue
{
    /**
     * @var PushNotificationHandler
     */
    private $pushNotification;
    /**
     * @var BusinessMember
     */
    private $businessMember;
    /**
     * @var Payslip
     */
    private $payslip;

    public function __construct(BusinessMember $business_member, Payslip $payslip)
    {
        $this->businessMember = $business_member;
        $this->payslip = $payslip;
        $this->pushNotification = new PushNotificationHandler();
        parent::__construct();
    }

    public function handle()
    {

        $topic = config('sheba.push_notification_topic_name.employee') . (int)$this->businessMember->member->id;
        $channel = config('sheba.push_notification_channel_name.employee');
        $sound = config('sheba.push_notification_sound.employee');
        $notification_data = [
            "title" => "Payslip Disbursement",
            "message" => "Your salary for " . $this->payslip->schedule_date->format('M Y') . " has been disbursed. Find your payslip here",
            "event_type" => 'payslip',
            "event_id" => $this->payslip->id,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];
        $this->pushNotification->send($notification_data, $topic, $channel, $sound);

    }

}