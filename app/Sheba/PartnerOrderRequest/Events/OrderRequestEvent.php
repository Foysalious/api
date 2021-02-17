<?php


namespace Sheba\PartnerOrderRequest\Events;


use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class OrderRequestEvent extends Event implements ShouldBroadcastNow
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function broadcastOn()
    {
        // TODO: Implement broadcastOn() method.
        return ['partner-order-notification-channel'];
    }
}