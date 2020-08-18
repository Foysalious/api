<?php namespace App\Repositories;

use Illuminate\Http\Request;

Class BankUserNotificationRepository
{
    public function getBankUserNotifications($model, $offset, $limit)
    {
        $notifications = $model->notifications()->select('id', 'title', 'event_type', 'event_id', 'type', 'is_seen', 'created_at')->orderBy('id', 'desc')->skip($offset)->limit($limit)->get();
        if (count($notifications) > 0)
            $notifications = $notifications->map(function ($notification) {
                array_add($notification, 'time', $notification->created_at->diffForHumans());
                return $notification;
            });

        return $notifications;
    }
}