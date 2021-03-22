<?php namespace App\Sheba\Checkout;

use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Affiliate;
use Sheba\Dal\Category\Category;
use App\Models\Customer;
use App\Models\Event;
use App\Models\HyperLocal;
use App\Models\Partner;
use App\Models\PartnerServiceDiscount;
use App\Models\PartnerServiceSurcharge;
use Sheba\Checkout\PriceBreakdownCalculators\PartnerPricingBreakdownCalculator;
use App\Repositories\PartnerServiceRepository;
use App\Sheba\Partner\PartnerAvailable;
use Carbon\Carbon;
use DB;
use Dingo\Api\Routing\Helpers;
use Sheba\Checkout\Partners\PartnerUnavailabilityReasons;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Checkout\PartnerSort;
use Sheba\ModificationFields;
use Sheba\Partner\ImpressionManager;
use Sheba\RequestIdentification;
use function request;

class PartnerList
{
    use Helpers, DispatchesJobs;

    /** @var Partner[] */
    public $partners;
    /** @var Partner */
    protected $partner;
    public $hasPartners = false;
    public $selected_services;
    public $location;
    protected $hyperLocation;
    protected $date;
    protected $time;
    protected $lat;
    protected $lng;
    protected $partnerServiceRepository;
    protected $skipAvailability;
    protected $selectedServiceIds;
    protected $notFoundValues;
    protected $isNotLite;
    protected $badgeResolver;
    /** @var PartnerListRequest */
    protected $partnerListRequest;
    /** @var PartnerPricingBreakdownCalculator */
    protected $priceBreakdownCalculator;
    /** @var ImpressionManager */
    protected $impressions;

    use ModificationFields;

    public function __construct()
    {
        $this->partnerServiceRepository = app(PartnerServiceRepository::class);
        $this->notFoundValues = [
            'service' => [],
            'location' => [],
            'credit' => [],
            'order_limit' => [],
            'options' => [],
            'handyman' => [],
            'availability' => []
        ];
        $this->priceBreakdownCalculator = app(PartnerPricingBreakdownCalculator::class);
        $this->impressions = app(ImpressionManager::class);
    }

    public function setPartnerListRequest(PartnerListRequest $partner_list_request)
    {
        $this->partnerListRequest = $partner_list_request;
        $this->priceBreakdownCalculator->setPartnerListRequest($this->partnerListRequest);
        $this->impressions->setPartnerListRequest($this->partnerListRequest);
        return $this;
    }

    /**
     * @param null $partner_id
     * @throws HyperLocationNotFoundException
     */
    public function find($partner_id = null)
    {
        $this->setPartner($partner_id);
        if ($this->partnerListRequest->lat && $this->partnerListRequest->lng) {
            $this->partners = $this->findPartnersByServiceAndGeo();
        } else {
            $this->partners = $this->findPartnersByServiceAndLocation();
        }
        if ($this->isNotLite) {
            $this->filterByCreditLimit();
        }
        if (!$this->partnerListRequest->isFromPartnerPortals()) {
            $this->filterByDailyOrderLimit();
        }
        $this->partners->load(['services' => function ($q) {
            $q->whereIn('service_id', $this->partnerListRequest->selectedServiceIds);
        }, 'categories' => function ($q) {
            $q->where('categories.id', $this->partnerListRequest->selectedCategory->id);
        }]);
        $this->filterByOption();
        $this->partners = $this->partners->filter(function ($partner) {
            return $this->hasResourcesForTheCategory($partner);
        });
        if (!$this->partnerListRequest->skipAvailabilityCheck) $this->addAvailability();
        elseif ($this->partners->count() > 1) $this->rejectShebaHelpDesk();
        $this->notFoundValues['handyman'] = $this->getPartnerIds();
        $this->calculateHasPartner();
    }

