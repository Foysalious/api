<?php namespace Sheba\Wallet;


use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class WalletUpdateEvent extends Event implements ShouldBroadcastNow
{
    use SerializesModels;
    public $data;
    /**
     * Create a new event instance.
     *
     * @param $data
     * @param $sender_id
     * @param $sender_type
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['wallet-update-channel'];
    }
}