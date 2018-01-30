<?php

namespace App\Sheba\Checkout;

use App\Models\Partner;
use App\Models\PartnerServiceDiscount;
use App\Models\Service;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ReviewRepository;
use DB;
use Sheba\Partner\PartnerAvailable;

class PartnerList
{
    public $partners;
    public $hasPartners = false;
    public $selected_services;
    private $location;
    private $date;
    private $time;
    private $partnerServiceRepository;

    public function __construct($services, $date, $time, $location)
    {
        $this->location = (int)$location;
        $this->date = $date;
        $this->time = $time;
        $this->selected_services = $this->getSelectedServices($services);
        $this->partnerServiceRepository = new PartnerServiceRepository();
    }

    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $selected_service = Service::select('id', 'category_id', 'min_quantity', 'variable_type', 'variables')->where('id', $service->id)->publishedForAll()->first();
            foreach ($service as $key => $value) {
                $selected_service[$key] = $value;
            }
            $selected_services->push($selected_service);
        }
        return $selected_services;
    }

    public function find($partner_id = null)
    {
        $this->partners = $this->findPartnersByServiceAndLocation($partner_id);
        $this->partners->load(['services' => function ($q) {
            $q->whereIn('service_id', $this->selected_services->pluck('id')->unique());
        }]);
        $selected_option_services = $this->selected_services->where('variable_type', 'Options');
        $this->filterByOption($selected_option_services);
        $this->filterByCreditLimit();
        $this->addAvailability();
        $this->calculateHasPartner();

    }

    private function findPartnersByServiceAndLocation($partner_id = null)
    {
        $service_ids = $this->selected_services->pluck('id')->unique();
        $query = Partner::whereHas('locations', function ($query) {
            $query->where('locations.id', (int)$this->location);
        })->whereHas('services', function ($query) use ($service_ids) {
            $query->select(DB::raw('count(*) as c'))->whereIn('services.id', $service_ids)->publishedForAll()->groupBy('partner_id')->havingRaw('c=' . count($service_ids));
        })->published()->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet');
        if ($partner_id != null) {
            $query = $query->where('partners.id', $partner_id);
        }
        return $query->get();
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
        $this->partners->load('walletSetting');
        $this->partners = $this->partners->filter(function ($partner, $key) {
            return ((new PartnerRepository($partner)))->hasAppropriateCreditLimit();
        });
    }

    private function addAvailability()
    {
        $this->partners->load(['basicInformations', 'leaves']);
        $this->partners->each(function ($partner, $key) {
            $partner['is_available'] = (new PartnerAvailable($partner))->available($this->date,$this->time,$this->selected_services->first()->category_id);
        });
    }

    public function addPricing()
    {
        foreach ($this->partners as $partner) {
            $pricing = $this->calculateServicePricingAndBreakdowntoPartner($partner);
            foreach ($pricing as $key => $value) {
                $partner[$key] = $value;
            }
        }
    }

    public function calculateAverageRating()
    {
        $this->partners->load('reviews');
        foreach ($this->partners as $partner) {
            $partner['rating'] = (new ReviewRepository())->getAvgRating($partner->reviews);
        }
    }

    public function calculateTotalRatings()
    {
        foreach ($this->partners as $partner) {
            $partner['total_ratings'] = count($partner->reviews);
        }
    }

    public function calculateOngoingJobs()
    {
        foreach ($this->partners as $partner) {
            $partner['ongoing_jobs'] = $partner->onGoingJobs();
        }
    }

    public function sortByShebaSelectedCriteria()
    {
        $this->sortByRatingDesc();
        $this->sortByLowestPrice();
    }

    private function sortByRatingDesc()
    {
        $this->partners = $this->partners->sortByDesc(function ($partner, $key) {
            return $partner->rating;
        });
    }

    private function sortByLowestPrice()
    {
        $this->partners = $this->partners->sortBy(function ($partner, $key) {
            return $partner->discounted_price;
        });
    }

    private function calculateServicePricingAndBreakdowntoPartner($partner)
    {
        $total_service_price = [
            'discount' => 0,
            'discounted_price' => 0,
            'original_price' => 0
        ];
        $services = [];
        foreach ($this->selected_services as $selected_service) {
            $service = $partner->services->where('id', $selected_service->id)->first();
            if ($service->isOptions()) {
                $price = $this->partnerServiceRepository->getPriceOfOptionsService($service->pivot->prices, $selected_service->option);
            } else {
                $price = (double)$service->pivot->prices;
            }
            $discount = $this->calculateDiscountForService($price, $selected_service, $service);
            $service = [];
            $service['discount'] = $discount->__get('discount');
            $service['discounted_price'] = $discount->__get('discounted_price');
            $service['original_price'] = $discount->__get('original_price');

            $total_service_price['discount'] += $service['discount'];
            $total_service_price['discounted_price'] += $service['discounted_price'];
            $total_service_price['original_price'] += $service['original_price'];
            $service['id'] = $selected_service->id;
            $service['option'] = $selected_service->option;
            array_push($services, $service);
        }
        array_add($partner, 'breakdown', $services);
        return $total_service_price;
    }

    private function calculateHasPartner()
    {
        if (count($this->partners) > 0) {
            $this->hasPartners = true;
        }
    }

    private function calculateDiscountForService($price, $selected_service, $service)
    {
        $discount = new Discount($price, $selected_service->quantity);
        $discount->calculateServiceDiscount((PartnerServiceDiscount::find($service->pivot->id)));
        return $discount;
    }
}