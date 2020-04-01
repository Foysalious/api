<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Jobs\JobStatuses;
use Sheba\PartnerOrder\PartnerOrderStatuses;
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
        return api_response($request, $job_list, 200, ['today' => $upto_todays_jobs, 'tomorrow' => $tomorrows_jobs, 'rest' => $rest_jobs]);
    }

    public function orderDetails($job, Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $job_info = [
            'id' => 192408,
            "code" => "D-160620-1751-00208408",
            'customer_id' => 102,
            'customer_name' => 'Mehedi Hasan',
            'pro_pic' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/images/profiles/avatar/default.jpg',
            'delivery_name' => 'Mehedi Hasan',
            'delivery_address' => 'Road#10, Avenue#9, House#1222&1223 Mirpur DOHS, Dhaka.',
            'delivery_mobile' => '+8801718741996',
            'geo_informations' => [

                "lat" => 23.7367689,
                "lng" => 90.3871961

            ],
            "status" => JobStatuses::PROCESS,
            'preferred_time' => '2:00 PM-3:00 PM',
            "preferred_time_start" => "2:00 PM",
            "schedule_date" => "2019-06-16",
            'services' => [
                [
                    'name' => 'Daily Budget Meal',
                    'variables' => [
                        [
                            "title" => "chicken",
                            "question" => "chicken",
                            "answer" => "chicken"
                        ],
                        [
                            "title" => "hg",
                            "question" => "g",
                            "answer" => "g9"
                        ]
                    ],
                    'unit' => 'person',
                    'quantity' => 1
                ]
            ]
        ];
        return api_response($request, $job_info, 200, ['order_details' => $job_info]);
    }

}