    protected function setPartner($partner_id)
    {
        if ($partner_id) $this->partner = Partner::find((int)$partner_id);
        $this->isNotLite = isset($this->partner) ? !$this->partner->isLite() : true;
    }

    protected function findPartnersByServiceAndLocation()
    {
        $this->partners = $this->findPartnersByService();
        $this->notFoundValues['service'] = $this->getPartnerIds();
        $this->partners->load('locations');
        $this->partners = $this->filterPartnerByLocation();
        $this->notFoundValues['location'] = $this->getPartnerIds();
        return $this->partners;
    }

    protected function findPartnersByService()
    {
        $category_ids = [$this->partnerListRequest->selectedCategory->id];
        $isNotLite = $this->isNotLite;
        $query = Partner::WhereHas('categories', function ($q) use ($category_ids, $isNotLite) {
            $q->whereIn('categories.id', $category_ids);
            if ($isNotLite) {
                $q->where('category_partner.is_verified', 1);
            }
            if ($this->partnerListRequest->homeDelivery) $q->where('category_partner.is_home_delivery_applied', $this->partnerListRequest->homeDelivery);
            if ($this->partnerListRequest->onPremise) $q->where('category_partner.is_partner_premise_applied', $this->partnerListRequest->onPremise);
            if (!$this->partnerListRequest->homeDelivery && !$this->partnerListRequest->onPremise) $q->where('category_partner.is_home_delivery_applied', 1);
        })->whereHas('services', function ($query) use ($isNotLite) {
            $query->whereHas('category', function ($q) {
                $q->publishedForAny();
            })->select(DB::raw('count(*) as c'))->whereIn('services.id', $this->partnerListRequest->selectedServiceIds)->where('partner_service.is_published', 1)
                ->publishedForAll()
                ->groupBy('partner_id')->havingRaw('c=' . count($this->partnerListRequest->selectedServiceIds));
            if ($isNotLite) {
                $query->where('partner_service.is_verified', 1);
            }
            if ($this->partnerListRequest->isWeeklySubscription()) {
                $query->where('partner_service.is_weekly_subscription_enable', 1);
            }
            if ($this->partnerListRequest->isMonthlySubscription()) {
                $query->where('partner_service.is_monthly_subscription_enable', 1);
            }
        })->whereDoesntHave('leaves', function ($q) {
            $q->where('end', null)->orWhere([['start', '<=', Carbon::now()], ['end', '>=', Carbon::now()->addDays(7)]]);
        })->with(['handymanResources' => function ($q) {
            $q->selectRaw('count(distinct resources.id) as total_experts, partner_id')
                ->join('category_partner_resource', 'category_partner_resource.partner_resource_id', '=', 'partner_resource.id')
                ->where('category_partner_resource.category_id', $this->partnerListRequest->selectedCategory->id)->groupBy('partner_id');
            if ($this->isNotLite) {
                $q->verified();
            }
        }])->select('partners.id', 'partners.current_impression', 'partners.geo_informations', 'partners.address', 'partners.name',
            'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet', 'partners.package_id', 'partners.badge',
            'partners.order_limit');

        if ($isNotLite) {
            $query->whereNotIn('package_id', config('sheba.marketplace_not_accessible_packages_id'))->verified();
        }

        if ($this->partner) {
            $query = $query->where('partners.id', $this->partner->id);
        }

        return $query->get();
    }

    private function hasResourcesForTheCategory($partner)
    {
        $handyman_resources = $partner->handymanResources->first();
        return $handyman_resources && (int)$handyman_resources->total_experts > 0 ? 1 : 0;
    }

