<?php


namespace Sheba\Customer\Jobs\Reschedule;


use App\Models\Job;
use GuzzleHttp\Client;
use Sheba\Jobs\JobTime;
use Sheba\Jobs\PreferredTime;
use Sheba\Customer\Jobs\Reschedule\RescheduleResponse;
use Sheba\UserAgentInformation;

class Reschedule
{
    /** @var Job */
    private $job;
    /** @var UserAgentInformation */
    private $userAgentInformation;

    private $scheduleDate;
    private $scheduleTimeSlot;

    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param Job $job
     * @return Reschedule
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
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

    public function reschedule()
    {
        $job_time = new JobTime($this->scheduleDate, $this->scheduleTimeSlot);
        $response = new RescheduleResponse();
        if (!$job_time->validate()) return $response->setCode(400)->setMessage($job_time->error_message);

        $client = new Client();
        $res = $client->request('POST', config('sheba.admin_url') . '/api/job/' . $this->job->id . '/reschedule',
            [
                'form_params' => [
                    'schedule_date' => $this->scheduleDate,
                    'preferred_time' => $this->scheduleTimeSlot,
                    'portal_name' => $this->userAgentInformation->getPortalName(),
                    'user_agent' => $this->userAgentInformation->getUserAgent(),
                    'ip' => $this->userAgentInformation->getIp()
                ]
            ]);
        $response->setResponse(json_decode($res->getBody(), 1));
        return $response;
    }

}