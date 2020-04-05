<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\PushNotificationHandler;

class SendOnlinePaymentNotificationToResource
{
    use InteractsWithQueue, SerializesModels;
    private $resource_id;
    /** @var Job  */
    private $job;
    /** @var PushNotificationHandler  */
    private $pushNotification;

    public function __construct($resource_id, Job $job)
    {
        $this->resource_id = $resource_id;
        $this->job = $job;
        $this->pushNotification = new PushNotificationHandler();
    }

    public function handle()
    {
        if ($this->attempts() <= 1) {
            notify()->resource($this->resource_id)->send([
                'title' => 'আপনার অর্ডার ' . $this->job->partnerOrder->order->code() . ' টি Online Payment এর মাধ্যমে পরিশোধ করা হয়েছে ',
                'type' => 'info',
                'event_type' => get_class($this->job),
                'event_id' => $this->job->id
            ]);
            $topic = config('sheba.push_notification_topic_name.resource') . $this->resource_id;
            $channel = config('sheba.push_notification_channel_name.resource');
            $this->pushNotification->send([
                "title" => 'Online Payment',
                "message" => 'আপনার অর্ডার ' . $this->job->partnerOrder->order->code() . ' টি Online Payment এর মাধ্যমে পরিশোধ করা হয়েছে ',
                "event_type" => 'online_payment',
                "event_id" => $this->job->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }
    }
}