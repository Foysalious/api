<?php namespace App\Sheba\Affiliate\PushNotification;

use Sheba\PushNotificationHandler;

class PushNotification
{
    protected $title;
    protected $message;
    protected $eventType;
    protected $link;
    private $sound;
    private $channel;
    private $topic;
    protected $affiliate_id;


    public function send()
    {
        $this->topic = config('sheba.push_notification_topic_name.affiliate') . $this->affiliate_id;
        $this->channel = config('sheba.push_notification_channel_name.affiliate');
        $this->sound = config('sheba.push_notification_sound.affiliate');

        (new PushNotificationHandler())->send([
            'title' => $this->title,
            'message' => $this->message,
            'event_type' => $this->eventType,
            'event_id' => $this->affiliate_id,
            'link' => $this->link,
            "sound" => "notification_sound",
            "channel_id" => $this->channel
        ], $this->topic, $this->channel, $this->sound);
        return true;
    }
}