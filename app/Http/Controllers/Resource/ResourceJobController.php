<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Repositories\ResourceJobRepository;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Dal\Job\JobRepositoryInterface;

class ResourceJobController extends Controller
{
    public function index(Request $request, JobRepositoryInterface $job_repository, ResourceJobRepository $resource_job_repository)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $jobs = $job_repository->getOngoingJobsListForResourceApp($resource->id)->get();
        $jobs = collect($resource_job_repository->rearrange($jobs));
        return api_response($request, $jobs, 200, ['jobs' => $jobs]);
    }

}