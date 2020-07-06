<?php namespace App\Http\Controllers\Resource;

use App\Models\Category;
use App\Models\Job;
use App\Models\Partner;
use App\Models\Resource;
use App\Transformers\CustomSerializer;
use App\Transformers\Resource\ResourceHomeTransformer;
use App\Transformers\Resource\ResourceProfileTransformer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Jobs\JobList;
use Sheba\Resource\Schedule\ResourceScheduleSlot;

class ResourceController extends Controller
{
    public function getProfile(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $data = new Item($resource, new ResourceProfileTransformer());
        $profile = $fractal->createData($data)->toArray()['data'];
        return api_response($request, $profile, 200, ['profile' => $profile]);
    }

    public function getSchedules(Job $job, Request $request, ResourceScheduleSlot $slot)
    {
        $resource = $request->auth_user->getResource();
        $category = Category::find($job->category->id);
        $slot->setCategory($category);
        $slot->setPartner(Partner::find($job->partner_order->partner_id));
        $slot->setLimit(7);
        $dates = $slot->getSchedulesByResource($resource);
        return api_response($request, $dates, 200, ['dates' => $dates]);

    }

    public function getHome(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $data = new Item($resource, new ResourceHomeTransformer());
        $info = $fractal->createData($data)->toArray()['data'];
        return api_response($request, $info, 200, ['home' => $info]);
    }

    public function dashboard(Request $request, JobList $jobList)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $jobs_summary = $jobList->setResource($resource)->getNumberOfJobs();
        $jobs_summary = [
            [
                'title' => 'শিডিউল ডিউ অর্ডার',
                'jobs_count' => $jobs_summary['schedule_due_jobs'],
                'value' => 'schedule_due_jobs'
            ],
            [
                'title' => 'আজকের অর্ডার',
                'jobs_count' => $jobs_summary['todays_jobs'],
                'value' => 'todays_jobs'
            ],
            [
                'title' => 'আগামীকালের অর্ডার',
                'jobs_count' => $jobs_summary['tomorrows_jobs'],
                'value' => 'tomorrows_jobs'
            ],
            [
                'title' => 'পরবর্তী অর্ডার',
                'jobs_count' => $jobs_summary['rest_jobs'],
                'value' => 'rest_jobs'
            ]
        ];
        return api_response($request, $jobs_summary, 200, ['jobs_summary' => $jobs_summary]);
    }

    public function help(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $fractal = new Manager();
        $content = [
            [
                'title' => 'sPro - রিসোর্সদের জন্য নতুন অ্যাপ',
                'link' => 'https://youtu.be/e2-pE8ioU4M',
                'thumbnail' => 'https://img.youtube.com/vi/e2-pE8ioU4M/0.jpg'
            ],
            [
                'title' => 'sPro- চলমান অর্ডারে সার্ভিস ম্যাটেরিয়াল ও পরিমাণ পরিবর্তনের পদ্ধতি',
                'link' => 'https://youtu.be/HbKBjkPhZqI',
                'thumbnail' => 'https://img.youtube.com/vi/HbKBjkPhZqI/0.jpg'
            ],
            [
                'title' => 'sPro- রিওয়ার্ড এন্ড সেবা ব্যালান্স',
                'link' => 'https://www.youtube.com/watch?v=ngGxaJKJCjU',
                'thumbnail' => 'https://img.youtube.com/vi/ngGxaJKJCjU/0.jpg'
            ]
        ];
        return api_response($request, $content, 200, ['help' => $content]);
    }
}
