<?php namespace Sheba\JobUpdateLog;


use App\Models\Job;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\JobUpdateLog\JobUpdateLogRepositoryInterface;
use Sheba\UserAgentInformation;

class Creator
{
    /** @var Job */
    private $job;
    private $jobUpdateLogRepository;
    private $message;
    /** @var UserAgentInformation */
    private $userAgentInformation;
    /** @var Model */
    private $createdBy;

    public function __construct(JobUpdateLogRepositoryInterface $jobUpdateLogRepository)
    {
        $this->jobUpdateLogRepository = $jobUpdateLogRepository;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param Job $job
     * @return Creator
     */
    public function setJob($job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @param UserAgentInformation $userAgentInformation
     * @return Creator
     */
    public function setUserAgentInformation($userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    /**
     * @param Model $createdBy
     * @return Creator
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function create()
    {
        $this->jobUpdateLogRepository->create([
            'job_id' => $this->job->id,
            'log' => json_encode(['message' => $this->message]),
            'portal_name' => $this->userAgentInformation->getPortalName(),
            'user_agent' => $this->userAgentInformation->getUserAgent(),
            'ip' => $this->userAgentInformation->getIp(),
            'created_by_type' => get_class($this->createdBy)
        ]);
    }
}