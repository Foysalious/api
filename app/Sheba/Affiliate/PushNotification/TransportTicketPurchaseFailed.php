<?php


namespace App\Sheba\Affiliate\PushNotification;


use App\Models\Transport\TransportTicketOrder;

class TransportTicketPurchaseFailed extends PushNotification
{
    public function __construct($affiliate_id,$reserver_mobile)
    {
        $this->affiliate_id =  $affiliate_id;
        $this->title = 'Bus Ticket Purchase Failed';
        $this->message = 'দুঃখিত বন্ধু! আপনার বাস টিকেট সফল ভাবে কাটা হয় নি '. $reserver_mobile . ', পুনরায় চেষ্টা করুন। ';
        $this->eventType = 'transport_ticket_purchase_failed';
        $this->link = 'transport_ticket_purchase_failed';
    }

}