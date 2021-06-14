<?php namespace Sheba\Partner;


use App\Models\Partner;
use App\Models\PartnerResource;
use App\Models\ReviewQuestionAnswer;
use App\Repositories\ReviewRepository;
use App\Sheba\Partner\Delivery\Methods;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\PartnerDeliveryInformation\Model as PartnerDeliveryInformation;
use Sheba\Dal\PartnerWebstoreBanner\Model as PartnerWebstoreBanner;
use Sheba\Jobs\JobStatuses;

class PartnerDetails
{
    const COMPLIMENT_QUESTION_ID = 2;

    /** @var Partner */
    private $partner;
    /** @var int */
    private $locationId;
    private $days;
    /** @var ReviewRepository  */
    private $reviewRepository;

    private $workingInfo = [];

    public function __construct(ReviewRepository $review_repo)
    {
        $this->reviewRepository = $review_repo;
        $this->days = constants('WEEK_DAYS');
    }

    /**
     * @param $identifier
     * @return $this
     * @throws \Exception
     */
    public function setPartner($identifier)
    {
        if (is_numeric($identifier)) {
            // $this->partner = Partner::find($identifier);
        } else {
            $this->partner = Partner::where('sub_domain', $identifier)->first();
        }
        if (!$this->partner) throw new \Exception("Invalid Partner");

        return $this;
    }

    public function setLocationId($location)
    {
        $this->locationId = $location;
        return $this;
    }

    public function get()
    {
        // $this->loadPartnerRelations();
        $partner      = $this->partner;
        $info         = collect($partner)->only([
            'id',
            'name',
            'sub_domain',
            'description',
            'email',
            'status',
            'logo',
            'address',
            'delivery_charge',
            'is_webstore_published'
        ]);
        $info->put('mobile', $partner->getContactNumber());
        $info->put('banner', $this->getWebStoreBanner());
        $info->put('delivery_method', $this->getDeliveryMethod());
        // $this->calculateWorkingDaysInfo();
        // $info->put('working_days', $this->workingInfo);
        // $info->put('is_available', $this->isOpenToday() ? 1 : 0);
        // $info->put('total_locations', $partner->locations->count());
        // $info->put('total_services', $partner->services->count());
        // $info->put('resources', $this->getResources());
        // $info->put('reviews', $this->getReviews());
        // $info->put('categories', $this->getCategories());
        // $info->put('compliments', $this->getCompliments()->values());
        // $info->put('total_resources', $partner->resources()->verified()->selectRaw('count(distinct resource_id) as total_resources')->first()->total_resources);
        // $info->put('total_jobs', $partner->jobs->count());
        // $info->put('total_rating', $partner->reviews->count());
        // $info->put('avg_rating', round($this->reviewRepository->getAvgRating($partner->reviews), 1));
        // $info->put('group_rating', $this->getGroupedRating());
        // $info->put('master_category_names', $this->partner->servingMasterCategories());
        // $info->put('badge', $this->partner->resolveBadge());
        // $info->put('geo_informations', $this->getGeoInfo());
        return $info;
    }

    private function getDeliveryMethod()
    {
        $partnerDeliveryInformation =  PartnerDeliveryInformation::where('partner_id', $this->partner->id)->first();
        return !empty($partnerDeliveryInformation) ? $partnerDeliveryInformation->delivery_vendor : Methods::OWN_DELIVERY;
    }

