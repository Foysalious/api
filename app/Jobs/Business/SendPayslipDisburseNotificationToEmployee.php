<?php namespace App\Jobs\Business;

use App\Models\BusinessMember;
use App\Sheba\Business\BusinessQueue;
use Sheba\Dal\Payslip\Payslip;
use Sheba\PushNotificationHandler;

class SendPayslipDisburseNotificationToEmployee extends BusinessQueue
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
        if ($this->attempts() < 2) {
            $title = "Your salary for ".$this->payslip->schedule_date->format('M Y')." has been disbursed";
            notify()->member($this->businessMember->member)->send([
                'title' => $title,
                'type' => 'Info',
                'event_type' => get_class($this->payslip),
                "event_id" => $this->payslip->id,
            ]);
        }
    }

}