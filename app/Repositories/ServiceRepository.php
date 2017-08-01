<?php

namespace App\Repositories;


use App\Models\PartnerService;
use App\Models\Service;
use Carbon\Carbon;
use Sheba\Partner\PartnerAvailable;

class ServiceRepository
{
    private $discountRepository;
    private $reviewRepository;
    private $_serviceRequest = [];

    public function __construct()
    {
        $this->discountRepository = new DiscountRepository();
        $this->reviewRepository = new ReviewRepository();
    }

    public function partners($service, $location = null, $request)
    {
        $this->_serviceRequest = $request->all();
        list($service_partners, $final_partners) = $this->getPartnerService($service, $location);
        foreach ($service_partners as $key => $partner) {
            $prices = json_decode($partner->prices);
            if ($service->variable_type == 'Options') {
                $variables = json_decode($service->variables);
                // Get the first option of service
                $option = $first_option = key($variables->prices);
                //check for first option exists in prices
                $price = $this->partnerServesThisOption($prices, $option);
                if ($price != null) {
                    array_set($partner, 'prices', $price);
                } else {
                    //remove the partner from service_partner list
                    array_forget($service_partners, $key);
                    continue;
                }
            }
            $partner = $this->getPartnerRatingReviewCount($service, $partner);
            $final_partners = $this->addToFinalPartnerListWithDiscount($partner, $final_partners);
        }
        return $final_partners;

    }

    /**
     * Get partner of a service for a selected option in
     * @param $service
     * @param $option
     * @param $location
     * @param $request
     * @return array
     */
    public function partnerWithSelectedOption($service, $option, $location, $request)
    {
        $this->_serviceRequest = $request->all();
        list($service_partners, $final_partners) = $this->getPartnerService($service, $location);
        foreach ($service_partners as $key => $partner) {
            $partner = $this->getPartnerRatingReviewCount($service, $partner);
            if ($service->variable_type == 'Options') {
                $prices = (array)json_decode($partner->prices);
                $price = $this->partnerServesThisOption($prices, $option);
                if ($price != null) {
                    array_set($partner, 'prices', $price);
                    $final_partners = $this->addToFinalPartnerListWithDiscount($partner, $final_partners);
                }
            } elseif ($service->variable_type == 'Fixed') {
                $final_partners = $this->addToFinalPartnerListWithDiscount($partner, $final_partners);
            }
        }
        return $final_partners;
    }

