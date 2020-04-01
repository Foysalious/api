<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\App\Jobs\BillInfo;
use Sheba\Resource\App\Jobs\JobInfo;
use Sheba\Resource\App\Jobs\JobList;

class ResourceJobController extends Controller
{
    public function index(Request $request, JobList $job_list)
    {
        $this->validate($request, ['offset' => 'numeric|min:0', 'limit' => 'numeric|min:1']);
        list($offset, $limit) = calculatePagination($request);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $jobs = $job_list->setResource($resource)->getOngoingJobs();
        if (count($jobs) == 0) return api_response($request, $jobs, 404);
        return api_response($request, $jobs, 200, ['orders' => $jobs->splice($offset, $limit)]);
    }

    public function getAllJobs(Request $request, JobList $job_list)
    {
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $upto_todays_jobs = $job_list->setResource($resource)->getOngoingJobs();
        $tomorrows_jobs = $job_list->setResource($resource)->getTomorrowsJobs();
        $rest_jobs = $job_list->setResource($resource)->getRestJobs();
        return api_response($request, $job_list, 200, ['jobs' => [['title' => 'আজকে', 'jobs' => $upto_todays_jobs], ['title' => 'আগামীকালকে', 'jobs' => $tomorrows_jobs], ['title' => 'পরবর্তী', 'jobs' => $rest_jobs]]]);
    }

    public function jobDetails(Job $job, Request $request, JobInfo $jobInfo)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job."]);
        $job = $jobInfo->setResource($resource)->getJobDetails($job);
        return api_response($request, $job, 200, ['job_details' => $job]);
    }

    public function getBills(Job $job, Request $request, BillInfo $billInfo)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($resource->id !== $job->resource_id) return api_response($request, $job, 403, ["message" => "You're not authorized to access this job's bill."]);
        $bill = $billInfo->getBill($job);
        return api_response($request, $bill, 200, ['bill' => $bill]);
    }

}