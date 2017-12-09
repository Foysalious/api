<?php

namespace App\Http\Controllers;


use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Repositories\NotificationRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;

use DB;
use Sheba\Charts\SalesGrowth;
use Validator;

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
            $reviews = $partner->reviews->filter(function ($review, $key) {
                return $review->review != '' || $review->review != null;
            })->values()->all();
            array_forget($partner, 'reviews');
            $partner['reviews'] = $reviews;
            return response()->json(['msg' => 'ok', 'code' => 200, 'partner' => $partner, 'breakdown' => $breakdown]);
        }
        return response()->json(['msg' => 'not found', 'code' => 404]);
    }

    public function getReviewInfo($partner, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_id' => 'sometimes|required|numeric',
                'resource_id' => 'sometimes|required|numeric'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            list($offset, $limit) = calculatePagination($request);
            $partner = $request->partner->load(['reviews' => function ($q) use ($request) {
                $q->with(['job.partner_order.partner', 'resource.profile', 'service']);
                if ($request->has('service_id')) {
                    $q->where('service_id', $request->service_id);
                }
                if ($request->has('resource_id')) {
                    $q->where('resource_id', $request->resource_id);
                }
            }]);
            $reviews = $partner->reviews;
            $breakdown = array_fill(1, 5, 0);
            $avg_rating = null;
            if (count($reviews) > 0) {
                $breakdown = $this->reviewRepository->getReviewBreakdown($reviews);
                $partner = $this->reviewRepository->getGeneralReviewInformation($partner);
                $avg_rating = $reviews->avg('rating');
                $reviews = $reviews->filter(function ($item, $key) {
                    return $item->review != '' || $item->review != null;
                })->each(function ($review, $key) {
                    $review['order_id'] = $review->job->partner_order->id;
                    $review['order_code'] = $review->job->partner_order->code();
                    $review['partner'] = $review->job->partner_order->partner->name;
                    $review['resource_name'] = ($review->resource) ? $review->resource->profile->name : null;
                    $review['resource_pic'] = ($review->resource) ? $review->resource->profile->pro_pic : null;
                    $review['service_name'] = $review->service->name;
                    removeRelationsFromModel($review);
                    removeSelectedFieldsFromModel($review);
                })->sortByDesc('created_at');
                removeRelationsFromModel($partner);
                removeSelectedFieldsFromModel($partner);
            }
            $info = array(
                'rating' => $avg_rating ? round($avg_rating, 2) : 5,
                'total_reviews' => $reviews->count(),
                'reviews' => array_slice($reviews->values()->all(), $offset, $limit),
                'breakdown' => $breakdown
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getResources($partner, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|string',
                'verified' => 'sometimes|required|boolean'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            list($offset, $limit) = calculatePagination($request);
            $partnerRepo = new PartnerRepository($request->partner);
            $type = $request->has('type') ? $request->type : null;
            $verified = $request->has('verified') ? $request->verified : null;
            $resources = $partnerRepo->resources($type, $verified);
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
        try {
            Carbon::setWeekStartsAt(Carbon::SATURDAY);
            Carbon::setWeekEndsAt(Carbon::FRIDAY);
            $start_time = Carbon::now()->startOfWeek();
            $end_time = Carbon::now()->endOfWeek();
            $partner = $request->partner;
            $partner_orders = PartnerOrder::with('order.location', 'jobs.usedMaterials')
                ->where('partner_id', $partner->id)
                ->whereBetween('closed_at', [$start_time, $end_time])
                ->select('id', 'partner_id', 'order_id', 'closed_at', 'sheba_collection', 'partner_collection', 'finance_collection')
                ->get()->each(function ($partner_order) {
                    $partner_order['sales'] = (double)$partner_order->calculate($price_only = true)->totalCost;
                    $partner_order['code'] = $partner_order->code();
                    $partner_order['week_name'] = $partner_order->closed_at->format('D');
                    $partner_order['day'] = $partner_order->closed_at->day;
                    $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
                    $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
                    $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
                    removeRelationsFromModel($partner_order);
                    removeSelectedFieldsFromModel($partner_order);
                });
            $breakdown = collect(array_fill((int)$start_time->format('d'), 7, 0));
            if (count($partner_orders) > 0) {
                $partner_orders->groupBy('day')->each(function ($item, $key) use ($breakdown) {
                    $breakdown[$key] = $item->sum('sales');
                });
            }
            $weekly_breakdown = collect();
            $breakdown->filter(function ($item, $key) use ($start_time, $end_time) {
                return ($start_time->day <= $key && $key <= $end_time->day);
            })->each(function ($item, $key) use ($weekly_breakdown) {
                $weekly_breakdown->put(Carbon::createFromDate(null, null, $key)->format('D'), $item);
            });
            $yearly = (new SalesGrowth($partner, 0, (int)date('Y')))->get();
            $yearly = array_has($yearly, 'month') ? collect($yearly['month']) : collect();
            $info = array(
                'today' => $breakdown[(int)date('d')],
                'week' => $breakdown->sum(),
                'month' => isset($yearly[(int)date('m')]) ? $yearly[(int)date('m')] : 0,
                'year' => $yearly->sum(),
                'total' => 1000
            );
            return api_response($request, $info, 200, ['info' => $info, 'breakdown' => $weekly_breakdown, 'orders' => $partner_orders]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function show($partner, Request $request)
    {
        try {
            $partner = $request->partner->load(['basicInformations', 'reviews', 'services' => function ($q) {
                $q->where('partner_service.is_verified', 1);
            }], 'locations');
            $locations = $partner->locations;
            $basic_info = $partner->basicInformations;
            $info = collect($partner)->only(['id', 'name', 'mobile', 'email', 'verified_at', 'status', 'logo', 'wallet', 'address', 'created_at']);
            $info->put('total_rating', $partner->reviews->count());
            $info->put('avg_rating', round($partner->reviews->avg('rating'), 2));
            $info->put('working_days', collect($basic_info)->only('working_days')->get('working_days'));
            $info->put('working_hours', collect($basic_info)->only('working_hours')->get('working_hours'));
            $info->put('locations', $locations->pluck('name'));
            $info->put('total_locations', $locations->count());
            $info->put('total_services', $partner->services->count());
            $info->put('wallet', (double)$info->get('wallet'));
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getNotifications($partner, Request $request)
    {
        $notifications = (new NotificationRepository())->getNotifications($request->partner);
        if (count($notifications) > 0) {
            return api_response($request, $notifications, 200, ['notifications' => $notifications->values()->all()]);
        } else {
            return api_response($request, null, 404);
        }
    }

}
