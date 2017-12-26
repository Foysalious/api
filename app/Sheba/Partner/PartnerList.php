<?php

namespace App\Sheba\Partner;

use App\Models\Partner;
use App\Models\PartnerService;
use App\Models\Service;
use App\Repositories\DiscountRepository;
use App\Repositories\PartnerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\ReviewRepository;

class PartnerList
{
    private $services;
    private $partners;
    private $location;
    private $partnerServiceRepository;

    public function __construct($services, $location)
    {
        $this->services = $services;
        $this->location = $location;
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->discountRepository = new DiscountRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function generatePartnerList($date, $time)
    {
        $service_details = collect(json_decode($this->services))->each(function ($item, $key) {
            $item->service = Service::find($item->id);
        });
        $service_ids = $service_details->pluck('id');
        $this->partners = $this->getPartners($service_ids);
        $this->filterPartnersBytServiceOption($service_details);
        $this->filterPartnersByCreditLimitAndAvailability($date, $time);
        foreach ($this->partners as $partner) {
            $total_discount_price = $total_price_with_discount = $totalPriceWithoutDiscount = 0;
            list($partner['discount'], $partner['priceWithDiscount'], $partner['priceWithoutDiscount']) = $this->getTotalServicePricing($partner, $service_details, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
            $partner['rating'] = $this->reviewRepository->getAvgRating($partner->reviews);
            removeRelationsAndFields($partner);
            array_forget($partner, 'wallet');
        }
        return $this;
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
        return $this->partners;
    }

    private function getPartners($service_ids)
    {
        return Partner::whereHas('locations', function ($query) {
            $query->where('locations.id', $this->location);
        })->whereHas('services', function ($query) use ($service_ids) {
            $query->whereIn('services.id', $service_ids)->published();
        })->with(['services' => function ($q) use ($service_ids) {
            $q->whereIn('services.id', $service_ids)->published();
        }])->published()->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet')->get();
    }

    private function filterPartnersBytServiceOption($service_details)
    {
        foreach ($service_details as $service_detail) {
            if ($service_detail->service->variable_type == 'Options') {
                $this->partners = $this->partners->filter(function ($partner, $key) use ($service_detail) {
                    $service = $partner->services->where('id', $service_detail->id)->first();
                    return $this->partnerServiceRepository->hasThisOption($service->pivot->prices, implode(',', $service_detail->option));
                });
            }
        }
    }

    private function filterPartnersByCreditLimitAndAvailability($date, $time)
    {
        $this->partners = $this->partners->load('walletSetting')->filter(function ($partner, $key) {
            return ((new PartnerRepository($partner)))->hasAppropriateCreditLimit();
        })->load('basicInformations', 'reviews')->each(function (&$partner, $key) use ($date, $time) {
            $partner['is_available'] = ((new PartnerRepository($partner)))->isAvailable($date, $time);
        });
    }

    private function getTotalServicePricing($partner, $service_details, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount)
    {
        foreach ($partner->services as $service) {
            $selected_service = $service_details->where('id', $service->id)->first();
            $price = $service->variable_type == 'Options' ? $this->partnerServiceRepository->getPriceOfThisOption($service->pivot->prices, implode(',', $selected_service->option)) : (double)$service->pivot->prices;
            list($discount_price, $price_with_discount, $priceWithoutDiscount) = $this->discountRepository->getServiceDiscountValues((PartnerService::find($service->pivot->id))->discount(), (double)$price, (double)$selected_service->quantity);
            $total_discount_price += $discount_price;
            $total_price_with_discount += $price_with_discount;
            $totalPriceWithoutDiscount += $priceWithoutDiscount;
        }
        return array($total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
    }
}