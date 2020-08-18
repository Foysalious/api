<?php namespace Sheba\Notification;


use App\Models\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SeenBy
{
    /** @var array */
    private $notifications;
    /** @var Model */
    private $user;

    public function setNotifications($notification)
    {
        $this->notifications = $notification;
        return $this;
    }

    public function setUser(Model $user)
    {
        $this->user = $user;
        return $this;
    }

    public function seen()
    {
        $this->notifications=array_map('intval',  $this->notifications);
        $notifications = $this->user->notifications->where('is_seen',0)->whereIn('id', $this->notifications);
        foreach ($notifications as $notification) {
            $this->setSeen($notification);
        }
    }
    public function setSeen(Notification $notification){
        $notification->timestamps = false;
        $notification->is_seen = 1;
        $notification->save();
    }
}
