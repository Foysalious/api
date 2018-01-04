<?php

namespace App\Repositories;


use App\Models\Job;
use App\Models\JobCancelLog;

class JobCancelLogRepository
{
    private $job;
    private $created_by;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function store($previous_status, $reason)
    {
        $job_cancel = new JobCancelLog();
        $job_cancel->job_id = $this->job->id;
        $job_cancel->from_status = $previous_status;
        $job_cancel->cancel_reason_details = $reason;
        $job_cancel->created_by_name = class_basename($this->created_by);
        $job_cancel->cancel_reason = class_basename($this->created_by) . ' Dependency';
        $job_cancel->log = 'Job has been cancelled by ' . class_basename($this->created_by);
        $job_cancel->save();
    }

}