    /**
     * @return mixed
     * @throws HyperLocationNotFoundException
     */
    protected function findPartnersByServiceAndGeo()
    {
        $hyper_local = HyperLocal::insidePolygon($this->partnerListRequest->lat, $this->partnerListRequest->lng)->with('location')->first();
        if (!$hyper_local) {
            $this->saveNotFoundEvent(1);
            $lat = $this->partnerListRequest->lat;
            $lng = $this->partnerListRequest->lng;
            throw new HyperLocationNotFoundException("lat : $lat, lng: $lng");
        }
        $this->partnerListRequest->setLocation($hyper_local->location_id);
        $this->partners = $this->findPartnersByService()->reject(function ($partner) {
            return $partner->geo_informations == null;
        });
        $this->notFoundValues['service'] = $this->getPartnerIds();
        if ($this->partners->count() == 0) return $this->partners;
        $this->filterPartnerByRadius();
        return $this->partners;
    }

    protected function filterByOption()
    {
        foreach ($this->partnerListRequest->selectedServices as $selected_service) {
            if ($selected_service->serviceModel->isOptions()) {
                $this->partners = $this->partners->filter(function ($partner, $key) use ($selected_service) {
                    $service = $partner->services->where('id', $selected_service->id)->first();
                    return $service->pivot->prices && $this->partnerServiceRepository->hasThisOption($service->pivot->prices, implode(',', $selected_service->option));
                });
            }
        }
        $this->notFoundValues['options'] = $this->getPartnerIds();
    }

    private function filterByCreditLimit()
    {
        $this->partners->load(['walletSetting' => function ($q) {
            $q->select('id', 'partner_id', 'min_wallet_threshold');
        }]);
        $this->partners = $this->partners->filter(function ($partner, $key) {
            /** @var Partner $partner */
            return $partner->hasAppropriateCreditLimit();
        });
        $this->notFoundValues['credit'] = $this->getPartnerIds();
    }

    private function filterByDailyOrderLimit()
    {
        $this->partners->load(['todayOrders' => function ($q) {
            $q->select('id', 'partner_id');
        }]);

        $this->partners = $this->partners->filter(function ($partner, $key) {
            /** @var Partner $partner */
            if (is_null($partner->order_limit)) return true;
            return $partner->todayOrders->count() < $partner->order_limit;
        });
        $this->notFoundValues['order_limit'] = $this->getPartnerIds();
    }

    protected function addAvailability()
    {
        $this->partners->load(['workingHours', 'leaves']);
        $this->partners->each(function ($partner) {
            if (!$this->isWithinPreparationTime($partner)) {
                $partner['is_available'] = 0;
                $partner['unavailability_reason'] = PartnerUnavailabilityReasons::PREPARATION_TIME;
                return;
            }

            $partner_available = new PartnerAvailable($partner);
            $partner_available->check($this->partnerListRequest->scheduleDate, $this->partnerListRequest->scheduleTime, $this->partnerListRequest->selectedCategory);

            if (!$partner_available->getAvailability()) {
                $partner['is_available'] = 0;
                $partner['unavailability_reason'] = $partner_available->getUnavailabilityReason();
                return;
            }

            $partner['is_available'] = 1;
        });

        if ($this->getAvailablePartners()->count() > 1) $this->rejectShebaHelpDesk();
    }

    private function isWithinPreparationTime($partner)
    {
        $category_preparation_time_minutes = $partner->categories->where('id', $this->partnerListRequest->selectedCategory->id)->first()->pivot->preparation_time_minutes;
        if ($category_preparation_time_minutes == 0) return 1;
        $start_time = Carbon::parse($this->partnerListRequest->scheduleDate[0] . ' ' . $this->partnerListRequest->scheduleStartTime);
        $end_time = Carbon::parse($this->partnerListRequest->scheduleDate[0] . ' ' . $this->partnerListRequest->scheduleEndTime);
        $preparation_time = Carbon::createFromTime(Carbon::now()->hour)->addMinute(61)->addMinute($category_preparation_time_minutes);
        return $preparation_time->lte($start_time) || $preparation_time->between($start_time, $end_time) ? 1 : 0;
    }

