<?php namespace App\Sheba\Checkout;

use App\Jobs\DeductPartnerImpression;
use App\Models\Category;
use App\Models\Customer;
use App\Models\ImpressionDeduction;
use App\Models\Partner;
use App\Models\PartnerServiceDiscount;
use App\Models\Service;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ReviewRepository;
use App\Sheba\Partner\PartnerAvailable;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Sheba\Checkout\PartnerSort;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;

class PartnerList
{
    use DispatchesJobs;
    public $partners;
    public $hasPartners = false;
    public $selected_services;
    private $location;
    private $date;
    private $time;
    private $partnerServiceRepository;
    private $rentCarServicesId;
    private $skipAvailability;
    private $selectedCategory;
    private $rentCarCategoryIds;
    use ModificationFields;

    public function __construct($services, $date, $time, $location)
    {
        $this->location = (int)$location;
        $this->date = $date;
        $this->time = $time;
        $this->rentCarServicesId = array_map('intval', explode(',', env('RENT_CAR_SERVICE_IDS')));
        $this->rentCarCategoryIds = array_map('intval', explode(',', env('RENT_CAR_IDS')));
        $start = microtime(true);
        $this->selected_services = $this->getSelectedServices($services);
        $this->selectedCategory = Category::find($this->selected_services->first()->category_id);
        $time_elapsed_secs = microtime(true) - $start;
        //dump("add selected service info: " . $time_elapsed_secs * 1000);
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->skipAvailability = 0;
    }

