<?php

namespace App\Sheba\Checkout;

use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\Service;
use App\Repositories\DiscountRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ReviewRepository;

class PartnerList
{
    private $selected_services;
    private $partners;
    private $location;
    private $date;
    private $time;
    private $partnerServiceRepository;
    private $discountRepository;
    private $reviewRepository;

    public function __construct(Array $services, $date, $time, $location)
    {
        $this->location = $location;
        $this->date = $date;
        $this->time = $time;
        $this->selected_services = $this->getSelectedServices($services);
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->discountRepository = new DiscountRepository();
        $this->reviewRepository = new ReviewRepository();
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

    public function get($partner = null)
    {
        $this->partners = $this->getPartners($this->selected_services->pluck('id'), $partner);
        $selected_option_services = $this->selected_services->where('variable_type', 'Options');
        $this->filterByOption($selected_option_services);
        $this->filterByCreditLimitAndAvailability();
        $this->partners->load('reviews');
        foreach ($this->partners as $partner) {
            $total_discount_price = $total_price_with_discount = $totalPriceWithoutDiscount = 0;
            list($partner['discount'], $partner['priceWithDiscount'], $partner['priceWithoutDiscount']) = $this->getTotalServicePricing($partner, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
            $partner['rating'] = $this->reviewRepository->getAvgRating($partner->reviews);
            array_forget($partner, 'wallet');
        }
        $this->filter();
        return $this->partners;
    }

    public function filter($rating = true, $priceWithDiscount = true)
    {
        if ($rating) {
            $this->partners = $this->partners->sortByDesc(function ($partner, $key) {
                return $partner->rating;
            });
        }
        if ($priceWithDiscount) {
            $this->partners = $this->partners->sortBy(function ($partner, $key) {
                return $partner->priceWithDiscount;
            });
        }
    }

    private function getPartners($service_ids, $partner = null)
    {
        $query = Partner::whereHas('locations', function ($query) {
            $query->where('locations.id', (int)$this->location);
        })->whereHas('services', function ($query) use ($service_ids) {
            $query->whereIn('services.id', $service_ids)->published();
        })->with(['services' => function ($q) use ($service_ids) {
            $q->whereIn('services.id', $service_ids)->published();
        }])->published()->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet');
        if ($partner != null) {
            $query = $query->where('partners.id', $partner);
        }
        return $query->get();
    }

    private function filterByOption($option_services)
    {
        foreach ($option_services as $option_service) {
            $this->partners = $this->partners->filter(function ($partner, $key) use ($option_service) {
                $service = $partner->services->where('id', $option_service->id)->first();
                return $this->partnerServiceRepository->hasThisOption($service->pivot->prices, implode(',', $option_service->option));
            });
        }
    }

    private function filterByCreditLimitAndAvailability()
    {
        $this->partners = $this->partners->load('walletSetting')->filter(function ($partner, $key) {
            return ((new PartnerRepository($partner)))->hasAppropriateCreditLimit();
        })->load('basicInformations')->each(function (&$partner, $key) {
            $partner['is_available'] = ((new PartnerRepository($partner)))->isAvailable($this->date, $this->time);
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
}