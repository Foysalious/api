<?php

namespace App\Sheba\Checkout;

use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\Service;
use App\Repositories\DiscountRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ReviewRepository;
use App\Sheba\Checkout\PartnerPrice;

class PartnerList
{
    public $partners;
    public $hasPartners = false;
    public $selected_services;
    private $location;
    private $date;
    private $time;
    private $partnerServiceRepository;
    private $discountRepository;

    public function __construct($services, $date, $time, $location)
    {
        $this->location = (int)$location;
        $this->date = $date;
        $this->time = $time;
        $this->selected_services = $this->getSelectedServices($services);
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->discountRepository = new DiscountRepository();
    }

    private function getSelectedServices($services)
    {
        $selected_services = collect();
        foreach ($services as $service) {
            $selected_service = Service::select('id', 'category_id', 'min_quantity', 'variable_type', 'variables')->where('id', $service->id)->published()->first();
            $selected_service['quantity'] = $service->quantity;
            $selected_service['option'] = $service->option;
            $selected_services->push($selected_service);
        }
        return $selected_services;
    }

    public function find($partner_id = null)
    {
        $this->partners = $this->findPartnersByServiceAndLocation($partner_id);
        $this->partners->load(['services' => function ($q) {
            $q->whereIn('services.id', $this->selected_services->pluck('id'));
        }]);
        $selected_option_services = $this->selected_services->where('variable_type', 'Options');
        $this->filterByOption($selected_option_services);
        $this->filterByCreditLimit();
        $this->addAvailability();
        $this->calculateHasPartner();
    }

    private function findPartnersByServiceAndLocation($partner_id = null)
    {
        $service_ids = $this->selected_services->pluck('id');
        $query = Partner::whereHas('locations', function ($query) {
            $query->where('locations.id', (int)$this->location);
        })->whereHas('services', function ($query) use ($service_ids) {
            $query->whereIn('services.id', $service_ids)->published();
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
        $this->partners->load('basicInformations');
        $this->partners->each(function ($partner, $key) {
            $partner['is_available'] = ((new PartnerRepository($partner)))->isAvailable($this->date, $this->time);
        });
    }

    public function calculatePrice()
    {
        foreach ($this->partners as $partner) {
            $total_discount_price = $total_price_with_discount = $totalPriceWithoutDiscount = 0;
            list($partner['discount'], $partner['priceWithDiscount'], $partner['priceWithoutDiscount']) = $this->getTotalServicePricing($partner, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
        }
    }

    public function sortByShebaSelectedCriteria()
    {
        $this->calculateAverageRating();
        $this->sortByRatingDesc();
        $this->sortByLowestPrice();
    }

    private function calculateAverageRating()
    {
        $this->partners->load('reviews');
        foreach ($this->partners as $partner) {
            $partner['rating'] = (new ReviewRepository())->getAvgRating($partner->reviews);
        }
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
            return $partner->priceWithDiscount;
        });
    }

    private function getTotalServicePricing($partner, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount)
    {
        foreach ($partner->services as &$service) {
            $selected_service = $this->selected_services->where('id', $service->id)->first();
            $price = $selected_service->variable_type == 'Options' ? $this->partnerServiceRepository->getPriceOfThisOption($service->pivot->prices, implode(',', $selected_service->option)) : (double)$service->pivot->prices;
            $running_discount = (PartnerService::find($service->pivot->id))->discount();
            list($discount_price, $price_with_discount, $priceWithoutDiscount) = $this->discountRepository->getServiceDiscountValues($running_discount, (double)$price, (double)$selected_service->quantity);
            $total_discount_price += $discount_price;
            $total_price_with_discount += $price_with_discount;
            $totalPriceWithoutDiscount += $priceWithoutDiscount;
            array_add($service, 'price', $price);
            array_add($service, 'priceWithDiscount', $price_with_discount);
            array_add($service, 'discountPrice', $discount_price);
            if ($running_discount) {
                array_add($service, 'discount_id', $running_discount->id);
                array_add($service, 'sheba_contribution', (double)$running_discount->sheba_contribution);
                array_add($service, 'partner_contribution', (double)$running_discount->partner_contribution);
                array_add($service, 'discount_percentage', $running_discount->is_amount_percentage);
            } else {
                array_add($service, 'discount_id', null);
                array_add($service, 'sheba_contribution', 0);
                array_add($service, 'partner_contribution', 0);
                array_add($service, 'discount_percentage', 0);
            }
        }
        return array($total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
    }

    private function calculateHasPartner()
    {
        if (count($this->partners) > 0) {
            $this->hasPartners = true;
        }
    }
}