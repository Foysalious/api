<?php

namespace App\Sheba\AccountingEntry\Notifications;

use Sheba\PushNotification\PushNotificationHandler as PusNotificationService;

class ReminderNotificationHandler
{
    protected array $reminder;

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
        $topic = config('sheba.push_notification_topic_name.manager_new') . $this->reminder['id'];
//        $channel = config('sheba.push_notification_channel_name.manager');
        $sound = config('sheba.push_notification_sound.manager');
        $data = [
            "title" => 'Due Tracker Reminder',
            "message" => "",
            "sound" => $sound,
            "event_type" => 'DueTrackerReminder',
            "event_id" => (string)$this->reminder['id']
        ];

        (new PusNotificationService())->send($topic, null, $data);
    }

}