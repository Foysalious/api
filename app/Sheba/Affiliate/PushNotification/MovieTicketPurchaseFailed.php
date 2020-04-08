<?php


namespace App\Sheba\Affiliate\PushNotification;


use App\Models\MovieTicketOrder;

class MovieTicketPurchaseFailed extends PushNotification
{
    public function __construct(MovieTicketOrder $movie_ticket_order)
    {
        parent::__construct();
        $this->affiliate_id =  $movie_ticket_order->agent_id;
        $this->title = 'Movie Ticket Purchase Failed';
        $this->message = 'দুঃখিত বন্ধু! আপনার মুভি টিকেট সফল ভাবে কাটা হয় নি '. $movie_ticket_order->reserver_mobile . ', পুনরায় চেষ্টা করুন। ';
        $this->eventType = 'movie_ticket_purchase_failed';
        $this->link = 'movie_ticket_purchase_failed';
    }

}