<?php

namespace App\Sheba\AccountingEntry\Notifications;

use App\Models\Partner;
use LaravelFCM\Message\Exceptions\InvalidOptionsException;
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

    /**
     * @throws InvalidOptionsException
     */
    public function handler()
    {
        $topic            = config('sheba.push_notification_topic_name.manager') . $this->reminder['partner_id'];
        $channel          = config('sheba.push_notification_channel_name.manager');
        $sound            = config('sheba.push_notification_sound.manager');
        $event_type       = 'DueTracker';

        $title = "Due Tracker Reminder";
        $message = "No message has been given";
        (new PushNotificationHandler())->send([
            "title"      => $title,
            "message"    => $message,
            "event_type" => $event_type,
            "sound"      => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel, $sound);
        $partner = Partner::find($this->reminder['partner_id']);
        notify()->partner($partner)->send([
            "title"       => $title,
            "description" => $message,
            "type" => "Info",
            "event_type" => "due_tracker"
        ]);
//        $topic = config('sheba.push_notification_topic_name.manager_new') . $this->reminder['partner_id'];
//        $sound = config('sheba.push_notification_sound.manager');
//        $data = [
//            "title" => 'Due Tracker Reminder',
//            "message" => "No message has been given",
//            "sound" => $sound,
//            "event_type" => 'DueTrackerReminder',
//            "event_id" => (string)$this->reminder['id']
//        ];
//        return (new PusNotificationService())->send($topic, null, $data);

//        $topic = config('sheba.push_notification_topic_name.manager_new') . $this->reminder['partner_id'];
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