    /**
     * @throws InvalidDiscountType
     */
    public function addPricing()
    {
        $pivot = collect();
        foreach (($this->partners->pluck('services')) as $services) {
            foreach ($services as $service) {
                $pivot->push($service->pivot);
            }
        }
        $partner_service_group_by_partners = $pivot->groupBy('partner_id');
        $discounts = PartnerServiceDiscount::whereIn('partner_service_id', $pivot->pluck('id')->toArray())->running()->get();
        $schedule_date_time = Carbon::parse($this->partnerListRequest->scheduleDate[0] . ' ' . $this->partnerListRequest->scheduleStartTime);
        $surcharges = PartnerServiceSurcharge::whereIn('partner_service_id', $pivot->pluck('id')->toArray())->runningAt($schedule_date_time)->get();
        foreach ($this->partners as $partner) {
            $partner_service_ids = $partner_service_group_by_partners->get("$partner->id")->pluck('id')->toArray();

            $partner['discounts'] = $discounts->filter(function ($discount) use ($partner_service_ids) {
                return in_array($discount->partner_service_id, $partner_service_ids);
            });
            $partner['vat_percentage'] = $this->partnerListRequest->selectedCategory->is_vat_applicable ? config('sheba.category_vat_in_percentage') : 0;
            
            $partner['surcharges'] = $surcharges->filter(function ($surcharge) use ($partner_service_ids) {
                return in_array($surcharge->partner_service_id, $partner_service_ids);
            });

            $pricing = $this->priceBreakdownCalculator->setPartner($partner)->calculate();
            foreach ($pricing->toArray() as $key => $value) {
                $partner[$key] = $value;
            }
        }
    }

