<?php namespace Sheba\PartnerList;

use Sheba\Dal\Category\Category;
use App\Models\Partner;
use App\Sheba\Partner\PartnerAvailable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Sheba\Checkout\Partners\PartnerUnavailabilityReasons;
use Sheba\Checkout\PartnerSort;
use Sheba\Location\Coords;
use Sheba\Location\Distance\Distance;
use Sheba\Location\Distance\DistanceStrategy;
use Sheba\Location\Geo;
use Sheba\ServiceRequest\ServiceRequestObject;
use DB;

class PartnerListBuilder implements Builder
{
    protected $partnerQuery;
    private $scheduleDate;
    private $scheduleTime;
    /** @var  Collection */
    private $partners;
    /** @var ServiceRequestObject[] */
    private $serviceRequestObject;
    private $partnerIds = [];
    private $partnerIdsToIgnore = [];
    /** @var Geo */
    private $geo;
    /** @var array */
    private $categoryIdsOfMasterCategory = [];

    public function __construct()
    {
        $this->partnerQuery = Partner::query();
    }

    private function setCategoryIdsOfMasterCategory()
    {
        if (count($this->categoryIdsOfMasterCategory) > 0) return;
        $this->categoryIdsOfMasterCategory = Category::where('parent_id', $this->getCategory()->parent_id)->pluck('id')->toArray();
    }

    private function getCategoryIdsOfMasterCategory()
    {
        $this->setCategoryIdsOfMasterCategory();
        return $this->categoryIdsOfMasterCategory;
    }

    public function checkCategory()
    {
        $this->partnerQuery = $this->partnerQuery->WhereHas('categories', function ($q) {
            $q->where('categories.id', $this->getCategoryId())->where('category_partner.is_verified', 1);
        });
    }

    private function getCategoryId()
    {
        return $this->serviceRequestObject[0]->getCategory()->id;
    }

    public function checkService()
    {
        $this->partnerQuery = $this->partnerQuery->whereHas('services', function ($query) {
            $this->buildServiceQuery($query);
        });
    }

    protected function buildServiceQuery(EloquentBuilder $query)
    {
        $query
            ->whereHas('category', function ($q) {
                $q->publishedForAny();
            })->select(DB::raw('count(*) as c'))
            ->whereIn('services.id', $this->getServiceIds())
            ->where('partner_service.is_published', 1)
            ->publishedForAll()
            ->groupBy('partner_id')
            ->havingRaw('c=' . count($this->getServiceIds()))
            ->where('partner_service.is_verified', 1);
    }

    private function getServiceIds()
    {
        $service_ids = [];
        foreach ($this->serviceRequestObject as $serviceRequestObject) {
            array_push($service_ids, $serviceRequestObject->getServiceId());
        }
        return array_unique($service_ids);
    }

    public function checkLeave()
    {
        $this->partnerQuery = $this->partnerQuery->whereDoesntHave('leaves', function ($q) {
            $q->where('end', null)->orWhere(function($q) {
                $q->where('start', '<=', Carbon::now())
                    ->where('end', '>=', Carbon::now()->addDays(7));
            });
        });
    }

    public function withResource()
    {
        $this->partnerQuery = $this->partnerQuery->with([
            'handymanResources' => function ($q) {
                $q->selectRaw('count(distinct resources.id) as total_experts, partner_id')
                    ->join('category_partner_resource', 'category_partner_resource.partner_resource_id', '=', 'partner_resource.id')
                    ->where('category_partner_resource.category_id', $this->getCategoryId())->groupBy('partner_id')->verified();
            }
        ]);
    }

    public function WithAvgReview()
    {
        $this->partnerQuery = $this->partnerQuery->with([
            'reviews' => function ($q) {
                $q->selectRaw("AVG(reviews.rating) as avg_rating")
                    ->selectRaw("count(reviews.id) as total_ratings")
                    ->selectRaw("reviews.partner_id")->whereIn('reviews.category_id', $this->getCategoryIdsOfMasterCategory())->groupBy('reviews.partner_id');
            }
        ]);
    }


    public function withTotalCompletedOrder()
    {
        $this->partnerQuery = $this->partnerQuery->with([
            'jobs' => function ($q) {
                $q->selectRaw("count(case when status in ('Served') and category_id in(" . implode($this->getCategoryIdsOfMasterCategory(), ',') . ") then status end) as total_completed_orders")
                    ->groupBy('partner_id');
            }
        ]);
    }

