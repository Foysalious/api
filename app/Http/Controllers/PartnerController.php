<?php namespace App\Http\Controllers;

use App\Exceptions\HyperLocationNotFoundException;
use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Sheba\Dal\Category\Category;
use Sheba\Dal\CategoryPartner\CategoryPartner;
use Sheba\Dal\DeliveryChargeUpdateRequest\DeliveryChargeUpdateRequest;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Location;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\PartnerPosCustomer;
use App\Models\PartnerResource;
use Sheba\Dal\PartnerService\PartnerService;
use App\Models\PartnerServicePricesUpdate;
use App\Models\Resource;
use App\Models\ReviewQuestionAnswer;
use Sheba\Dal\Service\Service;
use App\Models\SubscriptionOrder;
use App\Repositories\DiscountRepository;
use App\Repositories\FileRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PartnerOrderRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\ResourceJobRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Sheba\Checkout\PartnerList;
use App\Sheba\Checkout\Validation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Sheba\Analysis\Sales\PartnerSalesStatistics;
use Sheba\Checkout\Partners\LitePartnerList;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Logistics\Repository\ParcelRepository;
use Sheba\Manager\JobList;
use Sheba\ModificationFields;
use Sheba\Notification\Partner\PartnerNotificationHandler;
use Sheba\Partner\LeaveStatus;
use Sheba\Partner\QRCode\AccountType;
use Sheba\Partner\Updater;
use Sheba\Reward\PartnerReward;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;

class PartnerController extends Controller
{
    use ModificationFields;

    const COMPLIMENT_QUESTION_ID = 2;
    private $serviceRepository;
    private $partnerServiceRepository;
    private $reviewRepository;
    private $resourceJobRepository;
    private $partnerOrderRepository;
    private $discountRepository;
    private $rentCarCategoryIds;
    private $days;
    private $fileRepository;
    private $profileRepo;

    public function __construct()
    {
        $this->serviceRepository        = new ServiceRepository();
        $this->reviewRepository         = new ReviewRepository();
        $this->resourceJobRepository    = new ResourceJobRepository();
        $this->partnerOrderRepository   = new PartnerOrderRepository();
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->discountRepository       = new DiscountRepository();
        $this->fileRepository           = new FileRepository();
        $this->rentCarCategoryIds       = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        $this->days                     = constants('WEEK_DAYS');
        $this->profileRepo              = new ProfileRepository();
    }

    public function index()
    {
        $partners = Partner::select('id', 'name', 'sub_domain', 'logo')->whereHas('services', function ($q) {
            $q->published();
        })->has('resources', '>', 0)->where('status', 'Verified')->orderBy('name')->get();
        return response()->json([
            'partners' => $partners,
            'code'     => 200,
            'msg'      => 'successful'
        ]);
    }

