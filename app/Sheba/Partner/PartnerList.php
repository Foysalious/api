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

    public function __construct($location)
    {
        $this->location = $location;
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->discountRepository = new DiscountRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function getList($service_details, $date, $time, $partner = null)
    {
        $service_ids = $service_details->pluck('service_id');
        $this->partners = $this->getPartners($service_ids, $partner);
        $this->filterPartnersByServiceOption($service_details);
        $this->filterPartnersByCreditLimitAndAvailability($date, $time);
        $this->partners->load('reviews');
        foreach ($this->partners as $partner) {
            $total_discount_price = $total_price_with_discount = $totalPriceWithoutDiscount = 0;
            list($partner['discount'], $partner['priceWithDiscount'], $partner['priceWithoutDiscount']) = $this->getTotalServicePricing($partner, $service_details, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
            $partner['rating'] = $this->reviewRepository->getAvgRating($partner->reviews);
//            removeRelationsAndFields($partner);
            array_forget($partner, 'wallet');
        }
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
        return $this->partners;
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

    private function filterPartnersByServiceOption($service_details)
    {
        foreach ($service_details as $service_detail) {
            if ($service_detail->service->variable_type == 'Options') {
                $this->partners = $this->partners->filter(function ($partner, $key) use ($service_detail) {
                    $service = $partner->services->where('id', $service_detail->service_id)->first();
                    return $this->partnerServiceRepository->hasThisOption($service->pivot->prices, implode(',', $service_detail->option));
                });
            }
        }
    }

    private function filterPartnersByCreditLimitAndAvailability($date, $time)
    {
        $this->partners = $this->partners->load('walletSetting')->filter(function ($partner, $key) {
            return ((new PartnerRepository($partner)))->hasAppropriateCreditLimit();
        })->load('basicInformations')->each(function (&$partner, $key) use ($date, $time) {
            $partner['is_available'] = ((new PartnerRepository($partner)))->isAvailable($date, $time);
        });
    }

    private function getTotalServicePricing($partner, $service_details, $total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount)
    {
        foreach ($partner->services as &$service) {
            $selected_service = $service_details->where('id', $service->service_id)->first();
            $price = $service->variable_type == 'Options' ? $this->partnerServiceRepository->getPriceOfThisOption($service->pivot->prices, implode(',', $selected_service->option)) : (double)$service->pivot->prices;
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
                array_add($service, 'discount_percentage', null);
            }
        }
        return array($total_discount_price, $total_price_with_discount, $totalPriceWithoutDiscount);
    }
}