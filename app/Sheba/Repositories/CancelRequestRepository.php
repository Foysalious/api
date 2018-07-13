<?php namespace Sheba\Repositories;

use App\Models\Job;
use App\Models\JobCancelRequest;

use App\Sheba\UserRequestInformation;

class CancelRequestRepository extends BaseRepository
{
    public function create($data)
    {
        $data['created_by_type'] = 'App\Models\Resource';
        $data = array_merge((new UserRequestInformation(\request()))->getInformationArray(), $data);
        JobCancelRequest::create($data);
    }

    public function isDuplicatedRequest(Job $job)
    {
        $job_cancel_request = JobCancelRequest::where('job_id', $job->id)->whereIn('status', ['Pending', 'Approved']);
        return $job_cancel_request->count() > 0;
    }
}