    private function loadPartnerRelations()
    {
        $this->partner->load([
            'resources',
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
    }

    private function getWebStoreBanner()
    {
        /** @var PartnerWebstoreBanner $web_store_banner */
        $web_store_banner = $this->partner->webstoreBanner;

        if(!$web_store_banner) return null;

        return [
            'image_link' => $web_store_banner->banner->image_link,
            'small_image_link' => $web_store_banner->banner->small_image_link,
            'title'  => $web_store_banner->title,
            'description' => $web_store_banner->description,
            'is_published' => $web_store_banner->is_published
        ];
    }

    private function getResources()
    {
        $resources = PartnerResource::join('resources', 'resources.id', '=', 'partner_resource.resource_id')
            ->join('profiles', 'resources.profile_id', '=', 'profiles.id')
            ->join('reviews', 'reviews.resource_id', '=', 'resources.id')
            ->where('reviews.partner_id', $this->partner->id)
            ->where('partner_resource.partner_id', $this->partner->id)
            ->where('resources.is_verified', 1)
            ->groupBy('partner_resource.id')
            ->selectRaw('distinct(resources.id), profiles.name, profiles.mobile, profiles.pro_pic,  avg(reviews.rating) as avg_rating, count(rating) as total_rating, (select count(jobs.id) from jobs where jobs.status = "Served" and jobs.resource_id = resources.id) as served_jobs')
            ->orderBy(DB::raw('avg(reviews.rating)'), 'desc')
            ->take(5)
            ->get();

        foreach ($resources as $resource) {
            $resource['avg_rating'] = (float)round($resource->avg_rating, 2);
        }
        return $resources;
    }

    private function getReviews()
    {
        $partner_review = $this->partner->reviews()->pluck('id')->toArray();
        $partner_review = ReviewQuestionAnswer::where('review_type', 'App\Models\Review')
            ->whereIn('review_id', $partner_review)
            ->where('rate_answer_text', '<>', '')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->pluck('rate_answer_text', 'review_id')
            ->toArray();

        $reviews = [];

        $job_with_review = $this->partner->jobs->where('status', JobStatuses::SERVED)->filter(function ($job) {
            return $job->resource_id != null && $job->review != null;
        });
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

        return $reviews;
    }

    private function getCategories()
    {
        return $this->partner->categories->each(function ($category) {
            $category->service_count = $category->services()->published()->count();
            if ($this->locationId) {
                if (in_array($this->locationId, $category->locations->pluck('id')->toArray())) {
                    $category->available = true;
                } else {
                    $category->available = false;
                }
            }
            removeRelationsAndFields($category);
        });
    }

    private function calculateWorkingDaysInfo()
    {
        foreach ($this->days as $day) {
            $current_day = $this->partner->workingHours->filter(function ($working_day) use ($day) {
                return $day === $working_day->day;
            })->first();
            if ($current_day) {
                array_push($this->workingInfo, array(
                    'day'       => $current_day->day,
                    'hour'      => (Carbon::parse($current_day->start_time))->format('g:i A') . '-' . (Carbon::parse($current_day->end_time))->format('g:i A'),
                    'is_today'  => $current_day->day === $this->days[Carbon::now()->dayOfWeek],
                    'is_closed' => false
                ));
            } else {
                array_push($this->workingInfo, array(
                    'day'       => $day,
                    'hour'      => null,
                    'is_today'  => $day === $this->days[Carbon::now()->dayOfWeek],
                    'is_closed' => true
                ));
            }
        }
    }

    private function isOpenToday()
    {
        return in_array(date('l'), collect($this->workingInfo)->pluck('day')->toArray());
    }

    private function getCompliments()
    {
        return $this->partner->reviews->pluck('rates')->filter(function ($rate) {
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
    }

    private function getGroupedRating()
    {
        $group_rating = $this->partner->reviews->groupBy('rating')->map(function ($rate) {
            return $rate->count();
        });
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($group_rating[$i]))
                $group_rating[$i] = 0;
        }

        return $group_rating;
    }

    private function getGeoInfo()
    {
        $geo_information          = $this->partner->geo_informations;
        $geo_information = json_decode($geo_information);
        if ($geo_information) {
            $geo_information = [
                'lat'    => (float)$geo_information->lat,
                'lng'    => (float)$geo_information->lng,
                'radius' => (float)$geo_information->radius,
            ];
        }
        return $geo_information;
    }
}
