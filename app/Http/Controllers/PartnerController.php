<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Models\PartnerTransaction;
use App\Models\RateAnswer;
use App\Models\Service;
use App\Repositories\DiscountRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ResourceJobRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\Checkout\PartnerPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Illuminate\Validation\ValidationException;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Validator;

class PartnerController extends Controller
{
    private $serviceRepository;
    private $partnerServiceRepository;
    private $reviewRepository;
    private $resourceJobRepository;
    private $partnerOrderRepository;
    private $discountRepository;

    public function __construct()
    {
        $this->serviceRepository = new ServiceRepository();
        $this->reviewRepository = new ReviewRepository();
        $this->resourceJobRepository = new ResourceJobRepository();
        $this->partnerOrderRepository = new PartnerOrderRepository();
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->discountRepository = new DiscountRepository();
    }

    public function index()
    {
        $partners = Partner::select('id', 'name', 'sub_domain', 'logo')->whereHas('services', function ($q) {
            $q->published();
        })->has('resources', '>', 0)->where('status', 'Verified')->orderBy('name')->get();
        return response()->json(['partners' => $partners, 'code' => 200, 'msg' => 'successful']);
    }

    public function show($partner, Request $request)
    {
        try {
            $partner_request = $partner;
            $partner = Partner::where([['id', (int)$partner_request], ['status', 'Verified']])->first();
            if ($partner == null) {
                $partner = Partner::where([['sub_domain', $partner_request], ['status', 'Verified']])->first();
            }
            if ($partner == null)
                return api_response($request, null, 404);
            $partner->load(['workingHours', 'categories' => function ($q) {
                $q->select('categories.id', 'name', 'thumb', 'icon', 'categories.slug')->where('category_partner.is_verified', 1);
            }, 'reviews', 'jobs' => function ($q) {
                $q->whereHas('resource', function ($query) {
                    $query->verified();
                })->with(['resource' => function ($q) {
                    $q->select('resources.id', 'profile_id')->with('profile');
                }, 'review' => function ($q) {
                    $q->select('id', 'job_id', 'resource_id', 'customer_id', 'rating', 'review')->with('customer.profile');
                }]);
            }, 'services' => function ($q) {
                $q->where('partner_service.is_verified', 1);
            }, 'locations']);
            $locations = $partner->locations;
            $info = collect($partner)->only(['id', 'name', 'mobile', 'description', 'email', 'verified_at', 'status', 'logo', 'address', 'created_at']);
            $working_info = [];
            foreach ($partner->workingHours as $workingHour) {
                array_push($working_info, array(
                    'day' => $workingHour->day,
                    'hour' => (Carbon::parse($workingHour->start_time))->format('g:i A') . '-' . (Carbon::parse($workingHour->end_time))->format('g:i A')
                ));
            }
            $info->put('working_days', $working_info);
            $info->put('is_available', in_array(Carbon::today()->format('D') . 'day', collect($working_info)->pluck('day')->toArray()) ? 1 : 0);
            $info->put('total_locations', $locations->count());
            $info->put('total_services', $partner->services->count());
            $job_with_review = $partner->jobs->where('status', 'Served')->filter(function ($job) {
                return $job->resource_id != null && $job->review != null;
            });
            $resource_jobs = $job_with_review->groupBy('resource_id');
            $all_resources = collect();
            foreach ($resource_jobs as $resource_job) {
                $all_resources->push(collect([
                    'name' => $resource_job[0]->resource->profile->name,
                    'mobile' => $resource_job[0]->resource->profile->mobile,
                    'picture' => $resource_job[0]->resource->profile->pro_pic,
                    'total_rating' => $resource_job->count(),
                    'avg_rating' => round($resource_job->avg('review.rating'), 2),
                ]));
            }
            $all_resources = $all_resources->take(4);
            $info->put('resources', $all_resources->values()->all());
            $reviews = [];
            $job_with_review->filter(function ($job) {
                return $job->review->rating >= 4 && ($job->review->review != null || $job->review->review != '');
            })->each(function ($job) use (&$reviews) {
                $final = $job->review;
                $final['customer_name'] = $job->review->customer->profile->name;
                $final['customer_pic'] = $job->review->customer->profile->pro_pic;
                removeRelationsAndFields($final);
                array_push($reviews, $final);
            });
            $info->put('reviews', $reviews);
            $info->put('categories', $partner->categories->each(function ($category) {
                removeRelationsAndFields($category);
            }));
            $info->put('compliments', []);
//            $compliments = RateAnswer::select('id', 'badge', 'answer')->inRandomOrder()->take(5)->get();
//            $info->put('compliments', $compliments->each(function (&$compliment) {
//                array_add($compliment, 'count', rand(5, 10));
//            }));
            $info->put('total_resources', $partner->resources->count());
            $info->put('total_jobs', $partner->jobs->count());
            $info->put('total_rating', $partner->reviews->count());
            $info->put('avg_rating', $this->reviewRepository->getAvgRating($partner->reviews));
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
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
                $avg_rating = $this->reviewRepository->getAvgRating($reviews);
                $reviews = $reviews->filter(function ($item, $key) {
                    return $item->review != '' || $item->review != null;
                })->each(function ($review, $key) {
                    $review['order_id'] = $review->job->partner_order->id;
                    $review['order_code'] = $review->job->partner_order->code();
                    $review['partner'] = $review->job->partner_order->partner->name;
                    $review['resource_name'] = ($review->resource) ? $review->resource->profile->name : null;
                    $review['resource_pic'] = ($review->resource) ? $review->resource->profile->pro_pic : null;
                    $review['service_name'] = $review->service->name;
                    removeRelationsAndFields($review);
                })->sortByDesc('created_at');
                removeRelationsAndFields($partner);
            }
            $info = array(
                'rating' => $avg_rating,
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
        //try {
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|string',
                'verified' => 'sometimes|required|boolean',
                'job_id' => 'sometimes|required|numeric',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            list($offset, $limit) = calculatePagination($request);
            $partnerRepo = new PartnerRepository($request->partner);
            $type = $request->has('type') ? $request->type : null;
            $verified = $request->has('verified') ? $request->verified : null;
            $resources = $partnerRepo->resources($type, $verified, $request->job_id);
            $resources = $resources->filter(function ($resource) {
                return $resource['is_available'] == 1;
            });
            if (count($resources) > 0) {
                return api_response($request, $resources, 200, ['resources' => array_slice($resources->sortBy('name')->values()->all(), $offset, $limit)]);
            } else {
                return api_response($request, null, 404);
            }
        //} catch (\Throwable $e) {
        //    return api_response($request, null, 500);
        //}
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
            $partner->load(['walletSetting', 'resources' => function ($q) {
                $q->verified()->type('Handyman');
            }, 'jobs' => function ($q) use ($statuses) {
                $q->info()->status($statuses)->with('resource');
            }]);
            $jobs = $partner->jobs;
            $resource_ids = $partner->resources->pluck('id')->unique();
            $assigned_resource_ids = $jobs->whereIn('status', [constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due']])->pluck('resource_id')->unique();
            $unassigned_resource_ids = $resource_ids->diff($assigned_resource_ids);
            $sales_stats = (new PartnerSalesStatistics($request->partner))->calculate();
            $info = array(
                'todays_jobs' => $jobs->filter(function ($job, $key) {
                    return $job->schedule_date == Carbon::now()->toDateString() && $job->status != constants('JOB_STATUSES')['Served'];
                })->count(),
                'tomorrows_jobs' => $jobs->filter(function ($job, $key) {
                    return $job->schedule_date == Carbon::tomorrow()->toDateString() && $job->status != constants('JOB_STATUSES')['Served'];
                })->count(),
                'accepted_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Accepted'])->count(),
                'schedule_due_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Schedule_Due'])->count(),
                'process_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Process'])->count(),
                'served_jobs' => $jobs->where('status', constants('JOB_STATUSES')['Served'])->count(),
                'total_resources' => $resource_ids->count(),
                'assigned_resources' => $assigned_resource_ids->count(),
                'unassigned_resources' => $unassigned_resource_ids->count(),
                'balance' => (double)$partner->wallet,
                'is_credit_limit_exceed' => $partner->isCreditLimitExceed(),
                'today' => $sales_stats->today->sale,
                'week' => $sales_stats->week->sale,
                'month' => $sales_stats->month->sale
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
            $sales_stats = (new PartnerSalesStatistics($partner))->calculate();
            $partner_orders = $this->partnerOrderRepository->getOrdersByClosedAt($partner, $start_time, $end_time);
            $breakdown = $this->partnerOrderRepository->getWeeklyBreakdown($partner_orders, $start_time, $end_time);
            $info = array('today' => $sales_stats->today->sale, 'week' => $sales_stats->week->sale, 'month' => $sales_stats->month->sale, 'year' => $sales_stats->year->sale, 'total' => $sales_stats->lifetime->sale);
            return api_response($request, $info, 200, ['info' => $info, 'breakdown' => $breakdown, 'orders' => $partner_orders]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getInfo($partner, Request $request)
    {
        try {
            $partner = $request->partner->load(['basicInformations', 'reviews', 'services' => function ($q) {
                $q->where('partner_service.is_verified', 1);
            }, 'locations']);
            $locations = $partner->locations;
            $basic_info = $partner->basicInformations;
            $info = collect($partner)->only(['id', 'name', 'mobile', 'email', 'verified_at', 'status', 'logo', 'wallet', 'address', 'created_at']);
            $info->put('total_rating', $partner->reviews->count());
            $info->put('avg_rating', $this->reviewRepository->getAvgRating($partner->reviews));
            $info->put('working_days', json_decode(collect($basic_info)->only('working_days')->get('working_days')));
            $working_hours = json_decode(collect($basic_info)->only('working_hours')->get('working_hours'));
            $info->put('working_hour_starts', $working_hours->day_start);
            $info->put('working_hour_ends', $working_hours->day_end);
            $info->put('locations', $locations->pluck('name'));
            $info->put('total_locations', $locations->count());
            $info->put('total_services', $partner->services->count());
            $info->put('total_resources', $partner->resources->count());
            $info->put('wallet', $partner->wallet);
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getNotifications($partner, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $notifications = (new NotificationRepository())->getManagerNotifications($request->partner, $offset, $limit);
            if (count($notifications) > 0) {
                return api_response($request, $notifications, 200, ['notifications' => $notifications->values()->all()]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function findPartners(Request $request, $location)
    {
        try {
            $this->validate($request, [
                'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time' => 'required|string',
                'services' => 'required|string',
                'isAvailable' => 'sometimes|required'
            ]);
            $partner_list = new PartnerList(json_decode($request->services), $request->date, $request->time, $location);
            $partner_list->find();
            if ($request->has('isAvailable')) {
                $partners = $partner_list->partners;
                $available_partners = $partners->filter(function ($partner) {
                    return $partner->is_available == 1;
                });
                $is_available = count($available_partners) != 0 ? 1 : 0;
                return api_response($request, $is_available, 200, ['is_available' => $is_available, 'available_partners' => count($available_partners)]);
            }
            if ($partner_list->hasPartners) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                $partner_list->calculateAverageRating();
                $partner_list->calculateTotalRatings();
                $partner_list->calculateOngoingJobs();
                $partner_list->sortByShebaSelectedCriteria();
                $partners = $partner_list->partners;
                $partners->each(function ($partner, $key) {
                    array_forget($partner, 'wallet');
                    removeRelationsAndFields($partner);
                });
                return api_response($request, $partners, 200, ['partners' => $partners->values()->all()]);
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }


}
