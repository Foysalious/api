<?php namespace Sheba\JobUpdateLog;


use App\Models\Job;
use Sheba\Dal\JobUpdateLog\JobUpdateLogRepositoryInterface;

class Creator
{
    /** @var Job */
    private $job;
    private $jobUpdateLogRepository;
    private $message;

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
     * @param mixed $message
     * @return Creator
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function __construct(JobUpdateLogRepositoryInterface $jobUpdateLogRepository)
    {
        $this->jobUpdateLogRepository = $jobUpdateLogRepository;
    }

    public function create()
    {
        $this->jobUpdateLogRepository->create([
            'job_id' => $this->job->id,
            'log' => json_encode(['message' => $this->message])
        ]);
    }
}