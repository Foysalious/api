<?php namespace Sheba\Notification;

use App\Events\Event;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class NotificationCreated extends Event implements ShouldBroadcastNow
{

    use SerializesModels;

    public $notificationData;
    public $senderType;
    public $senderId;

    /**
     * Create a new event instance.
     *
     * @param $data
     * @param $sender_id
     * @param $sender_type
     */
    public function __construct($data, $sender_id = null, $sender_type = null)
    {
        $this->notificationData = $data;
        $this->senderId = $sender_id ?: Auth::user()->id;
        $this->senderType = $sender_type ?: "App\\Models\\User";
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['notification-channel'];
    }
}