    /**
     * Select partners for a service for a location
     * @param $service
     * @param $location
     * @return mixed
     */
    public function partnerSelectByLocation($service, $location)
    {
        return $service->partners()
            ->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'prices')
            ->with(['basicInformations' => function ($q) {
                $q->select('id', 'partner_id', 'working_days', 'working_hours');
            }])->where([
                ['partners.status', 'Verified'],
                ['is_verified', 1],
                ['is_published', 1]
            ])->whereHas('locations', function ($query) use ($location) {
                $query->where('id', $location);
            })->get();
    }

    public function partnerSelect($service)
    {
        return $service->partners()->with(['locations' => function ($query) {
            $query->select('id', 'name');
        }])->with(['basicInformations' => function ($q) {
            $q->select('id', 'partner_id', 'working_days', 'working_hours');
        }])->where([
            ['is_verified', 1],
            ['is_published', 1],
            ['partners.status', 'Verified']
        ])->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'prices')->get();
    }

    public function getMaxMinPrice($service)
    {
        $service = Service::find($service->id);
        $max_price = [];
        $min_price = [];
        if ($service->partners->isEmpty()) {
            return array(0, 0);
        }
        foreach ($service->partners as $partner) {
            $prices = (array)json_decode($partner->pivot->prices);
            $max = max($prices);
            $min = min($prices);
            array_push($max_price, $max);
            array_push($min_price, $min);
        }
        return array(max($max_price), min($min_price));
    }

    /**
     * Get Start price based on location
     * @param $service
     * @param int $location
     * @return mixed
     */
    public function getStartPrice($service, $location)
    {
        if (empty($location)) {
            $location = 4;
        }
        $calculated_service = Service::with(['partners' => function ($q) use ($location) {
            $q->where([
                ['is_published', 1],
                ['is_verified', 1],
                ['partners.status', 'Verified']
            ])->whereHas('locations', function ($query) use ($location) {
                $query->where('id', $location);
            });
        }])->where('id', $service->id)->first();
        $partners = $calculated_service->partners;
        if (count($partners) > 0) {
            if ($service->variable_type == 'Options') {
                $price = array();
                foreach ($partners as $partner) {
                    $min = min((array)json_decode($partner->pivot->prices));
                    $partner['prices'] = $min;
                    $discount = PartnerService::find($partner->pivot->id)->discount();
                    $calculate_partner = $this->discountRepository->addDiscountToPartnerForService($partner, $discount);
                    array_push($price, $calculate_partner['discounted_price']);
                }
                array_add($service, 'start_price', min($price) * $service->min_quantity);
            } elseif ($service->variable_type == 'Fixed') {
                $price = array();
                foreach ($partners as $partner) {
                    $partner['prices'] = (float)$partner->pivot->prices;
                    $discount = PartnerService::find($partner->pivot->id)->discount();
                    $calculate_partner = $this->discountRepository->addDiscountToPartnerForService($partner, $discount);
                    array_push($price, $calculate_partner['discounted_price']);
                }
                array_add($service, 'start_price', min($price) * $service->min_quantity);
            }
            array_forget($service, 'partners');
        }
//        dump($service->id,min($price));
        return $service;
    }

    /**
     * @param $service
     * @param $partner
     * @return mixed
     */
    private function getPartnerRatingReviewCount($service, $partner)
    {
        $review = $partner->reviews()->where([
//            ['review', '<>', ''],
            ['service_id', $service->id]
        ])->count('review');
        $rating = $partner->reviews()->where('service_id', $service->id)->avg('rating');
        array_add($partner, 'review', $review);
        $partner['rating'] = empty($rating) ? 5 : floor($rating);
        return $partner;
    }

    private function getPartnerService($service, $location)
    {
        $service_partners = $location != null ? $this->partnerSelectByLocation($service, $location) : $this->partnerSelect($service);
        return array($this->_filterPartnerOnWorkingHourDayLeave($service_partners), []);
    }


    private function partnerServesThisOption($prices, $option)
    {
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return $price;
            }
        }
        return null;
    }

    private function addToFinalPartnerListWithDiscount($partner, $final_partners)
    {
        $discount = PartnerService::find($partner->pivot->id)->discount();
        $partner = $this->discountRepository->addDiscountToPartnerForService($partner, $discount);
        array_forget($partner, 'pivot');
        array_push($final_partners, $partner);
        return $final_partners;
    }


    /**
     * @param $service_partners
     * @return mixed
     */
    private function _filterPartnerOnWorkingHourDayLeave($service_partners)
    {
        foreach ($service_partners as $key => $partner) {
            array_add($partner, 'available', true);
            if (!(new PartnerAvailable($partner))->available($this->_serviceRequest)) {
                $partner['available'] = false;
            }
            array_forget($partner, 'basicInformations');
        }
        return $service_partners;
    }

    public function _sortPartnerListByAvailability($service_partners)
    {
        $final = [];
        $not_available = [];
        foreach ($service_partners as $partner) {
            if ($partner->available) {
                array_push($final, $partner);
            } else {
                array_push($not_available, $partner);
            }
        }
        foreach ($not_available as $not) {
            array_push($final, $not);
        }
        return $final;
    }

    public function addServiceInfo($services, $location)
    {
        foreach ($services as $key => $service) {
            array_add($service, 'discount', Service::find($service->id)->hasDiscounts());
            $service = $this->getStartPrice($service, $location);
            array_add($service, 'slug', str_slug($service->name, '-'));
            $this->reviewRepository->getReviews($service);
            array_forget($service, 'variables');
            array_forget($service, 'partnerServices');
        }
        return $services;
    }


}