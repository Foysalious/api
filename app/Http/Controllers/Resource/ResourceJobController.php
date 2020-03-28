<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\App\Jobs\JobList;

class ResourceJobController extends Controller
{
    public function index(Request $request, JobList $job_list)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $jobs = $job_list->setResource($resource)->getOngoingJobs();
        if (count($jobs) > 0) return api_response($request, $jobs, 200, ['orders' => $jobs]);
        return api_response($request, $jobs, 404);
    }

}