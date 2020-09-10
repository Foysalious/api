<?php namespace App\Http\Controllers\Resource;

use App\Models\PartnerResource;
use Sheba\Dal\Category\Category;
use App\Models\Job;
use App\Models\Partner;
use App\Models\Resource;
use Sheba\Location\Geo;
use App\Transformers\CustomSerializer;
use App\Transformers\Resource\ResourceHomeTransformer;
use App\Transformers\Resource\ResourceProfileTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Jobs\JobList;
use Sheba\Resource\Review\RatingInfo;
use Sheba\Resource\Review\ReviewList;
use Sheba\Resource\Schedule\ResourceScheduleChecker;
use Sheba\Resource\Schedule\ResourceScheduleSlot;
use Sheba\Resource\Service\ServiceList;

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

    public function getService(Request $request, ServiceList $serviceList)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $geo = new Geo((double)$request->lat, (double)$request->lng);

        $services = $serviceList->setResource($resource)->setGeo($geo)->getAllServices();

        return $services
            ? api_response($request, $services, 200, ['services' => $services])
            : api_response($request, null, 404);
    }

    public function checkSchedule(Request $request, ResourceScheduleSlot $slot, ResourceScheduleChecker $resourceScheduleChecker)
    {
        $this->validate($request, [
            'category' => 'required|numeric',
            'partner' => 'required|numeric',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|string',
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $partner_resources = PartnerResource::whereHas('categories', function($q) use ($request) {
            $q->where('category_id', $request->category);
        })->handyman()->select('id')->where('resource_id', $resource->id)->get();
        if ($partner_resources->count() == 0) return api_response($request, null, 404, ["message" => "Category resource not found."]);
        $category = Category::find($request->category);
        $partner= Partner::find($request->partner);
        $date = Carbon::createFromFormat('Y-m-d', $request->date);
        $limit = $date->diffInDays(Carbon::now()) + 1;
        $slot->setCategory($category)->setPartner($partner)->setLimit($limit);
        $dates = $slot->getSchedulesByResource($resource);
        $schedule = $resourceScheduleChecker->setSchedules($dates)->setDate($request->date)->setTime($request->time)->checkScheduleAvailability();
        if (empty($schedule)) return api_response($request, $schedule, 404, ["message" => 'Schedule not found.']);
        return api_response($request, $schedule, 200, ['schedule' => $schedule]);
    }

    public function getRatingInfo(Request $request, RatingInfo $ratingInfo)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        $rating = $ratingInfo->setResource($resource)->getRatingInfo();


        return $rating ? api_response($request, $rating, 200, ['info' => $rating]) : api_response($request, null, 404);
    }
}