    public function withTotalOngoingJobs()
    {
        $this->partnerQuery = $this->partnerQuery->with([
            'jobs' => function ($q) {
                $q->selectRaw("count(case when status in ('Accepted', 'Schedule Due', 'Process', 'Serve Due') then status end) as ongoing_jobs")->groupBy('partner_id');
            }
        ]);
    }

    public function withService()
    {
        $this->partnerQuery = $this->partnerQuery->with([
            'services' => function ($q) {
                $q->whereIn('service_id', $this->getServiceIds());
            }, 'categories' => function ($q) {
                $q->where('categories.id', $this->getCategoryId());
            }
        ]);
    }

    public function withSubscriptionPackage()
    {
        $this->partnerQuery = $this->partnerQuery->with(['subscription']);
    }

    public function checkPartnerHasResource()
    {
        $this->partners = $this->partners->filter(function ($partner) {
            $handyman_resources = $partner->handymanResources->first();
            return $handyman_resources && (int)$handyman_resources->total_experts > 0 ? 1 : 0;
        });
    }

    public function checkPartner()
    {
        if (count($this->partnerIds) > 0) $this->partnerQuery = $this->partnerQuery->whereIn('partners.id', $this->partnerIds);
    }

    public function checkPartnersToIgnore()
    {
        if (count($this->partnerIdsToIgnore) > 0) $this->partnerQuery = $this->partnerQuery->whereNotIn('partners.id', $this->partnerIdsToIgnore);
    }

    public function checkCanAccessMarketPlace()
    {
        $this->partnerQuery = $this->partnerQuery->whereNotIn('package_id', config('sheba.marketplace_not_accessible_packages_id'));
    }

    public function checkPartnerVerification()
    {
        $this->partnerQuery = $this->partnerQuery->verified();
    }

    public function checkPartnerCreditLimit()
    {
        $this->partners->load([
            'walletSetting' => function ($q) {
                $q->select('id', 'partner_id', 'min_wallet_threshold');
            }
        ]);
        $this->partners = $this->partners->filter(function ($partner, $key) {
            /** @var Partner $partner */
            return $partner->hasAppropriateCreditLimit();
        });
    }

    public function setPartnerIds(array $partner_ids)
    {
        $this->partnerIds = $partner_ids;
        return $this;
    }

    public function setPartnerIdsToIgnore(array $partner_ids)
    {
        $this->partnerIdsToIgnore = $partner_ids;
        return $this;
    }

    public function setServiceRequestObjectArray(array $service_request_object)
    {
        $this->serviceRequestObject = $service_request_object;
        return $this;
    }

    public function setGeo(Geo $geo)
    {
        $this->geo = $geo;
        return $this;
    }

    public function setScheduleDate($date)
    {
        $this->scheduleDate = is_array($date) ? $date : (json_decode($date) ? json_decode($date) : [$date]);
        return $this;
    }

    public function setScheduleTime($time)
    {
        $this->scheduleTime = $time;
        return $this;
    }

    public function checkGeoWithinOperationalZone()
    {
        // TODO: Implement checkGeoWithinOperationalZone() method.
    }

    public function checkGeoWithinPartnerRadius()
    {
        if (count($this->partners) == 0) return;
        $current = new Coords($this->geo->getLat(), $this->geo->getLng());
        $this->partners = $this->partners->reject(function ($partner) {
            return $partner->geo_informations == null;
        });
        $to = $this->partners->map(function ($partner) {
            $geo = json_decode($partner->geo_informations);
            return new Coords($geo->lat, $geo->lng, $partner->id);
        })->values()->all();
        $distance = (new Distance(DistanceStrategy::$VINCENTY))->matrix();
        $results = $distance->from([$current])->to($to)->sortedDistance()[0];
        $this->partners = $this->partners->filter(function ($partner) use ($results) {
            return $results[$partner->id] <= (double)json_decode($partner->geo_informations)->radius * 1000;
        });
    }

    public function runQuery()
    {
        $this->partners = $this->partnerQuery->get();
    }

    public function get()
    {
        return $this->partners;
    }

    public function first()
    {
        return $this->partners->first();
    }

    public function removeShebaHelpDesk()
    {
        $this->partners = $this->partners->filter(function ($partner) {
            return $partner->id != config('sheba.sheba_help_desk_id');
        });
    }

