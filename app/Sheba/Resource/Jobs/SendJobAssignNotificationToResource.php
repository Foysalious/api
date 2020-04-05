<?php namespace Sheba\Resource\Jobs;


use App\Models\Job;
use App\Models\Resource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sheba\PushNotificationHandler;

class SendJobAssignNotificationToResource extends \App\Jobs\Job implements ShouldQueue
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
                'title' => 'আপনাকে একটি অর্ডার ' . $this->job->partnerOrder->order->code() . ' এ এসাইন করা হয়েছে',
                'type' => 'warning',
                'event_type' => get_class($this->job),
                'event_id' => $this->job->id
            ]);
            $topic = config('sheba.push_notification_topic_name.resource') . $this->resource_id;
            $channel = config('sheba.push_notification_channel_name.resource');
            $this->pushNotification->send([
                "title" => 'কাজ আসাইন',
                "message" => 'আপনাকে একটি অর্ডার ' . $this->job->partnerOrder->order->code() . ' এ এসাইন করা হয়েছে',
                "event_type" => 'job_assign',
                "event_id" => $this->job->id,
                "sound" => "notification_sound",
                "channel_id" => $channel,
                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
            ], $topic, $channel);
        }
    }
}