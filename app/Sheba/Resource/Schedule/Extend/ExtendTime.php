<?php namespace Sheba\Resource\Schedule\Extend;


use App\Models\Job;
use App\Models\ResourceSchedule;
use Sheba\PushNotificationHandler;

class ExtendTime
{
    /** @var Job */
    private $job;
    private $extendedTimeInMinutes;

    /**
     * @param Job $job
     * @return ExtendTime
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    public function setExtendedTimeInMinutes($time_in_minutes)
    {
        $this->extendedTimeInMinutes = $time_in_minutes;
        return $this;
    }

    /**
     * @return \Sheba\Resource\Jobs\Response
     */
    public function extend()
    {
        $response = new ExtendTimeResponse();
        $resource_schedule = ResourceSchedule::where([['job_id', $this->job->id], ['resource_id', $this->job->resource_id]])->first();
        if (!$resource_schedule) return $response->setMessage('Bad request')->setCode(400);
        scheduler($this->job->resource)->extend($resource_schedule, $this->extendedTimeInMinutes);
        $this->sendNotificationToPartner();
        return $response->setMessage('Successful')->setCode(200);
    }

    private function sendNotificationToPartner()
    {
        $topic = config('sheba.push_notification_topic_name.manager') . $this->job->partner_order->partner_id;
        $channel = config('sheba.push_notification_channel_name.manager');

        (new PushNotificationHandler())->send([
            "title" => 'Resource extended time',
            "message" => $this->job->resource->profile->name . " has extended time for " . $this->job->fullCode(),
            "event_type" => 'PartnerOrder',
            "event_id" => (string)$this->job->partner_order->id,
            "action" => 'extend_time',
            "version" => $this->job->partner_order->getVersion(),
            "sound" => "notification_sound",
            "channel_id" => $channel
        ], $topic, $channel);
    }


}