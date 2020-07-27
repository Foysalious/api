<?php namespace Sheba\Repositories;

use App\Models\Job;
use Sheba\Dal\JobCancelRequest\JobCancelRequest;

class CancelRequestRepository extends BaseRepository
{
    public function create(array $data)
    {
        JobCancelRequest::create($data);
    }

    public function isDuplicatedRequest(Job $job)
    {
        $job_cancel_request = JobCancelRequest::where('job_id', $job->id)->whereIn('status', ['Pending', 'Approved']);
        return $job_cancel_request->count() > 0;
    }
}
