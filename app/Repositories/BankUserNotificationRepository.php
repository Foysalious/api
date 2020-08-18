<?php namespace App\Repositories;

use App\Models\Notification;
use App\Models\OfferShowcase;
use Illuminate\Http\Request;

Class BankUserNotificationRepository extends NotificationRepository
{
    public function getBankUserNotifications($model, $offset, $limit)
    {
        $notifications = $model->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
        if (count($notifications) > 0)
            $notifications = $notifications->map(function ($notification) {
                array_add($notification, 'time', $notification->created_at->diffForHumans());
                $icon = $this->getNotificationIcon($notification->event_id, $notification->type);
                array_add($notification, 'icon', $icon);
                return $notification;
            });
        $count = $model->notifications()->where('is_seen', '0')->count();
        return ["notifications" => $notifications, 'unseen_notifications_count' => $count];
    }

    public function setNotificationSeen($id)
    {
        Notification::where('id',$id)->update(['is_seen' => 1]);
        return "Success";
    }
}
