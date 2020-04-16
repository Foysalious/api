<?php


namespace App\Sheba\Affiliate\PushNotification;
use App\Models\TopUpOrder;

class TopUpFailed extends PushNotification
{
    public function __construct($affiliate_id,$payee_mobile)
    {
        $this->affiliate_id =  $affiliate_id;
        $this->title = 'Top Up Failed';
        $this->message = 'দুঃখিত! আপনার মোবাইল রিচার্জ '.$payee_mobile.' সফল হয় নি, পুনরায় চেষ্টা করুন';
        $this->eventType = 'topup_failed';
        $this->link = 'topup_failed';
    }


}