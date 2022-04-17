<?php

namespace App\Sheba\AccountingEntry\Notifications;

use Sheba\PushNotification\PushNotificationHandler as PusNotificationService;
use Sheba\PushNotificationHandler;

class ReminderNotificationHandler
{
    protected $reminder;

    /**
     * @param mixed $reminder
     */
    public function setReminder($reminder): ReminderNotificationHandler
    {
        $this->reminder = $reminder;
        return $this;
    }

    public function handler()
    {
        $topic = config('sheba.push_notification_topic_name.manager_new') . $this->reminder['partner_id'];
        $sound = config('sheba.push_notification_sound.manager');
        $data = [
            "title" => 'Due Tracker Reminder',
            "message" => "No message has been given",
            "sound" => $sound,
            "event_type" => 'DueTrackerReminder',
            "event_id" => (string)$this->reminder['id']
        ];
        return (new PusNotificationService())->send($topic, null, $data);

//                $topic = config('sheba.push_notification_topic_name.manager_new') . $this->reminder['partner_id'];
//        $channel = config('sheba.push_notification_channel_name.manager');
//        $sound = config('sheba.push_notification_sound.manager');
//        $data = [
//            "title" => 'Due Tracker Reminder',
//            "message" => "No message has been given",
//            "sound" => $sound,
//            "event_type" => 'DueTrackerReminder',
//            "event_id" => (string)$this->reminder['id']
//        ];
//        (new PushNotificationHandler())->send($data, $topic, $channel, $sound);
    }
}