    public function show($partner, Request $request)
    {
        try {
            ini_set('memory_limit', '6096M');
            ini_set('max_execution_time', 660);
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location)->id;
            } else if ($request->has('lat')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation))
                    $location = $hyperLocation->location->id;
            }
            $partner_request = $partner;
            if (is_numeric($partner_request)) {
                $partner = Partner::find($partner_request);
            } else {
                $partner = Partner::where([
                    [
                        'sub_domain',
                        $partner_request
                    ]
                ])->first();
            }
            if ($partner == null)
                return api_response($request, null, 404);
            $serving_master_categories = $partner->servingMasterCategories();
            $badge                     = $partner->resolveBadge();
            $geo_informations          = $partner->geo_informations;
            $partner->load([
                'workingHours',
                'categories' => function ($q) {
                    $q->select('categories.id', 'name', 'thumb', 'icon', 'categories.slug')->where('category_partner.is_verified', 1)->published();
                },
                'reviews'    => function ($q) {
                    $q->with([
                        'rates' => function ($q) {
                            $q->select('review_id', 'review_type', 'rate_answer_id')->where('rate_question_id', self::COMPLIMENT_QUESTION_ID)->with([
                                'answer' => function ($q) {
                                    $q->select('id', 'answer', 'badge', 'asset');
                                }
                            ]);
                        }
                    ]);
                },
                'jobs'       => function ($q) {
                    $q->whereHas('resource', function ($query) {
                        $query->verified();
                    })->with([
                        'resource' => function ($q) {
                            $q->select('resources.id', 'profile_id', 'is_verified')->with('profile');
                        },
                        'review'   => function ($q) {
                            $q->select('id', 'job_id', 'resource_id', 'customer_id', 'rating', 'review', 'category_id', 'created_at')->with('customer.profile')->with('category');
                        }
                    ]);
                },
                'services'   => function ($q) {
                    $q->where('partner_service.is_verified', 1);
                },
                'locations'
            ]);
            $locations    = $partner->locations;
            $info         = collect($partner)->only([
                'id',
                'name',
                'sub_domain',
                'description',
                'email',
                'verified_at',
                'status',
                'logo',
                'address',
                'delivery_charge',
                'is_webstore_published',
                'created_at'
            ]);
            $info->put('mobile', $partner->getContactNumber());
            $banner = null;
            if($partner->webstoreBanner)
                $banner = [
                    'image_link' => $partner->webstoreBanner->banner->image_link,
                    'small_image_link' => $partner->webstoreBanner->banner->small_image_link,
                    'title'  => $partner->webstoreBanner->title,
                    'description' => $partner->webstoreBanner->description,
                    'is_published' => $partner->webstoreBanner->is_published
                ];
            $info->put('banner', $banner);
            $working_info = [];
            //$partner_not_available_days = array_diff( $this->days,$partner->workingHours->pluck('day')->toArray());
            foreach ($this->days as $day) {
                $current_day = $partner->workingHours->filter(function ($working_day) use ($day) {
                    return $day === $working_day->day;
                })->first();
                if ($current_day) {
                    array_push($working_info, array(
                        'day'       => $current_day->day,
                        'hour'      => (Carbon::parse($current_day->start_time))->format('g:i A') . '-' . (Carbon::parse($current_day->end_time))->format('g:i A'),
                        'is_today'  => $current_day->day === $this->days[Carbon::now()->dayOfWeek],
                        'is_closed' => false
                    ));
                } else {
                    array_push($working_info, array(
                        'day'       => $day,
                        'hour'      => null,
                        'is_today'  => $day === $this->days[Carbon::now()->dayOfWeek],
                        'is_closed' => true
                    ));
                }
            }
            $info->put('working_days', $working_info);
            $info->put('is_available', in_array(date('l'), collect($working_info)->pluck('day')->toArray()) ? 1 : 0);
            $info->put('total_locations', $locations->count());
            $info->put('total_services', $partner->services->count());
            $job_with_review = $partner->jobs->where('status', 'Served')->filter(function ($job) {
                return $job->resource_id != null && $job->review != null;
            });
            /*$resource_jobs = $job_with_review->groupBy('resource_id')->take(1);
            $all_resources = collect();
            foreach ($resource_jobs as $resource_job) {
                if ($partner_resource = PartnerResource::where('partner_id', $partner->id)
                        ->where('resource_id', $resource_job[0]->resource_id)->first() && $resource_job[0]->resource->is_verified) {
                        $resource = PartnerResource::where('partner_id', $partner->id)
                            ->where('resource_id', $resource_job[0]->resource_id)->first()->resource;

                            $all_resources->push(collect(['name' => $resource_job[0]->resource->profile->name,
                                'mobile' => $resource_job[0]->resource->profile->mobile, 'picture' => $resource_job[0]->resource->profile->pro_pic,
                                'total_rating' => $resource_job->count(), 'avg_rating' => round($resource_job->avg('review.rating'), 2),
                                'served_jobs' => $resource->totalServedJobs()]));


                }
            }*/
            $resources = PartnerResource::join('resources', 'resources.id', '=', 'partner_resource.resource_id')->join('profiles', 'resources.profile_id', '=', 'profiles.id')->join('reviews', 'reviews.resource_id', '=', 'resources.id')->where('reviews.partner_id', $partner->id)->where('partner_resource.partner_id', $partner->id)->where('resources.is_verified', 1)->groupBy('partner_resource.id')->selectRaw('distinct(resources.id), profiles.name, profiles.mobile, profiles.pro_pic,  avg(reviews.rating) as avg_rating, count(rating) as total_rating, (select count(jobs.id) from jobs where jobs.status = "Served" and jobs.resource_id = resources.id) as served_jobs')->orderBy(DB::raw('avg(reviews.rating)'), 'desc')->take(5)->get();
            foreach ($resources as $resource) {
                $resource['avg_rating'] = (float)round($resource->avg_rating, 2);
            }
            $info->put('resources', $resources);
            $partner_review = $partner->reviews()->pluck('id')->toArray();
            $partner_review = ReviewQuestionAnswer::where('review_type', 'App\Models\Review')->whereIn('review_id', $partner_review)->where('rate_answer_text', '<>', '')->orderBy('created_at', 'desc')->take(5)->pluck('rate_answer_text', 'review_id')->toArray();
            $reviews        = [];
            $job_with_review->filter(function ($job) use ($partner_review) {
                return $job->review->rating >= 4 && in_array($job->review->id, array_keys($partner_review));
            })->each(function ($job) use (&$reviews, $partner_review) {
                $final                  = $job->review;
                $final['customer_name'] = $job->review->customer->profile->name;
                $final['customer_pic']  = $job->review->customer->profile->pro_pic;
                $final['category_name'] = $job->review->category->name;
                $final['date']          = $job->review->created_at->format('F d, Y');
                $final['review']        = $partner_review[$job->review->id];
                removeRelationsAndFields($final);
                array_push($reviews, $final);
            });
            $info->put('reviews', $reviews);
            $info->put('categories', $partner->categories->each(function ($category) use ($location) {
                $category->service_count = $category->services()->published()->count();
                if ($location) {
                    if (in_array($location, $category->locations->pluck('id')->toArray())) {
                        $category->available = true;
                    } else {
                        $category->available = false;
                    }
                }
                removeRelationsAndFields($category);
            }));
            $compliment_counts = $partner->reviews->pluck('rates')->filter(function ($rate) {
                return $rate->count();
            })->flatten()->groupBy('rate_answer_id')->map(function ($answer, $index) {
                return [
                    'id'    => $index,
                    'name'  => $answer->first()->answer->answer,
                    'badge' => $answer->first()->answer->badge,
                    'asset' => $answer->first()->answer->asset,
                    'count' => $answer->count(),
                ];
            });
            $group_rating      = $partner->reviews->groupBy('rating')->map(function ($rate) {
                return $rate->count();
            });
            for ($i = 1; $i <= 5; $i++) {
                if (!isset($group_rating[$i]))
                    $group_rating[$i] = 0;
            }
            $info->put('compliments', $compliment_counts->values());
            $info->put('total_resources', $partner->resources()->verified()->selectRaw('count(distinct resource_id) as total_resources')->first()->total_resources);
            $info->put('total_jobs', $partner->jobs->count());
            $info->put('total_rating', $partner->reviews->count());
            $info->put('avg_rating', round($this->reviewRepository->getAvgRating($partner->reviews), 1));
            $info->put('group_rating', $group_rating);
            $info->put('master_category_names', $serving_master_categories);
            $info->put('badge', $badge);
            $geo_informations = json_decode($geo_informations);
            if ($geo_informations) {
                $geo_informations = array(
                    'lat'    => (float)$geo_informations->lat,
                    'lng'    => (float)$geo_informations->lng,
                    'radius' => (float)$geo_informations->radius,
                );
            }
            $info->put('geo_informations', $geo_informations);
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getServices($partner, $category, Request $request)
    {
        try {
            if ($partner = Partner::find((int)$partner)) {

                $services = $partner->services()->select($this->getSelectColumnsOfService())->where('category_id', $request->category)->where(function ($q) {
                    $q->where('publication_status', 1);
                    $q->orWhere('is_published_for_backend', 1);
                })->get();
                if (count($services) > 0) {
                    $services->each(function (&$service) {
                        $variables = json_decode($service->variables);
                        if ($service->variable_type == 'Options') {
                            $service['questions']     = $this->formatServiceQuestions($variables->options);
                            $service['option_prices'] = $this->formatOptionWithPrice(json_decode($service->pivot->prices));
                            $service['fixed_price']   = null;
                        } else {
                            $service['questions']   = $service['option_prices'] = [];
                            $service['fixed_price'] = (double)$variables->price;
                        }
                        array_forget($service, 'variables');
                        removeRelationsAndFields($service);
                    });
                    return api_response($request, null, 200, ['services' => $services]);
                } else {
                    return api_response($request, null, 404);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfService()
    {
        return [
            'services.id',
            'name',
            'is_published_for_backend',
            'variable_type',
            'services.min_quantity',
            'services.variables',
            'is_verified',
            'is_published',
            'app_thumb'
        ];
    }

    private function formatServiceQuestions($options)
    {
        $questions = collect();
        foreach ($options as $option) {
            $questions->push(array(
                'question' => $option->question,
                'answers'  => explode(',', $option->answers)
            ));
        }
        return $questions;
    }

    private function formatOptionWithPrice($prices)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $options->push(array(
                'option' => collect(explode(',', $key))->map(function ($key) {
                    return (int)$key;
                }),
                'price'  => (double)$price
            ));
        }
        return $options;
    }

    public function getReviews($partner)
    {
        $partner = Partner::with([
            'reviews' => function ($q) {
                $q->select('id', 'service_id', 'partner_id', 'customer_id', 'review_title', 'review', 'rating', DB::raw('DATE_FORMAT(updated_at, "%M %d,%Y at %h:%i:%s %p") as time'))->with([
                    'service' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])->with([
                    'customer' => function ($q) {
                        $q->select('id', 'profile_id')->with([
                            'profile' => function ($q) {
                                $q->select('id', 'name');
                            }
                        ]);
                    }
                ])->orderBy('updated_at', 'desc');
            }
        ])->select('id')->where('id', $partner)->first();
        if (count($partner->reviews) > 0) {
            $partner   = $this->reviewRepository->getGeneralReviewInformation($partner);
            $breakdown = $this->reviewRepository->getReviewBreakdown($partner->reviews);
            $reviews   = $partner->reviews->filter(function ($review, $key) {
                return $review->review != '' || $review->review != null;
            })->values()->all();
            array_forget($partner, 'reviews');
            $partner['reviews'] = $reviews;
            return response()->json([
                'msg'       => 'ok',
                'code'      => 200,
                'partner'   => $partner,
                'breakdown' => $breakdown
            ]);
        }
        return response()->json([
            'msg'  => 'not found',
            'code' => 404
        ]);
    }

    public function getReviewInfo($partner, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_id'  => 'sometimes|required|numeric',
                'resource_id' => 'sometimes|required|numeric'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            list($offset, $limit) = calculatePagination($request);
            $partner    = $request->partner->load([
                'reviews' => function ($q) use ($request) {
                    $q->with([
                        'job.partner_order.partner',
                        'resource.profile',
                        'category',
                        'rates'
                    ]);
                    if ($request->has('service_id')) {
                        $q->where('service_id', $request->service_id);
                    }
                    if ($request->has('resource_id')) {
                        $q->where('resource_id', $request->resource_id);
                    }
                }
            ]);
            $reviews    = $partner->reviews;
            $breakdown  = array_fill(1, 5, 0);
            $avg_rating = null;
            if (count($reviews) > 0) {
                $breakdown  = $this->reviewRepository->getReviewBreakdown($reviews);
                $partner    = $this->reviewRepository->getGeneralReviewInformation($partner);
                $avg_rating = $this->reviewRepository->getAvgRating($reviews);
                $reviews    = $reviews->each(function ($review) {
                    $review->review = $review->calculated_review;
                })->filter(function ($review) {
                    return !empty($review->review);
                })->each(function ($review, $key) {
                    $review['order_id']      = $review->job->partner_order->id;
                    $review['order_code']    = $review->job->partner_order->code();
                    $review['partner']       = $review->job->partner_order->partner->name;
                    $review['resource_name'] = ($review->resource) ? $review->resource->profile->name : null;
                    $review['resource_pic']  = ($review->resource) ? $review->resource->profile->pro_pic : null;
                    $review['service_name']  = $review->category ? $review->category->name : null;
                    removeRelationsAndFields($review);
                })->sortByDesc('created_at');
                removeRelationsAndFields($partner);
            }
            $info = array(
                'rating'        => $avg_rating,
                'total_reviews' => $reviews->count(),
                'reviews'       => array_slice($reviews->values()->all(), $offset, $limit),
                'breakdown'     => $breakdown
            );
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param         $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function getResources($partner, Request $request)
    {
        try {
            ini_set('memory_limit', '2048M');
            $this->validate($request, [
                'type'                  => 'sometimes|required|string',
                'verified'              => 'sometimes|required',
                'date'                  => 'sometimes|required|date',
                'time'                  => 'sometimes|required',
                'job_id'                => 'sometimes|required|numeric|exists:jobs,id',
                'category_id'           => 'sometimes|required|numeric',
                'subscription_order_id' => 'sometimes|required|numeric|exists:subscription_orders,id'
            ]);
            $partnerRepo = new PartnerRepository($request->partner);
            $verified    = $request->has('verified') ? (int)$request->verified : null;
            $category_id = $date = $preferred_time = $job = $subscription_order = null;
            if ($request->has('job_id')) {
                $job            = Job::find((int)$request->job_id);
                $category_id    = $job->category_id;
                $date           = $job->schedule_date;
                $preferred_time = $job->preferred_time;
            } elseif ($request->has('subscription_order_id')) {
                $subscription_order = SubscriptionOrder::find((int)$request->subscription_order_id);
                $category_id        = $subscription_order->category_id;
            } elseif ($request->has('category_id') && $request->has('date') && $request->has('time')) {
                $category_id    = $request->category_id;
                $date           = $request->date;
                $preferred_time = $request->time;
            }
            $resources = $partnerRepo->resources($verified, $category_id, $date, $preferred_time, $job, $subscription_order);
            if (count($resources) > 0) {
                return api_response($request, $resources, 200, ['resources' => $resources->sortBy('name')->values()->all()]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context([
                'request' => $request->all(),
                'message' => $message
            ]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDashboardInfo($partner, Request $request, PartnerReward $partner_reward)
    {
        try {
            $partner  = $request->partner;
            $statuses = array(
                constants('JOB_STATUSES')['Accepted'],
                constants('JOB_STATUSES')['Schedule_Due'],
                constants('JOB_STATUSES')['Process'],
                constants('JOB_STATUSES')['Served'],
                constants('JOB_STATUSES')['Serve_Due'],
            );
            $partner->load([
                'walletSetting',
                'resources' => function ($q) {
                    $q->verified()->type('Handyman');
                },
                'jobs'      => function ($q) use ($statuses) {
                    $q->info()->status($statuses)->with([
                        'resource',
                        'cancelRequests' => function ($q) {
                            $q->where('status', 'Pending');
                        }
                    ]);
                }
            ]);
            $jobs                    = $partner->jobs->reject(function ($job) {
                return $job->cancelRequests->count() > 0;
            });
            $resource_ids            = $partner->resources->pluck('id')->unique();
            $assigned_resource_ids   = $jobs->whereIn('status', [
                constants('JOB_STATUSES')['Process'],
                constants('JOB_STATUSES')['Accepted'],
                constants('JOB_STATUSES')['Schedule_Due']
            ])->pluck('resource_id')->unique();
            $unassigned_resource_ids = $resource_ids->diff($assigned_resource_ids);
            $sales_stats             = (new PartnerSalesStatistics($request->partner))->calculate();
            $info                    = [
                'todays_jobs'            => $jobs->filter(function ($job, $key) {
                    return $job->schedule_date == Carbon::now()->toDateString() && !in_array($job->status, [
                            'Served',
                            'Cancelled',
                            'Declined'
                        ]);
                })->count(),
                'tomorrows_jobs'         => $jobs->filter(function ($job, $key) {
                    return $job->schedule_date == Carbon::tomorrow()->toDateString() && !in_array($job->status, [
                            'Served',
                            'Cancelled',
                            'Declined'
                        ]);
                })->count(),
                'accepted_jobs'          => $jobs->where('status', constants('JOB_STATUSES')['Accepted'])->count(),
                'schedule_due_jobs'      => $jobs->where('status', constants('JOB_STATUSES')['Schedule_Due'])->count(),
                'process_jobs'           => $jobs->where('status', constants('JOB_STATUSES')['Process'])->count(),
                'served_jobs'            => $jobs->where('status', constants('JOB_STATUSES')['Served'])->count(),
                'serve_due_jobs'         => $jobs->where('status', constants('JOB_STATUSES')['Serve_Due'])->count(),
                'cancelled_jobs'         => $jobs->where('status', constants('JOB_STATUSES')['Cancelled'])->count(),
                'total_ongoing_orders'   => (new JobList($partner))->ongoing()->count(),
                'total_open_complains'   => $partner->complains->whereIn('status', [
                    'Observation',
                    'Open'
                ])->count(),
                'total_resources'        => $resource_ids->count(),
                'assigned_resources'     => $assigned_resource_ids->count(),
                'unassigned_resources'   => $unassigned_resource_ids->count(),
                'bkash_no'               => $partner->bkash_no,
                'balance'                => $partner->totalWalletAmount(),
                'credit'                 => (double)$partner->wallet,
                'bonus'                  => round($partner->bonusWallet(), 2),
                'is_credit_limit_exceed' => $partner->isCreditLimitExceed(),
                'geo_informations'       => $partner->geo_informations,
                'today'                  => $sales_stats->today->sale,
                'week'                   => $sales_stats->week->sale,
                'month'                  => $sales_stats->month->sale,
                'reward_point'           => $partner->reward_point,
                'has_reward_campaign'    => count($partner_reward->upcoming()) > 0 ? 1 : 0
            ];
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getEarnings($partner, Request $request)
    {
        try {
            Carbon::setWeekStartsAt(Carbon::SUNDAY);
            Carbon::setWeekEndsAt(Carbon::SATURDAY);
            $start_time     = Carbon::now()->startOfWeek();
            $end_time       = Carbon::now()->endOfWeek();
            $partner        = $request->partner;
            $sales_stats    = (new PartnerSalesStatistics($partner))->calculate();
            $partner_orders = $this->partnerOrderRepository->getOrdersByClosedAt($partner, $start_time, $end_time);
            $breakdown      = $this->partnerOrderRepository->getWeeklyBreakdown($partner_orders, $start_time, $end_time);
            $info           = array(
                'today' => $sales_stats->today->sale,
                'week'  => $sales_stats->week->sale,
                'month' => $sales_stats->month->sale,
                'year'  => $sales_stats->year->sale,
                'total' => $sales_stats->lifetime->sale
            );
            return api_response($request, $info, 200, [
                'info'      => $info,
                'breakdown' => $breakdown,
                'orders'    => $partner_orders
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getInfo($partner, Request $request)
    {
        try {
            $partner    = $request->partner->load([
                'basicInformations',
                'reviews',
                'services' => function ($q) {
                    $q->where('partner_service.is_verified', 1);
                },
                'locations'
            ]);
            $locations  = $partner->locations;
            $basic_info = $partner->basicInformations;
            $info       = collect($partner)->only([
                'id',
                'name',
                'mobile',
                'email',
                'verified_at',
                'status',
                'logo',
                'wallet',
                'address',
                'created_at'
            ]);
            $info->put('total_rating', $partner->reviews->count());
            $info->put('avg_rating', $this->reviewRepository->getAvgRating($partner->reviews));
            $info->put('working_days', json_decode(collect($basic_info)->only('working_days')->get('working_days')));
            $working_hours = json_decode(collect($basic_info)->only('working_hours')->get('working_hours'));
            $info->put('working_hour_starts', $working_hours->day_start);
            $info->put('working_hour_ends', $working_hours->day_end);
            $info->put('locations', $locations->pluck('name'));
            $info->put('subscription', $partner->subscription()->select('id', 'name', 'show_name', 'show_name_bn', 'badge')->first());
            $info->put('total_locations', $locations->count());
            $info->put('total_services', $partner->services->count());
            $info->put('total_resources', $partner->resources->count());
            $info->put('wallet', $partner->wallet);
            $info->put('leave_status', (new LeaveStatus($partner))->getCurrentStatus());
            return api_response($request, $info, 200, ['info' => $info]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param         $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotifications($partner, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            /** @var Partner $partner */
            $partner = $request->partner;
            list($notifications, $counter) = (new PartnerNotificationHandler($partner))->setPortal($request->header('portal-name'))->getList($offset, $limit);
            if (count($notifications) > 0) {
                return api_response($request, $notifications, 200, [
                    'notifications' => $notifications->values()->all(),
                    'unseen'        => $counter
                ]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getNotification($partner, $notification, Request $request)
    {
        try {
            list($notification, $unseen_notifications) = (new PartnerNotificationHandler($request->partner))->getDetails($notification);
            return api_response($request, $notification, 200, [
                'notification'         => $notification,
                'unseen_notifications' => $unseen_notifications
            ]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function findPartners(Request $request, $location, PartnerListRequest $partnerListRequest)
    {
        try {
            $this->validate($request, [
                'date'              => 'sometimes|required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
                'time'              => 'sometimes|required|string',
                'services'          => 'required|string',
                'isAvailable'       => 'sometimes|required',
                'skip_availability' => 'sometimes|required|numeric|in:0,1',
                'partner'           => 'sometimes|required',
                'filter'            => 'sometimes|required|in:sheba',
                'has_premise'       => 'sometimes|required',
                'has_home_delivery' => 'sometimes|required'
            ]);
            $validation = new Validation($request);
            if (!$validation->isValid()) {
                return api_response($request, $validation->message, 400, ['message' => $validation->message]);
            }
            $partner = $request->has('partner') ? $request->partner : null;
            $partnerListRequest->setRequest($request)->prepareObject();
            $partner_list = new PartnerList();
            $partner_list->setPartnerListRequest($partnerListRequest)->find($partner);
            if ($request->has('isAvailable')) {
                $partners           = $partner_list->partners;
                $available_partners = $partners->filter(function ($partner) {
                    return $partner->is_available == 1;
                });
                $is_available       = count($available_partners) != 0 ? 1 : 0;
                return api_response($request, $is_available, 200, [
                    'is_available'       => $is_available,
                    'available_partners' => count($available_partners)
                ]);
            }
            if ($partner_list->hasPartners) {
                $partner_list->addPricing();
                $partner_list->addInfo();
                if ($request->has('filter') && $request->filter == 'sheba') {
                    $partner_list->sortByShebaPartnerPriority();
                } else {
                    $partner_list->sortByShebaSelectedCriteria();
                }
                $partners = $partner_list->removeKeysFromPartner()->values()->all();
                if (count($partners) < 50) {
                    $lite_list = new LitePartnerList();
                    $lite_list->setPartnerListRequest($partnerListRequest)->setLimit(50 - count($partners))->find($partner);
                    $lite_list->addInfo();
                    $lite_partners = $lite_list->removeKeysFromPartner()->values()->all();
                } else {
                    $lite_partners = [];
                }
                return api_response($request, $partners, 200, [
                    'partners'      => $partners,
                    'lite_partners' => $lite_partners
                ]);
            }
            return api_response($request, null, 404, ['message' => 'No partner found.']);
        } catch (HyperLocationNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'Your are out of service area.']);
        } catch (InsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, [
                'message' => 'Please try with outside city for this location.',
                'code'    => 700
            ]);
        } catch (OutsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, [
                'message' => 'This service isn\'t available at this location.',
                'code'    => 701
            ]);
        } catch (DestinationCitySameAsPickupException $e) {
            return api_response($request, null, 400, [
                'message' => 'Please try with inside city for this location.',
                'code'    => 702
            ]);
        }
    }

    public function getLocations($partner, Request $request)
    {
        try {
            $partner = Partner::find($partner);
            if (!$partner)
                return api_response($request, null, 404);
            $geo_info = json_decode($partner->geo_informations);
            if (!$geo_info)
                return api_response($request, null, 404);
            $locations = collect();
            HyperLocal::insideCircle($geo_info)->with('location')->get()->pluck('location')->filter()->each(function ($location) use (&$locations) {
                $locations->push([
                    'id'   => $location->id,
                    'name' => $location->name,
                    'lat'  => $location->geo_informations ? json_decode($location->geo_informations)->lat : null,
                    'lng'  => $location->geo_informations ? json_decode($location->geo_informations)->lng : null,
                ]);
            });
            if ($locations->count() == 0)
                return api_response($request, null, 404);
            return api_response($request, $locations, 200, ['locations' => $locations]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getCategories($partner, Request $request)
    {
        try {
            $partner = Partner::find($partner);
            if (!$partner->isLite()) {
                $partner = $partner->load([
                    'categories' => function ($query) {
                        return $query->published()->wherePivot('is_verified', 1);
                    }
                ]);
            } else {
                $partner = $partner->load([
                    'categories' => function ($query) {
                        return $query->published();
                    }
                ]);
            }
            if ($partner) {
                $categories = collect();
                foreach ($partner->categories as $category) {
                    $services = $partner->services()->select('services.id', 'name', 'variable_type', 'services.min_quantity', 'services.variables')->where('category_id', $category->id)->where(function ($q) {
                        $q->where('publication_status', 1);
                        $q->oRwhere('is_published_for_backend', 1);
                    })->wherePivot('is_published', 1)->publishedForAll();
                    if (!$partner->isLite()) {
                        $services = $services->wherePivot('is_verified', 1);
                    }
                    $services       = $services->get();
                    $final_services = [];
                    if (count($services) > 0) {
                        foreach ($services as $service) {
                            if (!$service->pivot->prices) continue;
                            $variables = json_decode($service->variables);
                            if ($service->variable_type == 'Options') {
                                $prices = json_decode($service->pivot->prices, 1);
                                if (!is_array($prices)) continue;
                                $service['questions']     = $this->formatServiceQuestions($variables->options);
                                $service['option_prices'] = $this->formatOptionWithPrice(json_decode($service->pivot->prices));
                                $service['fixed_price']   = null;
                            } else {
                                $service['questions']   = $service['option_prices'] = [];
                                $service['fixed_price'] = (double)$variables->price;
                            }
                            array_forget($service, 'variables');
                            removeRelationsAndFields($service);
                            array_push($final_services, $service);
                        }
                    }
                    $categories->push([
                        'id'          => $category->id,
                        'name'        => $category->name,
                        'app_thumb'   => $category->app_thumb,
                        'services'    => $final_services,
                        'is_verified' => $category->pivot->is_verified
                    ]);
                }
                if (count($categories) > 0) {
                    $hasCarRental = $categories->filter(function ($category) {
                        return in_array($category['id'], $this->rentCarCategoryIds);
                    })->count() > 0 ? 1 : 0;
                    $hasOthers    = $categories->filter(function ($category) {
                        return !in_array($category['id'], $this->rentCarCategoryIds);
                    })->count() > 0 ? 1 : 0;
                    return api_response($request, $categories, 200, [
                        'categories'     => $categories,
                        'has_car_rental' => $hasCarRental,
                        'has_others'     => $hasOthers
                    ]);
                }
            }
            return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getCategoriesTree($partner, Request $request, ParcelRepository $parcelRepository)
    {
        try {
            $partner = Partner::with([
                'categories' => function ($query) {
                    return $query->select('categories.id', 'name', 'parent_id', 'thumb', 'app_thumb', 'categories.is_home_delivery_applied', 'categories.is_partner_premise_applied', 'categories.is_logistic_available', 'categories.logistic_parcel_type')->published()->with([
                        'parent' => function ($query) {
                            return $query->select('id', 'name', 'thumb', 'app_thumb');
                        }
                    ]);
                }
            ])->find($partner);
            if ($partner) {
                $number_of_services_with_sheba_delivery = 0;
                $master_categories                      = collect();
                foreach ($partner->categories as $category) {
                    $published_services   = $partner->services()->where('category_id', $category->id)->wherePivot('is_published', 1)->wherePivot('is_verified', 1)->published()->count();
                    $unpublished_services = $partner->services()->where('category_id', $category->id)->wherePivot('is_published', 0)->wherePivot('is_verified', 1)->published()->count();
                    $master_category      = $master_categories->where('id', $category->parent->id)->first();
                    if (!$master_category) {
                        $master_category = [
                            'id'                 => $category->parent->id,
                            'name'               => $category->parent->name,
                            'app_thumb'          => $category->parent->app_thumb,
                            'secondary_category' => collect()
                        ];
                        $master_categories->push($master_category);
                    }
                    $category_partner               = CategoryPartner::where('category_id', $category->id)->where('partner_id', $partner->id)->first();
                    $delivery_charge_update_request = DeliveryChargeUpdateRequest::where('category_partner_id', $category_partner->id)->first();
                    $logistic_price                 = 0;
                    if ($category->logistic_parcel_type) {
                        $type = (object)$parcelRepository->findBySlug($category->logistic_parcel_type);
                        if ($type) {
                            $logistic_price = $type->price;
                        }

                    }
                    if ($category->is_logistic_available)
                        $number_of_services_with_sheba_delivery++;
                    $category = [
                        'id'                               => $category->id,
                        'name'                             => $category->name,
                        'parent_id'                        => $category->parent_id,
                        'thumb'                            => $category->thumb,
                        'app_thumb'                        => $category->app_thumb,
                        'is_verified'                      => $category->pivot->is_verified,
                        'is_sheba_home_delivery_applied'   => $category->is_home_delivery_applied,
                        'is_sheba_partner_premise_applied' => $category->is_partner_premise_applied,
                        'is_home_delivery_applied'         => $category->pivot->is_home_delivery_applied,
                        'is_partner_premise_applied'       => $category->pivot->is_partner_premise_applied,
                        'delivery_charge'                  => $category->pivot->is_home_delivery_applied ? (double)$category->pivot->delivery_charge : $logistic_price,
                        'published_services'               => $published_services,
                        'unpublished_services'             => $unpublished_services,
                        'is_logistic_available'            => $category->is_logistic_available,
                        'uses_sheba_logistic'              => $category_partner->uses_sheba_logistic,
                        'status'                           => $delivery_charge_update_request ? $delivery_charge_update_request->status : null
                    ];
                    $master_category['secondary_category']->push($category);
                }
                return api_response($request, $master_categories, 200, [
                    'master_categories'                      => $master_categories,
                    'number_of_services_with_sheba_delivery' => $number_of_services_with_sheba_delivery
                ]);
            }
            return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function changePublicationStatus($partner, $category, $service, Request $request)
    {
        try {
            $partner         = Partner::find((int)$partner);
            $partner_service = new PartnerService();
            $partner_service = $partner_service->where('partner_id', $request->partner_id)->where('service_id', $request->service_id)->first();
            if ($partner_service) {
                $data['is_published'] = !$partner_service->is_published;
                $this->setModifier($partner);
                $partner_service->update($this->withUpdateModificationField($data));
                return api_response($request, null, 200, ['message' => 'Your service(s) will be updated within 2 working days.']);
            } else {
                return api_response($request, null, 500);
            }
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getSecondaryCategory($partner, $category, Request $request)
    {
        try {
            $partner          = Partner::find((int)$partner);
            $category_partner = new CategoryPartner();
            $category_partner = CategoryPartner::select($this->getSelectColumnsOfCategory())->where('partner_id', $request->partner->id)->where('category_id', $request->category)->first();
            if ($category_partner) {
                $secondary_category = $category_partner;
                return api_response($request, $secondary_category, 200, ['secondary_category' => $secondary_category]);
            }
            return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfCategory()
    {
        return [
            'id',
            'category_id',
            'partner_id',
            'is_home_delivery_applied',
            'is_partner_premise_applied',
            'delivery_charge'
        ];
    }

    public function serviceOption($partner, $category, $service, Request $request)
    {
        try {
            if ($partner = Partner::find((int)$partner)) {
                $service = $partner->services()->select('services.id', 'name', 'variable_type', 'services.min_quantity', 'services.variables', 'services.is_published_for_b2b')->where('services.id', $service)->first();
                if (count($service) > 0) {
                    $variables                    = json_decode($service->variables);
                    $partner_service_price_update = PartnerServicePricesUpdate::where('partner_service_id', $service->pivot->id)->where('status', 'Pending')->first();
                    $old_prices                   = $partner_service_price_update ? json_decode($partner_service_price_update->old_prices, 1) : null;
                    $new_prices                   = $partner_service_price_update ? json_decode($partner_service_price_update->new_prices, 1) : json_decode($service->pivot->prices, 1);
                    if ($service->variable_type == 'Options') {
                        $service['questions']       = $this->formatServiceQuestions($variables->options);
                        $service['option_prices']   = $this->formatOptionWithOldPrice($new_prices, $old_prices);
                        $service['fixed_price']     = null;
                        $service['fixed_old_price'] = null;
                    } else {
                        $service['questions']       = $service['option_prices'] = [];
                        $service['fixed_price']     = (double)$service->pivot->prices;
                        $service['fixed_old_price'] = $partner_service_price_update ? (double)$partner_service_price_update->new_prices : null;
                    }
                    $service['is_published_for_b2b'] = $service->is_published_for_b2b ? true : false;
                    array_forget($service, 'variables');
                    removeRelationsAndFields($service);
                    return api_response($request, null, 200, ['service' => $service]);
                } else {
                    return api_response($request, null, 404);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatOptionWithOldPrice($prices, $old_prices)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $options->push(array(
                'option'    => collect(explode(',', $key))->map(function ($key) {
                    return (int)$key;
                }),
                'price'     => (double)$price,
                'old_price' => is_null($old_prices) ? null : (isset($old_prices[$key]) ? (double)$old_prices[$key] : null)
            ));
        }
        return $options;
    }

    public function storeBkashNumber($partner, Request $request)
    {
        try {
            $this->validate($request, ['bkash_no' => 'required|string|mobile:bd']);
            $bkash_no         = formatMobile($request->bkash_no);
            $data['bkash_no'] = $bkash_no;
            $this->setModifier($request->partner);
            $request->partner->update($this->withUpdateModificationField($data));
            return api_response($request, null, 200, ['message' => "Update Successful"]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAddableServices($partner, $category, Request $request)
    {
        try {
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation))
                    $location = $hyperLocation->location;
            }
            if ($partner = Partner::find((int)$partner)) {
                $registered_services = $partner->services()->where('category_id', $request->category)->publishedForAll()->get()->pluck('id')->toArray();
                $addable_services    = Service::where('category_id', $request->category)->select($this->getSelectColumnsOfAddableService())->whereNotIn('id', $registered_services)->publishedForAll()->get();
                if (!is_null($location)) {
                    $addable_services = $addable_services->filter(function ($service) use ($location) {
                        $locations = $service->locations->pluck('id')->toArray();
                        return in_array($location->id, $locations);
                    });
                }
                if (count($addable_services) > 0) {
                    return api_response($request, null, 200, ['addable_services' => $addable_services]);
                } else {
                    return api_response($request, null, 404);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getSelectColumnsOfAddableService()
    {
        return [
            'services.id',
            'name',
            'is_published_for_backend',
            'thumb',
            'app_thumb',
            'is_published_for_business',
            'publication_status'
        ];
    }

    public function getLocationWiseCategory(Request $request, $partner, $location)
    {
        try {
            $categories = $request->partner->categories()->published()->where('is_verified', 1)->select('categories.name', 'categories.id')->whereExists(function ($query) use ($location) {
                $query->from('category_location')->where('location_id', $location)->whereRaw('category_id=categories.id');
            })->get();
            return api_response($request, $request, 200, ['categories' => $categories]);
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getLocationWiseCategoryService(Request $request, $partner, $category)
    {
        try {
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation))
                    $location = $hyperLocation->location;
            }
            $service_base_query = $request->partner->services()->whereHas('locations', function ($query) use ($location) {
                $query->where('locations.id', $location->id);
            })->where('category_id', $category);
            if ($request->has('publication_status')) {
                $service_base_query = $request->publication_status ? $service_base_query->where('is_published', 1) : $service_base_query->where('is_published', 0);
            }
            $service = $service_base_query->select('services.id', 'services.name', 'services.variable_type', 'services.app_thumb')->get();
            return api_response($request, $request, 200, ['services' => $service]);
        } catch (Throwable $e) {
            return api_response($request, null, 500, ['message' => $e->getMessage()]);
        }
    }

    public function untaggedCategories(Request $request)
    {
        try {
            $location = null;
            if ($request->has('location')) {
                $location = Location::find($request->location);
            } else if ($request->has('lat') && $request->has('lng')) {
                $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                if (!is_null($hyperLocation))
                    $location = $hyperLocation->location;
            }
            $categories        = Category::child()->publishedOrPublishedForBusiness()->whereDoesntHave('partners', function ($query) use ($request) {
                return $query->where('partner_id', $request->partner->id);
            });
            $master_categories = Category::publishedForAll()->select('id', 'name', 'app_thumb', 'icon', 'icon_png');
            if ($location) {
                $categories        = $categories->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location->id);
                });
                $master_categories = $master_categories->whereHas('locations', function ($q) use ($location) {
                    $q->where('locations.id', $location->id);
                });
                $master_categories = $master_categories->whereHas('allChildren', function ($q) use ($location, $request) {
                    $request->has('is_business') && (int)$request->is_business ? $q->publishedForBusiness() : $q->published();
                    $q->whereHas('locations', function ($query) use ($location) {
                        $query->where('locations.id', $location->id);
                    });
                    $q->whereHas('services', function ($q) use ($location) {
                        $q->published()->whereHas('locations', function ($q) use ($location) {
                            $q->where('locations.id', $location->id);
                        });
                    })->whereDoesntHave('partners', function ($query) use ($request) {
                        return $query->where('partner_id', $request->partner->id);
                    });;
                });
            }
            $categories        = $categories->get();
            $master_categories = $master_categories->get();
            foreach ($categories as $category) {
                $master_category = $master_categories->where('id', $category->parent_id)->first();
                if (is_null($master_category['sub_categories']))
                    $master_category['sub_categories'] = collect([]);
                $master_category['sub_categories']->push([
                    'id'        => $category->id,
                    'name'      => $category->name,
                    'app_thumb' => $category->app_thumb,
                    'icon'      => $category->icon,
                    'icon_png'  => $category->icon_png
                ]);
            }
            return api_response($request, $master_categories, 200, ['categories' => $master_categories]);
        } catch (Throwable $exception) {
            app('sentry')->captureException($exception);
            return api_response($request, null, 500);
        }
    }

    public function updateSecondaryCategory($partner, $category, Request $request)
    {
        try {
            $partner          = Partner::find((int)$partner);
            $category_partner = new CategoryPartner();
            $category_partner = $category_partner->where('partner_id', $request->partner_id)->where('category_id', $request->category_id)->first();
            $this->setModifier($partner);
            if ($category_partner->is_verified) {
                if ($this->isRequestCreatable($request->partner_id, $request->category_id)) {
                    if ($request->has('bulk')) {
                        $categories        = $partner->categories()->where('is_logistic_available', true)->pluck('categories.id')->toArray();
                        $category_partners = CategoryPartner::whereIn('category_id', $categories)->where('partner_id', $partner->id);
                        foreach ($category_partners as $current_category_partner) {
                            $this->createDeliveryChargeUpdateRequest($current_category_partner, $request);
                        }
                    } else {
                        $this->createDeliveryChargeUpdateRequest($category_partner, $request);
                    }
                    return api_response($request, 1, 200, ['message' => 'Your home delivery charge will be updated within 2 working days.']);
                } else {
                    return api_response($request, null, 403, ['message' => 'You already have a pending a request']);
                }
            } else {
                $category_partner->update($this->withUpdateModificationField([
                    'is_home_delivery_applied'   => $request->has('is_home_delivery_applied') ? 1 : 0,
                    'is_partner_premise_applied' => $request->has('on_premise') ? 1 : 0,
                    'delivery_charge'            => $request->has('is_home_delivery_applied') ? $request->delivery_charge : 0,
                    'uses_sheba_logistic'        => $this->doesUseShebaLogistic(Category::find($category), $request) ? 1 : 0,
                ]));
                return api_response($request, 1, 200);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function isRequestCreatable($partner_id, $category_id)
    {
        return CategoryPartner::where('category_id', $category_id)->where('partner_id', $partner_id)->first()->deliveryChargeUpdateRequest()->Status(constants('DELIVERY_CHARGE_UPDATE_STATUSES')['pending'])->count() ? false : true;
    }

    private function createDeliveryChargeUpdateRequest($category_partner, $request)
    {
        list($old_category_partner_info, $new_category_partner_info) = $this->formatData($category_partner, $request);
        DeliveryChargeUpdateRequest::create($this->withCreateModificationField([
            'category_partner_id'       => $category_partner->id,
            'old_category_partner_info' => json_encode($old_category_partner_info),
            'new_category_partner_info' => json_encode($new_category_partner_info)
        ]));
    }

    private function formatData($category_partner, Request $request)
    {
        $category = Category::find($category_partner->category_id);
        $old      = [
            'is_home_delivery_applied'   => $category_partner->is_home_delivery_applied,
            'is_partner_premise_applied' => $category_partner->is_partner_premise_applied,
            'delivery_charge'            => $category_partner->delivery_charge,
            'uses_sheba_logistic'        => $category_partner->uses_sheba_logistic
        ];
        $new      = [
            'is_home_delivery_applied'   => $request->has('is_home_delivery_applied') ? 1 : 0,
            'is_partner_premise_applied' => $request->has('on_premise') ? 1 : 0,
            'delivery_charge'            => $request->has('is_home_delivery_applied') ? $request->delivery_charge : 0,
            'uses_sheba_logistic'        => $this->doesUseShebaLogistic($category, $request),
        ];
        return [
            $old,
            $new
        ];
    }

    private function doesUseShebaLogistic(Category $category, Request $request)
    {
        return $category->is_home_delivery_applied && $category->is_logistic_available && $request->has('uses_sheba_logistic') && $request->uses_sheba_logistic;
    }

    /**
     * @param         $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function getServedCustomers($partner, Request $request)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 660);
        try {
            $partner_orders   = PartnerOrder::where('partner_id', $partner)->whereNotNull('closed_and_paid_at')->with([
                'jobs' => function ($q) {
                    $q->with([
                        'category' => function ($q1) {
                            $q1->select('id', 'name');
                        }
                    ]);
                },
                'order.customer.profile'
            ])->orderBy('closed_and_paid_at', 'desc')->get();
            $served_customers = collect();
            foreach ($partner_orders as $partner_order) {
                if (!$partner_order->order->customer || !$partner_order->order->customer->profile->mobile)
                    continue;
                if (!$served_customers->contains('mobile', $partner_order->order->customer->profile->mobile))
                    $customer = $partner_order->order->customer->profile;
                $served_customers->push([
                    'name'     => $customer->name,
                    'mobile'   => $customer->mobile,
                    'image'    => $customer->pro_pic,
                    'category' => $partner_order->jobs[0]->category->name
                ]);
            }
            PartnerPosCustomer::with('customer.profile')->byPartner($partner)->get()->each(function ($pos_customer) use ($served_customers) {
                $customer = $pos_customer->customer->profile;
                $served_customers->push([
                    'name'     => $customer->name,
                    'mobile'   => $customer->mobile,
                    'image'    => $customer->pro_pic,
                    'category' => 'Pos Category'
                ]);
            });
            $served_customers = $served_customers->unique('mobile')->values();
            return api_response($request, $served_customers, 200, ['customers' => $served_customers]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function changeLeaveStatus($partner, Request $request)
    {
        try {
            $status = (new LeaveStatus(Partner::find($partner)))->changeStatus()->getCurrentStatus();
            return api_response($request, $status, 200, ['status' => $status]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * VAT REGISTRATION NUMBER ADD FOR PARTNER
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addVatRegistrationNumber(Request $request)
    {
        try {
            $this->validate($request, ['vat_registration_number' => 'required']);
            /** @var Partner $partner */
            $partner = $request->partner;
            $this->setModifier($request->manager_resource);
            $partner->basicInformations()->update($this->withUpdateModificationField(['vat_registration_number' => $request->vat_registration_number]));
            return api_response($request, null, 200, ['msg' => 'Vat Registration Number Update Successfully']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function changeLogo($partner, Request $request)
    {
        try {
            $this->validate($request, ['logo' => 'required|file|image']);
        } catch (ValidationException $e) {
            $messages = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $messages]);
        }
        $partner = Partner::find($partner);
        $repo = new PartnerRepository($partner);
        $logo = $repo->updateLogo($request);
        return api_response($request, $logo, 200, ['logo' => $logo]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getResourceTypes(Request $request)
    {
        try {
            $resource_types     = [];
            $all_resource_types = constants('RESOURCE_TYPES');
            foreach ($all_resource_types as $key => $unit) {
                array_push($resource_types, $unit);
            }
            return api_response($request, null, 200, ['resource_types' => $resource_types]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBusinessTypes(Request $request)
    {
        try {
            return api_response($request, null, 200, ['partner_business_types' => constants('PARTNER_BUSINESS_TYPE')]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getWalletBalance(Request $request)
    {
        try {
            $wallet_balance = $request->partner->wallet;
            return api_response($request, null, 200, ['wallet_balance' => $wallet_balance]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function setQRCode(Request $request)
    {
        try {
            $account_type = array_keys(config('partner.qr_code.account_types'));
            $account_type = implode(',', $account_type);
            $this->validate($request, [
                'account_type' => "required|in:$account_type",
                'image'        => "required|mimes:jpeg,png,jpg",
            ]);
            $image   = $request->file('image');
            $partner = $request->partner;
            if ($partner->qr_code_image) {
                $file_name = substr($partner->qr_code_image, strlen(env('S3_URL')));
                $this->fileRepository->deleteFileFromCDN($file_name);
            }
            $file_name  = $partner->id . '_QR_code' . '.' . $image->extension();
            $image_link = $this->fileRepository->uploadToCDN($file_name, $request->file('image'), 'partner/qr-code/');
            $this->setModifier($partner);
            $partner->update($this->withUpdateModificationField([
                'qr_code_account_type' => $request->account_type,
                'qr_code_image'        => $image_link,
            ]));
            return api_response($request, null, 200, ['message' => 'QR code set successfully']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context([
                'request' => $request->all(),
                'message' => $message
            ]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getQRCode(Request $request)
    {

        try {
            $partner = $request->partner;
            $data    = [
                'account_type' => $partner->qr_code_account_type ? config('partner.qr_code.account_types')[$partner->qr_code_account_type] : null,
                'image'        => $partner->qr_code_image ?: null
            ];
            return api_response($request, null, 200, ['data' => $data]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getSliderDetailsAndAccountTypes(Request $request)
    {
        try {
            $account_types     = [];
            $all_account_types = config('partner.qr_code.account_types');
            foreach ($all_account_types as $key => $type) {
                array_push($account_types, $type);
            }
            $data = [
                'description'   => config('partner.qr_code.description'),
                'slider_image'  => config('partner.qr_code.slider_image'),
                'account_types' => $account_types
            ];
            return api_response($request, null, 200, ['data' => $data]);

        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function dashboardByToken(Request $request)
    {
        try {
            $this->validate($request, [
                'access_token' => 'required',
            ]);
            $access_token = $request->access_token;
            $partner      = Redis::get($access_token);
            if (is_null($partner) || empty($partner))
                return api_response($request, null, 400, ['message' => 'Invalid token']);
            $partner = json_decode($partner);
            /** @var Partner $partner */
            if (is_null($partner) || empty($partner))
                return api_response($request, null, 400, ['message' => 'Partner not found']);
            Redis::del($access_token);
            $manager_resource = Resource::where('remember_token', $partner->remember_token)->first();
            if (empty($manager_resource))
                return api_response($request, null, 400, ['message' => 'Invalid token']);
            $partner = Partner::find((int)$partner->partner_id);
            $data    = (new PartnerRepository($partner))->getProfile($manager_resource);
            return api_response($request, null, 200, ['data' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry  = app('sentry');
            $sentry->user_context([
                'request' => $request->all(),
                'message' => $message
            ]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function updateAddress(Request $request, $partner, Updater $updater)
    {
        try {
            $this->validate($request, ['address' => 'required'], ['required' => 'ঠিকানা আবশ্যক']);
            $partner = $request->partner;
            $updater->setPartner($partner)->setAddress($request->address)->update();
            return api_response($request, null, 200, ['message' => 'Address Updated Successfully']);
        } catch (ValidationException $e) {
            app('sentry')->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return response()->json(['code' => 400, 'message' => $message], 400);
        } catch (ModelNotFoundException $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 404, 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleSmsActivation(Request $request, $partner, Updater $updater)
    {
        try {
            /** @var Partner $partner */
            $partner = $request->partner;
            $isWebstoreSmsActive = !(int)$partner->is_webstore_sms_active;
            $updater->setPartner($partner)->setIsWebstoreSmsActive($isWebstoreSmsActive)->update();
            return api_response($request, null, 200, ['message' => 'SMS Settings Updated Successfully']);
        } catch (ValidationException $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 400, 'message' => $e->getMessage()], 400);
        } catch (ModelNotFoundException $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 404, 'message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }
}