    public function withoutShebaHelpDesk()
    {
        $this->partnerQuery = $this->partnerQuery->where('partners.id', '<>', config('sheba.sheba_help_desk_id'));
    }

    public function removeUnavailablePartners()
    {
        $this->partners = $this->partners->filter(function ($partner) {
            return $partner['is_available'];
        });
    }

    public function checkPartnerDailyOrderLimit()
    {
        $this->partners->load([
            'todayOrders' => function ($q) {
                $q->select('id', 'partner_id');
            }
        ]);
        $this->partners = $this->partners->filter(function ($partner, $key) {
            /** @var Partner $partner */
            if (is_null($partner->order_limit)) return true;
            return $partner->todayOrders->count() < $partner->order_limit;
        });
    }

    public function checkOption()
    {
        foreach ($this->serviceRequestObject as $selected_service) {
            /** @var ServiceRequestObject $selected_service */
            $service = $selected_service->getService();
            if ($service->isOptions()) {
                $this->partners = $this->partners->filter(function ($partner) use ($service, $selected_service) {
                    $service = $partner->services->where('id', $service->id)->first();
                    if (empty($service->pivot->prices)) return 0;
                    return $this->hasThisOption($service->pivot->prices, implode(',', $selected_service->getOption()));
                });
            }
        }
    }

    private function hasThisOption($prices, $option)
    {
        $prices = json_decode($prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return true;
            }
        }
        return false;
    }

    public function checkPartnerAvailability()
    {
        $this->partners->load(['workingHours', 'leaves']);
        $this->partners->each(function ($partner) {
            if (!$this->isWithinPreparationTime($partner)) {
                $partner['is_available'] = 0;
                $partner['unavailability_reason'] = PartnerUnavailabilityReasons::PREPARATION_TIME;
                return;
            }
            $partner_available = new PartnerAvailable($partner);
            $partner_available->check($this->scheduleDate, $this->scheduleTime, $this->getCategory());
            if (!$partner_available->getAvailability()) {
                $partner['is_available'] = 0;
                $partner['unavailability_reason'] = $partner_available->getUnavailabilityReason();
                return;
            }
            $partner['is_available'] = 1;
        });
    }

    private function isWithinPreparationTime($partner)
    {
        $category_preparation_time_minutes = $partner->categories->where('id', $this->getCategoryId())->first()->pivot->preparation_time_minutes;
        if ($category_preparation_time_minutes == 0) return 1;
        $start_time = Carbon::parse($this->scheduleDate[0] . ' ' . $this->getScheduleStartTime());
        $end_time = Carbon::parse($this->scheduleDate[0] . ' ' . $this->getScheduleEndTime());
        $preparation_time = Carbon::createFromTime(Carbon::now()->hour)->addMinute(61)->addMinute($category_preparation_time_minutes);
        return $preparation_time->lte($start_time) || $preparation_time->between($start_time, $end_time) ? 1 : 0;
    }

    private function getScheduleStartTime()
    {
        $time = explode('-', $this->scheduleTime);
        return $time[0];
    }

    private function getScheduleEndTime()
    {
        $time = explode('-', $this->scheduleTime);
        return $time[1];
    }

    private function getCategory()
    {
        return $this->serviceRequestObject[0]->getCategory();
    }

    public function resolvePartnerSortingParameters()
    {
        $this->partners = $this->partners->map(function ($partner) {
            $partner['total_completed_orders'] = $partner->jobs->first() ? $partner->jobs->first()->total_completed_orders : 0;
            $partner['total_ratings'] = $partner->reviews->first() ? (int)$partner->reviews->first()->total_ratings : 0;
            $partner['rating'] = $partner->reviews->first() ? (double)$partner->reviews->first()->avg_rating : 0;
            $partner['total_experts'] = $partner->handymanResources->first() ? (int)$partner->handymanResources->first()->total_experts : 0;
            return $partner;
        });
    }

    public function resolveInfoForAdminPortal()
    {
        $this->partners = $this->partners->map(function ($partner) {
            $partner['contact_no'] = $partner->getContactNumber();
            $partner['subscription_type'] = $partner->resolveSubscriptionType();
            $partner['ongoing_jobs'] = $partner->jobs->first() ? $partner->jobs->first()->ongoing_jobs : 0;
            return $partner;
        });

    }

    public function sortPartners()
    {
        $this->partners = (new PartnerSort())->setPartners($this->partners)->getSortedPartners();
    }
}

