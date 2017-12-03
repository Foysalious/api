<?php

namespace App\Http\Controllers;


use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

use DB;
use Sheba\Charts\SalesGrowth;

class PartnerController extends Controller
{
    private $serviceRepository;
    private $reviewRepository;
    private $resourceJobRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->reviewRepository = new ReviewRepository();
        $this->resourceJobRepository = new ResourceJobRepository();
    }

    public function index()
    {
        $partners = Partner::select('id', 'name', 'sub_domain', 'logo')->where('status', 'Verified')->orderBy('name')->get();
        return response()->json(['partners' => $partners, 'code' => 200, 'msg' => 'successful']);
    }

    public function getPartnerServices($partner, Request $request)
    {
        $location = $request->has('location') ? $request->location : 4;
        $partner = Partner::select('id', 'name', 'sub_domain', 'description', 'logo', 'type', 'level')
            ->where('sub_domain', $partner)
            ->first();
        if ($partner == null) {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
        $review = $partner->reviews()->where('review', '<>', '')->count('review');
        $rating = round($partner->reviews()->avg('rating'), 1);
        if ($rating == 0) {
            $rating = 5;
        }
        $served_job_count = $partner->jobs()->where('status', 'Served')->count();
        $resource_count = $partner->resources()->where('resources.is_verified', 1)->count();

        array_add($partner, 'review', $review);
        array_add($partner, 'rating', $rating);
        array_add($partner, 'job_count', $served_job_count);
        array_add($partner, 'resource_count', $resource_count);

        $partner_services = $partner->services()
            ->select('services.id', 'services.banner', 'services.category_id', 'services.publication_status', 'name', 'variable_type', 'services.min_quantity')
            ->where([
                ['is_verified', 1],
                ['is_published', 1],
                ['services.publication_status', 1]
            ])->get();
        $count_of_partner_services = count($partner_services);
        array_add($partner, 'service_count', $count_of_partner_services);
        if ($count_of_partner_services > 6) {
            $partner_services = $partner_services->random(6);
        }
        $final_service = [];
        foreach ($partner_services as $service) {
            $service = $this->serviceRepository->getStartPrice($service, $location);
            array_add($service, 'slug_service', str_slug($service->name, '-'));
            //review count of partner of this service
            $review = $service->reviews()->where([
                ['review', '<>', ''],
                ['partner_id', $partner->id]
            ])->count('review');
            //avg rating of the partner for this service
            $rating = $service->reviews()->where('partner_id', $partner->id)->avg('rating');
            array_add($service, 'review', $review);
            if ($rating == null) {
                array_add($service, 'rating', 5);
            } else {
                array_add($service, 'rating', round($rating, 1));
            }
            array_forget($service, 'pivot');
            array_push($final_service, $service);
        }
        if (count($partner) > 0) {
            return response()->json([
                'partner' => $partner,
                'services' => $final_service,
                'msg' => 'successful',
                'code' => 200
            ]);
        }
    }

    public function getReviews($partner)
    {
        $partner = Partner::with(['reviews' => function ($q) {
            $q->select('id', 'service_id', 'partner_id', 'customer_id', 'review_title', 'review', 'rating', DB::raw('DATE_FORMAT(updated_at, "%M %d,%Y at %h:%i:%s %p") as time'))
                ->with(['service' => function ($q) {
                    $q->select('id', 'name');
                }])->with(['customer' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name');
                    }]);
                }])->orderBy('updated_at', 'desc');
        }])->select('id')->where('id', $partner)->first();
        if (count($partner->reviews) > 0) {
            $partner = $this->reviewRepository->getGeneralReviewInformation($partner);
            $breakdown = $this->reviewRepository->getReviewBreakdown($partner->reviews);
            return response()->json(['msg' => 'ok', 'code' => 200, 'partner' => $partner, 'breakdown' => $breakdown]);
        }
        return response()->json(['msg' => 'not found', 'code' => 404]);
    }


    public function getResources($partner, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $partnerRepo = new PartnerRepository($request->partner);
            $resources = $partnerRepo->resources();
            if (count($resources) > 0) {
                return api_response($request, $resources, 200, ['resources' => array_slice($resources->sortBy('name')->values()->all(), $offset, $limit)]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getDashboardInfo($partner, Request $request)
    {
        try {
            $partner = $request->partner;
            $statuses = array(
                constants('JOB_STATUSES')['Accepted'],
                constants('JOB_STATUSES')['Schedule_Due'],
                constants('JOB_STATUSES')['Process'],
                constants('JOB_STATUSES')['Served'],
            );
            $partner->load(['resources' => function ($q) {
                $q->verified()->type('Handyman');
            }, 'jobs' => function ($q) use ($statuses) {
                $q->info()->status($statuses)->with('resource');
            }]);
            $jobs = $partner->jobs;
            $resource_ids = $partner->resources->pluck('id')->unique();
            $assigned_resource_ids = $jobs->whereIn('status', [constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due']])->pluck('resource_id')->unique();
            $unassigned_resource_ids = $resource_ids->diff($assigned_resource_ids);
            $weekly = (new SalesGrowth($partner))->getWeekData();
            $breakdown = (new SalesGrowth($partner))->get();
            $info = array(
                'schedule_due' => $jobs->where('status', constants('JOB_STATUSES')['Schedule_Due'])->count(),
                'todays_jobs' => $jobs->where('schedule_date', Carbon::now()->toDateString())->count(),
                'tomorrows_jobs' => $jobs->where('schedule_date', Carbon::tomorrow()->toDateString())->count(),
                'accepted_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Accepted'])->count(),
                'process_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Process'])->count(),
                'served_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Served'])->count(),
                'total_resources' => $resource_ids->count(),
                'assigned_resources' => $assigned_resource_ids->count(),
                'unassigned_resources' => $unassigned_resource_ids->count(),
                'balance' => (double)$partner->wallet,
                'today' => $weekly[(int)date('d')],
                'week' => $weekly->sum(),
                'month' => $breakdown->sum()
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getEarnings($partner, Request $request)
    {

//        try {
        $breakdown = collect(array_fill(1, Carbon::create($this->year, $this->month, null)->daysInMonth, 0));
            $partner = $request->partner;
            $partner_orders = PartnerOrder::with('order.location', 'jobs.usedMaterials')
                ->where('partner_id', $partner->id)
                ->whereBetween('closed_at', [Carbon::parse('2017-11-2'), Carbon::parse('2017-11-8')])
                ->get()
                ->each(function ($partner_order) {
                    $partner_order['sales'] = (double)$partner_order->calculate($price_only = true)->totalCost;
                    $partner_order['code'] = $partner_order->code();
                   $partner_order['week_name']=$partner_order->closed_at->format('D');
                    $partner_order['day'] = $partner_order->closed_at->day;
                    removeRelationsFromModel($partner_order);
                    removeSelectedFieldsFromModel($partner_order);
                });
            dd($partner_orders->groupBy('day'));
//        } catch (\Throwable $e) {
//            return api_response($request, null, 500);
//        }
    }


}
