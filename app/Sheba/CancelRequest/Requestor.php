<?php namespace Sheba\CancelRequest;

use App\Models\Job;

use Sheba\Repositories\CancelRequestRepository;
use Sheba\Repositories\JobRepository;

abstract class Requestor
{
    protected $job;
    private $reason;
    private $cancelRequests;
    private $jobRepo;
    private $isEscalated;

    public function __construct(CancelRequestRepository $cancel_requests, JobRepository $job_repo)
    {
        $this->cancelRequests = $cancel_requests;
        $this->jobRepo = $job_repo;
    }

    public function hasError()
    {
        if ($this->job->isClosed()) return ['code' => 422, 'msg' => 'You are not authorized to send cancel request to this stage.'];
        if ($this->cancelRequests->isDuplicatedRequest($this->job)) return ['code' => 422, 'msg' => 'Already send a cancelled request'];

        return false;
    }

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    public function setEscalatedStatus($escalated_status)
    {
        $this->isEscalated = $escalated_status;
        return $this;
    }

    abstract function request();

    abstract protected function notify();

    protected function saveToDB()
    {
        $data = [
            'job_id'        => $this->job->id,
            'cancel_reason' => $this->reason,
            'from_status'   => $this->job->status,
            'is_escalated'  => $this->isEscalated
        ];
        $this->cancelRequests->create($data);
    }

    protected function freeResource()
    {
        if (!empty($this->job->resource)) {
            scheduler($this->job->resource)->release($this->job);
            $this->jobRepo->update($this->job, ['resource_id' => null]);
        }
    }
}