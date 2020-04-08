<?php


namespace App\Sheba\Affiliate\PushNotification;
use App\Models\TopUpOrder;

class TopUpFailed extends PushNotification
{
    public function __construct(TopUpOrder $top_up_order)
    {
        parent::__construct();
        $this->affiliate_id =  $top_up_order->agent_id;
        $this->title = 'Top Up Failed';
        $this->message = 'দুঃখিত! আপনার মোবাইল রিচার্জ '.$top_up_order->payee_mobile.' সফল হয় নি, পুনরায় চেষ্টা করুন';
        $this->eventType = 'topup_failed';
        $this->link = 'topup_failed';
    }


}