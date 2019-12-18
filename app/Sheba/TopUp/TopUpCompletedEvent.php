<?php namespace Sheba\TopUp;

use App\Events\Event;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class TopUpCompletedEvent extends Event implements ShouldBroadcastNow
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
        return ['topup-complete-channel'];
    }
}