    public function addInfo()
    {
        $category_ids = Category::where('parent_id', $this->partnerListRequest->selectedCategory->parent_id)->pluck('id')->toArray();
        $relations = [
            'workingHours',
            'jobs' => function ($q) use ($category_ids) {
                $q->selectRaw("count(case when status in ('Served') and category_id in(" . implode($category_ids, ',') . ") then status end) as total_completed_orders")
                    ->groupBy('partner_id');
                if ($this->partnerListRequest->isFromAdminPortal()) {
                    $q->selectRaw("count(case when status in ('Accepted', 'Schedule Due', 'Process', 'Serve Due') then status end) as ongoing_jobs");
                }
            }, 'subscription' => function ($q) {
                $q->select('id', 'name', 'rules');
            }, 'reviews' => function ($q) use ($category_ids) {
                $q->selectRaw("count(DISTINCT(reviews.id)) as total_ratings")
                    ->selectRaw("count(DISTINCT(case when rating=5 then reviews.id end)) as total_five_star_ratings")
                    ->selectRaw("count(DISTINCT(case when rating=4 then reviews.id end)) as total_four_star_ratings")
                    ->selectRaw("count(DISTINCT(case when rating=3 then reviews.id end)) as total_three_star_ratings")
                    ->selectRaw("count(DISTINCT(case when rating=2 then reviews.id end)) as total_two_star_ratings")
                    ->selectRaw("count(DISTINCT(case when rating=1 then reviews.id end)) as total_one_star_ratings")
                    ->selectRaw("count(review_question_answer.id) as total_compliments")
                    ->selectRaw("reviews.partner_id")
                    ->leftJoin('review_question_answer', function ($q) {
                        $q->on('reviews.id', '=', 'review_question_answer.review_id');
                        $q->where('review_question_answer.review_type', '=', 'App\\Models\\Review');
                        $q->where('reviews.rating', '=', 5);
                    })->whereIn('reviews.category_id', $category_ids)
                    ->groupBy('reviews.partner_id');
            }
        ];

        if ($this->partnerListRequest->isFromAdminPortal()) {
            $relations['resources'] = function ($q) {
                $q->select('resources.id', 'profile_id')->with(['profile' => function ($q) {
                    $q->select('profiles.id', 'mobile');
                }]);
            };
        }

        $this->partners->load($relations);

        foreach ($this->partners as $partner) {
            $partner['total_jobs'] = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $partner['ongoing_jobs'] = $partner->jobs->first() && $partner->jobs->first()->ongoing_jobs ? $partner->jobs->first()->ongoing_jobs : 0;
            $partner['total_jobs_of_category'] = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $partner['total_completed_orders'] = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            if ($this->partnerListRequest->isFromAdminPortal()) $partner['contact_no'] = $partner->getContactNumber();
            $partner['badge'] = $partner->resolveBadge();
            $partner['subscription_type'] = $partner->resolveSubscriptionType();
            $partner['total_working_days'] = $partner->workingHours ? $partner->workingHours->count() : 0;
            $partner['rating'] = $this->calculateAvgRating($partner);
            $partner['total_ratings'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_ratings : 0;
            $partner['total_five_star_ratings'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_five_star_ratings : 0;
            $partner['total_compliments'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_five_star_ratings : 0;
            $partner['total_experts'] = $partner->handymanResources->first() ? (int)$partner->handymanResources->first()->total_experts : 0;
            $partner['agent_commission'] = $this->getAgentCommission($partner->discounted_price);

        }
    }

    private function getAgentCommission($discounted_price)
    {
        $commission = $discounted_price ? $discounted_price * (constants('AFFILIATE_REWARD')['SERVICE_REFER']['AGENT']['percentage']/100) : 0;
        if($commission > constants('AFFILIATE_REWARD')['SERVICE_REFER']['AGENT']['cap'])
            return constants('AFFILIATE_REWARD')['SERVICE_REFER']['AGENT']['cap'];
        return $commission;
    }

    private function calculateAvgRating(Partner $partner)
    {
        $reviews = $partner->reviews->first();
        if ($reviews) {
            return ($reviews->total_five_star_ratings * 5 + $reviews->total_four_star_ratings * 4 + $reviews->total_three_star_ratings * 3 +
                    $reviews->total_two_star_ratings * 2 + $reviews->total_one_star_ratings * 1) / $reviews->total_ratings;
        } else {
            return 0;
        }
    }

    public function sortByShebaPartnerPriority()
    {
        $this->partners = (new PartnerSort())->setPartners($this->partners)->getSortedPartners();
        if ($this->impressions->needsToDeduct()) $this->impressions->deduct($this->getPartnerIds());
    }

    public function sortByShebaSelectedCriteria()
    {
        $final = collect();
        $prices = $this->partners->unique('discounted_price')->pluck('discounted_price')->sort();
        $prices->each(function ($price) use ($final) {
            $this->partners->filter(function ($item) use ($price, $final) {
                return $item->discounted_price == $price;
            })->sortByDesc('rating')->each(function ($partner) use ($final) {
                $final->push($partner);
            });
        });
        $this->partners = $final->unique();
        $this->sortByAvailability();
    }

    private function sortByAvailability()
    {
        $unavailable_partners = $this->partners->filter(function ($partner, $key) {
            return $partner->is_available == 0;
        });
        $available_partners = $this->partners->filter(function ($partner, $key) {
            return $partner->is_available == 1;
        });
        $this->partners = $available_partners->merge($unavailable_partners);
    }


    protected function calculateHasPartner()
    {
        if (count($this->partners) > 0) {
            $this->hasPartners = true;
        } else {
            $this->saveNotFoundEvent();
        }
    }

    /**
     * @return mixed
     */
    private function getAvailablePartners()
    {
        return $this->partners->filter(function ($partner, $key) {
            return $partner->is_available == 1;
        });
    }

    private function rejectShebaHelpDesk()
    {
        $this->partners = $this->partners->reject(function ($partner) {
            return $partner->id == 1809;
        });
    }

    private function getPartnerIds()
    {
        return $this->partners->pluck('id')->toArray();
    }

    public function saveNotFoundEvent($is_out_of_service = 0)
    {
        $event = new Event();
        $event->tag = 'no_partner_found';
        $event->value = $this->getNotFoundValues($is_out_of_service);
        $event->fill((new RequestIdentification())->get());
        $user_id = $this->getUserId();
        if ($event->portal_name == 'bondhu-app') {
            $event->created_by_type = "App\\Models\\Affiliate";
            if ($user_id) {
                $event->created_by = $user_id;
                $event->created_by_name = "Affiliate - " . (Affiliate::find($user_id))->profile->name;
            }
        } elseif ($event->portal_name == 'customer-app' || $event->portal_name == 'customer-portal') {
            $event->created_by_type = "App\\Models\\Customer";
            if ($user_id) {
                $event->created_by = request()->header('User-Id');
                $event->created_by_name = "Customer - " . (Customer::find($user_id))->profile->name;
            }
        }
        $event->created_at = Carbon::now();
        $event->save();
    }

    private function getUserId()
    {
        return request()->hasHeader('User-Id') && request()->header('User-Id') != null ? (int)request()->header('User-Id') : null;
    }

    private function getNotFoundValues($is_out_of_service)
    {
        return json_encode(
            array_merge($this->notFoundValues, [
                'request' => [
                    'services' => $this->partnerListRequest->selectedServices->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'option' => $service->option,
                            'quantity' => $service->quantity
                        ];
                    }),
                    'lat' => $this->partnerListRequest->lat,
                    'lng' => $this->partnerListRequest->lng,
                    'location' => $this->partnerListRequest->location,
                    'date' => $this->partnerListRequest->scheduleDate[0],
                    'time' => $this->partnerListRequest->scheduleTime,
                    'is_out' => $is_out_of_service,
                    'origin' => request()->header('Origin')
                ]
            ])
        );
    }

