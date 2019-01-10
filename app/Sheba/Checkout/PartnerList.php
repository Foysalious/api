<?php

namespace App\Sheba\Checkout;

use App\Exceptions\HyperLocationNotFoundException;
use App\Jobs\DeductPartnerImpression;
use App\Models\Affiliate;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Event;
use App\Models\HyperLocal;
use App\Models\ImpressionDeduction;
use App\Models\Partner;
use App\Models\Service;
use App\Repositories\PartnerServiceRepository;
use App\Sheba\Partner\PartnerAvailable;
use Carbon\Carbon;
use DB;
use Dingo\Api\Routing\Helpers;
use Sheba\Checkout\Services\RentACarServiceObject;
use Sheba\Checkout\Services\ServiceObject;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Checkout\PartnerSort;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class PartnerList
{
    use Helpers;
    use DispatchesJobs;
    public $partners;
    public $hasPartners = false;
    public $selected_services;
    public $location;
    private $hyperLocation;
    private $date;
    private $time;
    private $lat;
    private $lng;
    private $partnerServiceRepository;
    private $rentCarServicesId;
    private $skipAvailability;
    /** @var Category */
    public $selectedCategory;
    private $rentCarCategoryIds;
    private $selectedServiceIds;
    private $notFoundValues;
    use ModificationFields;

    public function __construct($services, $date, $time, $location = null)
    {
        $this->location = $location;
        $this->date = $date;
        $this->time = $time;
        $this->rentCarServicesId = array_map('intval', explode(',', env('RENT_CAR_SERVICE_IDS')));
        $this->rentCarCategoryIds = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        $start = microtime(true);
        $this->selectedCategory = Service::find((int)$services[0]->id)->category;
        $this->selected_services = $this->getSelectedServices($services);
        $this->selectedServiceIds = $this->getServiceIds();
        $time_elapsed_secs = microtime(true) - $start;
        //dump("add selected service info: " . $time_elapsed_secs * 1000);
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->skipAvailability = 0;
        $this->checkForRentACarPickUpGeo();
        $this->notFoundValues = [
            'service' => [],
            'location' => [],
            'credit' => [],
            'options' => [],
            'handyman' => []
        ];
    }

    public function setGeo($lat, $lng)
    {
        $this->lat = (double)$lat;
        $this->lng = (double)$lng;
        return $this;
    }

    public function setAvailability($availability)
    {
        $this->skipAvailability = $availability;
        return $this;
    }

    private function checkForRentACarPickUpGeo()
    {
        if ($this->selectedCategory->isRentCar()) {
            $service = $this->selected_services->first();
            if ($service->pickUpLocationLat && $service->pickUpLocationLng) {
                $this->setGeo($service->pickUpLocationLat, $service->pickUpLocationLng);
                $this->location = null;
            }
        }
    }

    /**
     * @param $services
     * @return ServiceObject[]
     */
    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $service = $this->selectedCategory->isRentCar() ? new RentACarServiceObject($service) : new ServiceObject($service);
            $selected_services->push($service);
        }
        return $selected_services;
    }

    private function getCalculatedLocation(ServiceObject $service)
    {
        if ($service instanceof RentACarServiceObject) {
            $location = $this->api->get('/v2/locations/current?lat=' . $service->pickUpLocationLat . '&lng=' . $service->pickUpLocationLng);
            return $location ? $location->id : null;
        } else {
            return $this->location;
        }
    }

    private function getServiceIds()
    {
        $service_ids = collect();
        foreach ($this->selected_services as $selected_service) {
            $service_ids->push($selected_service->id);
        }
        return $service_ids->unique()->toArray();
    }

    /**
     * @param null $partner_id
     * @throws HyperLocationNotFoundException
     */
    public function find($partner_id = null)
    {
        if ($this->location) {
            $this->location = $this->getCalculatedLocation($this->selected_services->first());
            $start = microtime(true);
            $this->partners = $this->findPartnersByServiceAndLocation($partner_id);
            $time_elapsed_secs = microtime(true) - $start;
            // dump("filter partner by service,location,category: " . $time_elapsed_secs * 1000);
        } else {
            $this->partners = $this->findPartnersByServiceAndGeo($partner_id);
        }
        $start = microtime(true);
        $this->filterByCreditLimit();
        $time_elapsed_secs = microtime(true) - $start;
        //dump("filter partner by credit: " . $time_elapsed_secs * 1000);

        $start = microtime(true);
        $this->partners->load(['services' => function ($q) {
            $q->whereIn('service_id', $this->selectedServiceIds);
        }, 'categories' => function ($q) {
            $q->where('categories.id', $this->selectedCategory->id);
        }]);
        $time_elapsed_secs = microtime(true) - $start;
        //dump("load partner service and category: " . $time_elapsed_secs * 1000);
        $start = microtime(true);
        $this->filterByOption();
        $time_elapsed_secs = microtime(true) - $start;
        //dump("filter partner by option: " . $time_elapsed_secs * 1000);
        $start = microtime(true);
        if (!$this->skipAvailability) $this->addAvailability();
        elseif ($this->partners->count() > 1) $this->rejectShebaHelpDesk();
        $this->partners = $this->partners->filter(function ($partner) {
            return $this->hasResourcesForTheCategory($partner);
        });
        $this->notFoundValues['handyman'] = $this->getPartnerIds();
        $time_elapsed_secs = microtime(true) - $start;
        //dump("filter partner by availability: " . $time_elapsed_secs * 1000);
        $this->calculateHasPartner();
    }


    private function findPartnersByServiceAndLocation($partner_id = null)
    {
        $this->partners = $this->findPartnersByService($partner_id);
        $this->partners->load('locations');
        return $this->partners->filter(function ($partner) {
            /** Do not delete this code, will be used for later, range will be fetched using hyper local. */
//            $is_partner_has_coverage = $partner->geo_informations && in_array($this->location, HyperLocal::insideCircle(json_decode($partner->geo_informations))->pluck('location_id')->toArray());
//            return $is_partner_has_coverage;]
            return $partner->locations->where('id', $this->location)->count() > 0;
            // $is_partner_has_coverage = $partner->geo_informations && in_array($this->location, HyperLocal::insideCircle(json_decode($partner->geo_informations))->pluck('location_id')->toArray());
            // return $is_partner_has_coverage;
        });
    }

    private function findPartnersByService($partner_id = null)
    {
        $has_premise = (int)request()->get('has_premise');
        $has_home_delivery = (int)request()->get('has_home_delivery');
        $category_ids = [$this->selectedCategory->id];
        $query = Partner::WhereHas('categories', function ($q) use ($category_ids, $has_premise, $has_home_delivery) {
            $q->whereIn('categories.id', $category_ids)->where('category_partner.is_verified', 1);
            if (request()->has('has_home_delivery')) $q->where('category_partner.is_home_delivery_applied', $has_home_delivery);
            if (request()->has('has_premise')) $q->where('category_partner.is_partner_premise_applied', $has_premise);
            if (!request()->has('has_home_delivery') && !request()->has('has_premise')) $q->where('category_partner.is_home_delivery_applied', 1);
        })->whereHas('services', function ($query) {
            $query->whereHas('category', function ($q) {
                $q->published();
            })->select(DB::raw('count(*) as c'))->whereIn('services.id', $this->selectedServiceIds)->where([['partner_service.is_published', 1], ['partner_service.is_verified', 1]])->publishedForAll()
                ->groupBy('partner_id')->havingRaw('c=' . count($this->selectedServiceIds));
        })->whereDoesntHave('leaves', function ($q) {
            $q->where('end', null)->orWhere([['start', '<=', Carbon::now()], ['end', '>=', Carbon::now()->addDays(7)]]);
        })->with(['handymanResources' => function ($q) {
            $q->verified();
        }])->published()
            ->select('partners.id', 'partners.current_impression', 'partners.geo_informations', 'partners.address', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet', 'partners.package_id');
        if ($partner_id != null) {
            $query = $query->where('partners.id', $partner_id);
        }
        return $query->get();
    }

    private function hasResourcesForTheCategory($partner)
    {
        $partner_resource_ids = [];
        $partner->handymanResources->map(function ($resource) use (&$partner_resource_ids) {
            $partner_resource_ids[$resource->pivot->id] = $resource;
        });
        $result = [];
        collect(DB::table('category_partner_resource')->select('partner_resource_id')->whereIn('partner_resource_id', array_keys($partner_resource_ids))
            ->where('category_id', $this->selectedCategory->id)->get())->pluck('partner_resource_id')->each(function ($partner_resource_id) use ($partner_resource_ids, &$result) {
            $result[] = $partner_resource_ids[$partner_resource_id];
        });
        return count($result) > 0 ? 1 : 0;
    }

    private function getContactNumber($partner)
    {
        if ($operation_resource = $partner->resources->where('pivot.resource_type', constants('RESOURCE_TYPES')['Operation'])->first()) {
            return $operation_resource->profile->mobile;
        } elseif ($admin_resource = $partner->resources->where('pivot.resource_type', constants('RESOURCE_TYPES')['Admin'])->first()) {
            return $admin_resource->profile->mobile;
        }
        return null;
    }


    /**
     * @param null $partner_id
     * @return mixed
     * @throws HyperLocationNotFoundException
     */
    private function findPartnersByServiceAndGeo($partner_id = null)
    {
        $hyper_local = HyperLocal::insidePolygon($this->lat, $this->lng)->with('location')->first();
        if (!$hyper_local) {
            $this->saveNotFoundEvent();
            throw new HyperLocationNotFoundException("lat : $this->lat, lng: $this->lng");
        }
        $this->location = $hyper_local->location->id;
        $this->partners = $this->findPartnersByService($partner_id)->reject(function ($partner) {
            return $partner->geo_informations == null;
        });
        $this->notFoundValues['service'] = $this->getPartnerIds();
        if ($this->partners->count() == 0) return $this->partners;
        $current = new Coords($this->lat, $this->lng);
        $to = $this->partners->map(function ($partner) {
            $geo = json_decode($partner->geo_informations);
            return new Coords(floatval($geo->lat), floatval($geo->lng), $partner->id);
        })->toArray();
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = $distance->from([$current])->to($to)->sortedDistance()[0];
        $this->partners = $this->partners->filter(function ($partner) use ($results) {
            return $results[$partner->id] <= (double)json_decode($partner->geo_informations)->radius * 1000;
        });
        $this->notFoundValues['location'] = $this->getPartnerIds();
        return $this->partners;
    }

    private function filterByOption()
    {
        foreach ($this->selected_services as $selected_service) {
            if ($selected_service->serviceModel->isOptions()) {
                $this->partners = $this->partners->filter(function ($partner, $key) use ($selected_service) {
                    $service = $partner->services->where('id', $selected_service->id)->first();
                    return $this->partnerServiceRepository->hasThisOption($service->pivot->prices, implode(',', $selected_service->option));
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

    private function addAvailability()
    {
        $this->partners->load(['workingHours', 'leaves']);
        $this->partners->each(function ($partner) {
            $partner['is_available'] = $this->isWithinPreparationTime($partner) && (new PartnerAvailable($partner))->available($this->date, $this->time, $this->selectedCategory) ? 1 : 0;
        });
        if ($this->getAvailablePartners()->count() > 1) $this->rejectShebaHelpDesk();
    }

    public function isWithinPreparationTime($partner)
    {
        $category_preparation_time_minutes = $partner->categories->where('id', $this->selectedCategory->id)->first()->pivot->preparation_time_minutes;
        if ($category_preparation_time_minutes == 0) return 1;
        $start_time = Carbon::parse($this->date . ' ' . explode('-', $this->time)[0]);
        $end_time = Carbon::parse($this->date . ' ' . explode('-', $this->time)[1]);
        $preparation_time = Carbon::createFromTime(Carbon::now()->hour)->addMinute(61)->addMinute($category_preparation_time_minutes);
        return $preparation_time->lte($start_time) || $preparation_time->between($start_time, $end_time) ? 1 : 0;
    }

    public function addPricing()
    {
        foreach ($this->partners as $partner) {
            $pricing = $this->calculateServicePricingAndBreakdownOfPartner($partner);
            foreach ($pricing as $key => $value) {
                $partner[$key] = $value;
            }
        }
    }

    public function addInfo()
    {
        $category_ids = (string)$this->selectedCategory->id;
        if (in_array($this->selectedCategory->id, $this->rentCarCategoryIds)) {
            $category_ids = $this->selectedCategory->id == (int)env('RENT_CAR_OUTSIDE_ID') ? $category_ids . ",40" : $category_ids . ",38";
        }
        $this->partners->load(['workingHours', 'jobs' => function ($q) use ($category_ids) {
            $q->selectRaw("count(case when status in ('Accepted', 'Served', 'Process', 'Schedule Due', 'Serve Due') then status end) as total_jobs")
                ->selectRaw("count(case when status in ('Accepted', 'Schedule Due', 'Process', 'Serve Due') then status end) as ongoing_jobs")
                ->selectRaw("count(case when status in ('Served') and category_id=" . $this->selectedCategory->id . " then status end) as total_completed_orders")
                ->selectRaw("count(case when category_id in(" . $category_ids . ") and status in ('Accepted', 'Served', 'Process', 'Schedule Due', 'Serve Due') then category_id end) as total_jobs_of_category")
                ->groupBy('partner_id');
        }, 'subscription' => function ($q) {
            $q->select('id', 'name', 'rules');
        }, 'resources' => function ($q) {
            $q->select('resources.id', 'profile_id')->with(['profile' => function ($q) {
                $q->select('profiles.id', 'mobile');
            }]);
        }, 'reviews' => function ($q) {
            $q->selectRaw("avg(rating) as avg_rating")
                ->selectRaw("count(reviews.id) as total_ratings")
                ->selectRaw("count(case when rating=5 then reviews.id end) as total_five_star_ratings")
                ->selectRaw("count(case when review_question_answer.review_type='App\\\Models\\\Review' and rating=5 then review_question_answer.id end) as total_compliments,reviews.partner_id")
                ->leftJoin('review_question_answer', 'reviews.id', '=', 'review_question_answer.review_id')
                ->where('category_id', $this->selectedCategory->id)
                ->groupBy('reviews.partner_id');
        }, 'handymanResources' => function ($q) {
            $q->selectRaw('count(distinct resources.id) as total_experts, partner_id')
                ->verified()->join('category_partner_resource', 'category_partner_resource.partner_resource_id', '=', 'partner_resource.id')
                ->where('category_partner_resource.category_id', $this->selectedCategory->id)->groupBy('partner_id');
        }]);
        foreach ($this->partners as $partner) {
            $partner['total_jobs'] = $partner->jobs->first() ? $partner->jobs->first()->total_jobs : 0;
            $partner['ongoing_jobs'] = $partner->jobs->first() ? $partner->jobs->first()->ongoing_jobs : 0;
            $partner['total_jobs_of_category'] = $partner->jobs->first() ? $partner->jobs->first()->total_jobs_of_category : 0;
            $partner['total_completed_orders'] = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $partner['contact_no'] = $this->getContactNumber($partner);
            $partner['subscription_type'] = $this->setBadgeName($partner->badge);
            $partner['total_working_days'] = $partner->workingHours ? $partner->workingHours->count() : 0;
            $partner['rating'] = $partner->reviews->first() ? (double)$partner->reviews->first()->avg_rating : 0;
            $partner['total_ratings'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_ratings : 0;
            $partner['total_five_star_ratings'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_five_star_ratings : 0;
            $partner['total_compliments'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_compliments : 0;
            $partner['total_experts'] = $partner->handymanResources->first() ? (int)$partner->handymanResources->first()->total_experts : 0;
        }
    }

    public function setBadgeName($badge)
    {
        if($badge === 'gold') return 'LSP';
        else if($badge === 'silver') return 'PSP';
        else return 'ESP';
    }

    public function sortByShebaPartnerPriority()
    {
        $this->partners = (new PartnerSort($this->partners))->get();
        $this->deductImpression();
    }

    private function deductImpression()
    {
        if (request()->has('screen') && request()->get('screen') == 'partner_list'
            && in_array(request()->header('Portal-Name'), ['customer-portal', 'customer-app', 'manager-app', 'manager-portal'])) {
            $partners = $this->getPartnerIds();
            $impression_deduction = new ImpressionDeduction();
            $impression_deduction->category_id = $this->selectedCategory->id;
            $impression_deduction->location_id = $this->location;
            $serviceArray = [];
            foreach ($this->selected_services as $service) {
                array_push($serviceArray, [
                    'id' => $service->id,
                    'quantity' => $service->quantity,
                    'option' => $service->option
                ]);
            }
            $impression_deduction->order_details = json_encode(['services' => $serviceArray]);
            $customer = request()->hasHeader('User-Id') && request()->header('User-Id') ? Customer::find((int)request()->header('User-Id')) : null;
            if ($customer) $impression_deduction->customer_id = $customer->id;
            $impression_deduction->portal_name = request()->header('Portal-Name');
            $impression_deduction->ip = request()->ip();
            $impression_deduction->user_agent = request()->header('User-Agent');
            $impression_deduction->created_at = Carbon::now();
            $impression_deduction->save();
            $impression_deduction->partners()->sync($partners);
            dispatch(new DeductPartnerImpression($partners));
        }
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
        $this->rejectShebaHelpDesk();
    }

    private function calculateServicePricingAndBreakdownOfPartner($partner)
    {
        $total_service_price = [
            'discount' => 0,
            'discounted_price' => 0,
            'original_price' => 0,
            'is_min_price_applied' => 0,
        ];
        $services = [];
        $category_pivot = $partner->categories->first()->pivot;
        foreach ($this->selected_services as $selected_service) {
            $service = $partner->services->where('id', $selected_service->id)->first();
            $schedule_date_time = Carbon::parse($this->date . ' ' . explode('-', $this->time)[0]);
            $discount = new Discount();
            $discount->setServiceObj($selected_service)->setServicePivot($service->pivot)->setScheduleDateTime($schedule_date_time)->initialize();
            $discount->calculateServiceDiscount();
            $service = [];
            $service['discount'] = $discount->discount;
            $service['cap'] = $discount->cap;
            $service['amount'] = $discount->amount;
            $service['is_percentage'] = $discount->isDiscountPercentage;
            $service['discounted_price'] = $discount->discounted_price;
            $service['original_price'] = $discount->original_price;
            $service['min_price'] = $discount->min_price;
            $service['unit_price'] = $discount->unit_price;
            $service['sheba_contribution'] = $discount->sheba_contribution;
            $service['partner_contribution'] = $discount->partner_contribution;
            $service['is_min_price_applied'] = $discount->original_price == $discount->min_price ? 1 : 0;
            if ($discount->original_price == $discount->min_price) {
                $total_service_price['is_min_price_applied'] = 1;
            }
            $total_service_price['discount'] += $service['discount'];
            $total_service_price['discounted_price'] += $service['discounted_price'];
            $total_service_price['original_price'] += $service['original_price'];
            $service['id'] = $selected_service->id;
            $service['name'] = $selected_service->serviceModel->name;
            $service['option'] = $selected_service->option;
            $service['quantity'] = $selected_service->quantity;
            $service['unit'] = $selected_service->serviceModel->unit;
            list($option, $variables) = $this->getVariableOptionOfService($selected_service->serviceModel, $selected_service->option);
            $service['questions'] = json_decode($variables);
            array_push($services, $service);
        }
        array_add($partner, 'breakdown', $services);
        $total_service_price['discount'] = (int)$total_service_price['discount'];
        $delivery_charge = (double)$category_pivot->delivery_charge;
        $total_service_price['discounted_price'] += $delivery_charge;
        $total_service_price['original_price'] += $delivery_charge;
        $total_service_price['delivery_charge'] = $delivery_charge;
        $total_service_price['has_home_delivery'] = (int)$category_pivot->is_home_delivery_applied ? 1 : 0;
        $total_service_price['has_premise_available'] = (int)$category_pivot->is_partner_premise_applied ? 1 : 0;
        return $total_service_price;
    }

    private function calculateHasPartner()
    {
        if (count($this->partners) > 0) {
            $this->hasPartners = true;
        } else {
            $this->saveNotFoundEvent();
        }
    }

    private function getVariableOptionOfService(Service $service, Array $option)
    {
        if ($service->isOptions()) {
            $variables = [];
            $options = implode(',', $option);
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
        }
        return array($option, $variables);
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

    public function saveNotFoundEvent()
    {
        $event = new Event();
        $event->tag = 'no_partner_found';
        $event->value = $this->getNotFoundValues();
        $event->fill((new RequestIdentification)->get());
        if ($event->portal_name == 'bondhu-app') {
            $event->created_by_type = "App\\Models\\Affiliate";
            if (\request()->hasHeader('User-Id')) {
                $event->created_by = \request()->header('User-Id');
                $event->created_by_name = "Affiliate - " . Affiliate::find((int)\request()->header('User-Id'))->profile->name;
            }
        } elseif ($event->portal_name == 'customer-app' || $event->portal_name == 'customer-portal') {
            $event->created_by_type = "App\\Models\\Customer";
            if (\request()->hasHeader('User-Id')) {
                $event->created_by = \request()->header('User-Id');
                $event->created_by_name = "Customer - " . Customer::find((int)\request()->header('User-Id'))->profile->name;
            }
        }
        $event->created_at = Carbon::now();
        $event->save();
    }

    public function getNotFoundValues()
    {
        return json_encode(
            array_merge($this->notFoundValues, [
                'request' => [
                    'services' => $this->selected_services->map(function ($service) {
                        return [
                            'id' => $service->id,
                            'option' => $service->option,
                            'quantity' => $service->quantity
                        ];
                    }),
                    'lat' => $this->lat,
                    'lng' => $this->lng,
                    'date' => $this->date,
                    'time' => $this->time,
                    'location' => $this->location,
                    'origin' => request()->header('Origin'),
                    'user-id' => \request()->header('User-Id')
                ]
            ])
        );
    }

}