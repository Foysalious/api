<?php namespace Sheba\Resource\Jobs\Reschedule;


use App\Models\Job;
use App\Models\Resource;
use GuzzleHttp\Client;
use Sheba\Jobs\JobTime;
use Sheba\Jobs\PreferredTime;
use Sheba\UserAgentInformation;

class Reschedule
{
    /** @var Resource */
    private $resource;
    /** @var Job */
    private $job;
    /** @var UserAgentInformation */
    private $userAgentInformation;

    private $scheduleDate;
    private $scheduleTimeSlot;
    private $rescheduleReason;


    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param mixed $scheduleDate
     * @return Reschedule
     */
    public function setScheduleDate($scheduleDate)
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    /**
     * @param mixed $scheduleTime
     * @return Reschedule
     */
    public function setScheduleTimeSlot($scheduleTime)
    {
        $this->scheduleTimeSlot = $scheduleTime;
        return $this;
    }

    /**
     * @param mixed $rescheduleReason
     */
    public function setRescheduleReason($rescheduleReason)
    {
        $this->rescheduleReason = $rescheduleReason;
        return $this;
    }

    /**
     * @return RescheduleResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function reschedule()
    {
        $job_time = new JobTime($this->scheduleDate, $this->scheduleTimeSlot);
        $response = new RescheduleResponse();
        if (!$job_time->validate()) return $response->setCode(400)->setMessage($job_time->error_message);
        $preferred_time = new PreferredTime($this->scheduleTimeSlot);
        if (!scheduler($this->resource)->isAvailableForCategory($this->scheduleDate, $preferred_time->getStartString(), $this->job->category, $this->job)) {
            return $response->setCode(403)
                ->setMessage('Resource is not available at this time. Please select different date time or change the resource');
        }
        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $this->job->id . '/reschedule',
            [
                'form_params' => [
                    'resource_id' => $this->resource->id,
                    'remember_token' => $this->resource->remember_token,
                    'schedule_date' => $this->scheduleDate,
                    'preferred_time' => $this->scheduleTimeSlot,
                    'order_reschedule_reason' => $this->rescheduleReason,
                    'created_by_type' => get_class($this->resource),
                    'portal_name' => $this->userAgentInformation->getPortalName(),
                    'user_agent' => $this->userAgentInformation->getUserAgent(),
                    'ip' => $this->userAgentInformation->getIp()
                ]
            ]);
        $response->setResponse(json_decode($res->getBody(), 1));
        return $response;
    }


}