    public function removeKeysFromPartner()
    {
        return $this->partners->each(function ($partner, $key) {
            if (isset($partner->rating)) $partner['rating'] = round($partner->rating, 2);
            array_forget($partner, 'wallet');
            array_forget($partner, 'package_id');
            array_forget($partner, 'geo_informations');
            array_forget($partner, 'discounts');
            array_forget($partner, 'surcharges');
            array_forget($partner, 'distance');
            array_forget($partner, 'order_limit');
            removeRelationsAndFields($partner);
        });
    }

    protected function filterPartnerByLocation()
    {
        return $this->partners->filter(function ($partner) {
            if (!$partner->geo_informations) return false;
            $locations = HyperLocal::insideCircle(json_decode($partner->geo_informations))
                ->with('location')
                ->get()
                ->pluck('location')
                ->filter();
            return $locations->where('id', $this->partnerListRequest->location)->count() > 0;
        });
    }

    protected function filterPartnerByRadius()
    {
        if ($this->partners->count() == 0) return;
        $current = new Coords($this->partnerListRequest->lat, $this->partnerListRequest->lng);
        $to = $this->partners->map(function ($partner) {
            $geo = json_decode($partner->geo_informations);
            return new Coords(floatval($geo->lat), floatval($geo->lng), $partner->id);
        })->toArray();
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = $distance->from([$current])->to($to)->sortedDistance()[0];
        $this->partners = $this->partners->filter(function ($partner) use ($results) {
            $partner['distance'] = $results[$partner->id];
            return $results[$partner->id] <= (double)json_decode($partner->geo_informations)->radius * 1000;
        });
        $this->notFoundValues['location'] = $this->getPartnerIds();
    }

    public function getNotShowingReason()
    {
        return $this->notFoundValues;
    }

    public function filterPartnerByAvailability()
    {
        $this->partners = $this->partners->filter(function ($partner) {
            return $partner->is_available == 1;
        });
        $this->notFoundValues['availability'] = $this->getPartnerIds();
    }

    public function removeShebaHelpDesk()
    {
        $this->partners = $this->partners->filter(function ($partner) {
            return $partner->id != config('sheba.sheba_help_desk_id');
        });
    }
}