    public function setAvailability($availability)
    {
        $this->skipAvailability = $availability;
        return $this;
    }

    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $selected_service = Service::where('id', $service->id)->publishedForAll()->first();
            $selected_service['option'] = $service->option;
            $selected_service['pick_up_location_id'] = isset($service->pick_up_location_id) ? $service->pick_up_location_id : null;
            $selected_service['pick_up_location_type'] = isset($service->pick_up_location_type) ? $service->pick_up_location_type : null;
            $selected_service['pick_up_address'] = isset($service->pick_up_address) ? $service->pick_up_address : null;
            if ($selected_service->category_id != (int)env('RENT_CAR_OUTSIDE_ID')) {
                $selected_service['destination_location_id'] = null;
                $selected_service['destination_location_type'] = null;
                $selected_service['destination_address'] = null;
                $selected_service['drop_off_date'] = null;
                $selected_service['drop_off_time'] = null;
            } else {
                $selected_service['destination_location_id'] = isset($service->destination_location_id) ? $service->destination_location_id : null;
                $selected_service['destination_location_type'] = isset($service->destination_location_type) ? $service->destination_location_type : null;
                $selected_service['destination_address'] = isset($service->destination_address) ? $service->destination_address : null;
                $selected_service['drop_off_date'] = isset($service->drop_off_date) ? $service->drop_off_date : null;
                $selected_service['drop_off_time'] = isset($service->drop_off_time) ? $service->drop_off_time : null;
            }
            if (in_array($selected_service->id, $this->rentCarServicesId)) {
                $model = "App\\Models\\" . $service->pick_up_location_type;
                $origin = $model::find($service->pick_up_location_id);
                $selected_service['pick_up_address_geo'] = json_encode(array('lat' => $origin->lat, 'lng' => $origin->lng));
                $model = "App\\Models\\" . $service->destination_location_type;
                $destination = $model::find($service->destination_location_id);
                $selected_service['destination_address_geo'] = json_encode(array('lat' => $destination->lat, 'lng' => $destination->lng));
                $data = $this->getDistanceCalculationResult($origin->lat . ',' . $origin->lng, $destination->lat . ',' . $destination->lng);
                $selected_service['quantity'] = (double)($data->rows[0]->elements[0]->distance->value) / 1000;
                $selected_service['estimated_distance'] = $selected_service['quantity'];
                $selected_service['estimated_time'] = (double)($data->rows[0]->elements[0]->duration->value) / 60;
            } else {
                $selected_service['quantity'] = $this->getSelectedServiceQuantity($service, (double)$selected_service->min_quantity);
            }
            $selected_services->push($selected_service);
        }
        return $selected_services;
    }

    private function getSelectedServiceQuantity($service, $min_quantity)
    {
        if (isset($service->quantity)) {
            $quantity = (double)$service->quantity;
            return $quantity >= $min_quantity ? $quantity : $min_quantity;
        } else {
            return $min_quantity;
        }
    }

    private function getDistanceCalculationResult($origin, $destination)
    {
        $client = new Client();
        try {
            $res = $client->request('GET', 'https://maps.googleapis.com/maps/api/distancematrix/json',
                [
                    'query' => ['origins' => $origin, 'destinations' => $destination, 'key' => env('GOOGLE_DISTANCEMATRIX_KEY'), 'mode' => 'driving']
                ]);
            return json_decode($res->getBody());
        } catch (RequestException $e) {
            return null;
        }
    }

    public function find($partner_id = null)
    {
        $start = microtime(true);
        $this->partners = $this->findPartnersByServiceAndLocation((int)$partner_id);
        $time_elapsed_secs = microtime(true) - $start;
        // dump("filter partner by service,location,category: " . $time_elapsed_secs * 1000);

        $start = microtime(true);
        $this->filterByCreditLimit();
        $time_elapsed_secs = microtime(true) - $start;
        //dump("filter partner by credit: " . $time_elapsed_secs * 1000);

        $start = microtime(true);
        $this->partners->load(['services' => function ($q) {
            $q->whereIn('service_id', $this->selected_services->pluck('id')->unique());
        }, 'categories' => function ($q) {
            $q->where('categories.id', $this->selectedCategory->id);
        }]);
        $time_elapsed_secs = microtime(true) - $start;
        //dump("load partner service and category: " . $time_elapsed_secs * 1000);
        $start = microtime(true);
        $selected_option_services = $this->selected_services->where('variable_type', 'Options');
        $this->filterByOption($selected_option_services);
        $time_elapsed_secs = microtime(true) - $start;
        //dump("filter partner by option: " . $time_elapsed_secs * 1000);

        $start = microtime(true);
        if (!$this->skipAvailability) $this->addAvailability();
        $time_elapsed_secs = microtime(true) - $start;
        //dump("filter partner by availability: " . $time_elapsed_secs * 1000);
        $this->calculateHasPartner();
    }

    private function findPartnersByServiceAndLocation($partner_id = null)
    {
        $has_premise = (int)request()->get('has_premise');
        $has_home_delivery = (int)request()->get('has_home_delivery');
        $service_ids = $this->selected_services->pluck('id')->unique();
        $category_ids = $this->selected_services->pluck('category_id')->unique()->toArray();
        $query = Partner::WhereHas('categories', function ($q) use ($category_ids, $has_premise, $has_home_delivery) {
            $q->whereIn('categories.id', $category_ids)->where('category_partner.is_verified', 1);
            if (request()->has('has_home_delivery')) $q->where('category_partner.is_home_delivery_applied', $has_home_delivery);
            if (request()->has('has_premise')) $q->where('category_partner.is_partner_premise_applied', $has_premise);
            if (!request()->has('has_home_delivery') && !request()->has('has_premise')) $q->where('category_partner.is_home_delivery_applied', 1);
        })->whereHas('locations', function ($query) {
            $query->where('locations.id', (int)$this->location);
        })->whereHas('services', function ($query) use ($service_ids) {
            $query->whereHas('category', function ($q) {
                $q->published();
            })->select(DB::raw('count(*) as c'))->whereIn('services.id', $service_ids)->where([['partner_service.is_published', 1], ['partner_service.is_verified', 1]])->publishedForAll()
                ->groupBy('partner_id')->havingRaw('c=' . count($service_ids));
        })->published()->select('partners.id', 'partners.current_impression', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet', 'partners.package_id');
        if ($partner_id != null) {
            $query = $query->where('partners.id', $partner_id);
        }
        return $query->get();
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

    private function filterByOption($selected_option_services)
    {
        foreach ($selected_option_services as $selected_option_service) {
            $this->partners = $this->partners->filter(function ($partner, $key) use ($selected_option_service) {
                $service = $partner->services->where('id', $selected_option_service->id)->first();
                return $this->partnerServiceRepository->hasThisOption($service->pivot->prices, implode(',', $selected_option_service->option));
            });
        }
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
    }

    private function addAvailability()
    {
        $this->partners->load(['workingHours', 'leaves']);
        $this->partners->each(function ($partner) {
            $partner['is_available'] = $this->isWithinPreparationTime($partner) && (new PartnerAvailable($partner))->available($this->date, $this->time, $this->selectedCategory) ? 1 : 0;
        });
        $available_partners = $this->partners->where('is_available', 1);
        if ($available_partners->count() > 1) {
            $this->rejectShebaHelpDesk();
        }
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
        $this->partners->load(['handymanResources', 'workingHours', 'jobs' => function ($q) use ($category_ids) {
            $q->selectRaw("count(case when status in ('Accepted', 'Served', 'Process', 'Schedule Due', 'Serve Due') then status end) as total_jobs")
                ->selectRaw("count(case when status in ('Accepted', 'Schedule Due', 'Process', 'Serve Due') then status end) as ongoing_jobs")
                ->selectRaw("count(case when status in ('Served') and category_id=" . $this->selectedCategory->id . " then status end) as total_completed_orders")
                ->selectRaw("count(case when category_id in(" . $category_ids . ") and status in ('Accepted', 'Served', 'Process', 'Schedule Due', 'Serve Due') then category_id end) as total_jobs_of_category")
                ->groupBy('partner_id');
        }, 'subscription' => function ($q) {
            $q->select('id', 'name');
        }, 'resources' => function ($q) {
            $q->select('resources.id', 'profile_id')->with(['profile' => function ($q) {
                $q->select('profiles.id', 'mobile');
            }]);
        }]);
        foreach ($this->partners as $partner) {
            $partner['total_jobs'] = $partner->jobs->first() ? $partner->jobs->first()->total_jobs : 0;
            $partner['ongoing_jobs'] = $partner->jobs->first() ? $partner->jobs->first()->ongoing_jobs : 0;
            $partner['total_jobs_of_category'] = $partner->jobs->first() ? $partner->jobs->first()->total_jobs_of_category : 0;

            $partner['contact_no'] = $this->getContactNumber($partner);
            $partner['subscription_type'] = $partner->subscription ? $partner->subscription->name : null;
            $partner['total_experts'] = $partner->handymanResources->first() ? $partner->handymanResources->first()->total_experts : 0;
            $partner['total_working_days'] = $partner->workingHours ? $partner->workingHours->count() : 0;
            $partner['total_completed_orders'] = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $partner['address'] = $partner->address;
        }
    }

    public function calculateAverageRating()
    {
        $this->partners->load(['reviews' => function ($q) {
            $q->select('reviews.id', 'rating', 'category_id', 'partner_id')->with(['rates' => function ($q) {
                $q->select('id', 'review_id');
            }]);
        }]);
        foreach ($this->partners as $partner) {
            $partner['rating'] = (new ReviewRepository())->getAvgRating($partner->reviews);
        }
    }

    public function calculateTotalRatings()
    {
        foreach ($this->partners as $partner) {
            $partner['total_ratings'] = count($partner->reviews);
            $five_star_reviews = $partner->reviews->filter(function ($review) {
                return $review->rating == 5;
            });
            $partner['total_compliments'] = $five_star_reviews->reduce(function ($count, $review) {
                return $count + $review->rates->count();
            }, 0);
            $partner['total_five_star_ratings'] = $five_star_reviews->count();
        }
    }

    public function sortByShebaPartnerPriority()
    {
        $this->partners->load(['reviews' => function ($q) {
            $q->selectRaw("avg(rating) as avg_rating")
                ->selectRaw("count(reviews.id) as total_rating")
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
            $partner['avg_rating'] = $partner->reviews->first() ? (double)$partner->reviews->first()->avg_rating : 0;
            $partner['total_rating'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_rating : 0;
            $partner['total_five_star_ratings'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_five_star_ratings : 0;
            $partner['total_compliments'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_compliments : 0;
            $partner['total_experts'] = $partner->handymanResources->first() ? (int)$partner->handymanResources->first()->total_experts : 0;
        }
        $this->partners = (new PartnerSort($this->partners))->get();
        $this->deductImpression();

    }

    private function deductImpression()
    {
        if (request()->has('screen') && request()->get('screen') == 'partner_list'
            && in_array(request()->header('Portal-Name'), ['customer-portal', 'customer-app', 'manager-app'])) {
            $partners = $this->partners->pluck('id')->toArray();
            $impression_deduction = new ImpressionDeduction();
            $impression_deduction->category_id = $this->selectedCategory->id;
            $impression_deduction->location_id = $this->location;
            $serviceArray = [];
            foreach (json_decode(request()->services) as $service) {
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
            if ($service->isOptions()) {
                $price = $this->partnerServiceRepository->getPriceOfOptionsService($service->pivot->prices, $selected_service->option);
                $min_price = empty($service->pivot->min_prices) ? 0 : $this->partnerServiceRepository->getMinimumPriceOfOptionsService($service->pivot->min_prices, $selected_service->option);
            } else {
                $price = (double)$service->pivot->prices;
                $min_price = (double)$service->pivot->min_prices;
            }

            if ($selected_service->is_surcharges_applicable) {
                $schedule_date_time = Carbon::parse($this->date . ' ' . explode('-', $this->time)[0]);
                $surcharge_amount = $this->partnerServiceRepository->getSurchargePriceOfService($service->pivot, $schedule_date_time);
                $price = $price + ($price * $surcharge_amount / 100);
                $service['is_surcharge_applied'] = ($surcharge_amount > 0) ? 1 : 0;
            }

            $discount = new Discount($price, $selected_service->quantity, $min_price);
            $discount->calculateServiceDiscount(PartnerServiceDiscount::where('partner_service_id', $service->pivot->id)->running()->first());
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
            $service['name'] = $selected_service->name;
            $service['option'] = $selected_service->option;
            $service['quantity'] = $selected_service->quantity;
            $service['unit'] = $selected_service->unit;
            list($option, $variables) = $this->getVariableOptionOfService($selected_service, $selected_service->option);
            $service['questions'] = json_decode($variables);
            array_push($services, $service);
        }
        array_add($partner, 'breakdown', $services);
        $total_service_price['discount'] = (int)$total_service_price['discount'];
        $delivery_charge = (double)$category_pivot->delivery_charge;
        $total_service_price['discounted_price'] += $delivery_charge;
        $total_service_price['original_price'] += $delivery_charge;
        $total_service_price['delivery_charge'] = $delivery_charge;
        $total_service_price['has_home_delivery'] = $delivery_charge > 0 ? 1 : 0;
        $total_service_price['has_premise_available'] = (int)$category_pivot->is_partner_premise_applied ? 1 : 0;
        return $total_service_price;
    }

    private function calculateHasPartner()
    {
        if (count($this->partners) > 0) {
            $this->hasPartners = true;
        }
    }

    private function getVariableOptionOfService(Service $service, Array $option)
    {
        if ($service->variable_type == 'Options') {
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

    private function rejectShebaHelpDesk()
    {
        try {
            $this->partners = $this->partners->reject(function ($partner) {
                return $partner->id == 1809;
            });
        } catch (\Throwable $e) {
        }
    }
}