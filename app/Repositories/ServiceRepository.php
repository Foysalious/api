<?php

namespace App\Repositories;


use App\Models\PartnerService;
use App\Models\Service;

class ServiceRepository
{
    private $discountRepository;

    public function __construct()
    {
        $this->discountRepository = new DiscountRepository();
    }

    /**
     * @param $service
     * @param $location
     * @return mixed
     */
    public function partners($service, $location = null)
    {
        if ($location != null) {
            $service_partners = $this->partnerSelectByLocation($service, $location);
        } else {
            $service_partners = $this->partnerSelect($service);
        }
        $final_partners = [];
        foreach ($service_partners as $key => $partner) {
            $prices = json_decode($partner->prices);
            /**
             * For optioned services
             */
            if ($service->variable_type == 'Options') {
                $variables = json_decode($service->variables);
                // Get the first option of service
                $first_option = key($variables->prices);
                //check for first option exists in prices
                $count = 0;
                foreach ($prices as $key => $price) {
                    if ($key == $first_option) {
                        $count++;
                        break;
                    }
                }
                if ($count > 0) {
                    $price = reset($prices);// first value of array key
                    array_set($partner, 'prices', $price);
                } else {
                    //remove the partner from service_partner list
                    array_forget($service_partners, $key);
                    continue;
                }
            }
            $partner = $this->discountRepository->addDiscountToPartnerForService($partner);
            // review count of this partner for this service
            $review = $partner->reviews()->where([
                ['review', '<>', ''],
                ['service_id', $service->id]
            ])->count('review');
            //avg rating of the partner for this service
            $rating = $partner->reviews()->where('service_id', $service->id)->avg('rating');
            array_add($partner, 'review', $review);
            array_add($partner, 'rating', $rating);
            array_forget($partner, 'pivot');
            array_push($final_partners, $partner);
        }
        return $final_partners;

    }

    /**
     * Get partner of a service for a selected option in
     * @param $service
     * @param $option
     * @param $location
     * @return array
     */
    public function partnerWithSelectedOption($service, $option, $location)
    {
        if ($location != null) {
            $service_partners = $this->partnerSelectByLocation($service, $location);
        } else {
            $service_partners = $this->partnerSelect($service);
        }
        $final_partners = [];
        foreach ($service_partners as $key => $partner) {
            // review count of this partner for this service
            $review = $partner->reviews()->where([
                ['review', '<>', ''],
                ['service_id', $service->id]
            ])->count('review');
            //avg rating of the partner for this service
            $rating = $partner->reviews()->where('service_id', $service->id)->avg('rating');
            array_add($partner, 'review', $review);
            array_add($partner, 'rating', $rating);
            /**
             * For optioned services
             */
            if ($service->variable_type == 'Options') {
                $options = (array)json_decode($partner->prices);
                $count = 0;
                foreach ($options as $key => $price) {
                    if ($key == $option) {
                        $count++;
                        break;
                    }
                }
                //if the selected option exist in partner option list add the partner to final list
                if ($count > 0) {
                    array_set($partner, 'prices', $price);
                    $partner = $this->discountRepository->addDiscountToPartnerForService($partner);
                    array_forget($partner, 'pivot');
                    array_push($final_partners, $partner);
//                    /**
//                     * if service has discount update the discount prices
//                     */
//                    if (($discount = $service->runningDiscountOf($partner->id)) != null) {
//                        $d_p = $discount->getAmount($option);
//                        $partner['discount_price'] = $d_p;
//                        $partner['discounted_price'] = $partner->prices - $d_p;
//                    }
                }
            } elseif ($service->variable_type == 'Fixed') {
                $partner = $this->discountRepository->addDiscountToPartnerForService($partner);
                array_forget($partner, 'pivot');
                array_push($final_partners, $partner);
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
            ->where([
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
        }])
            ->where([
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

    public function getStartEndPrice($service)
    {
        $partners = $service->partners()->where([
            ['is_published', 1],
            ['is_verified', 1]
        ])->get();
        if (count($partners) > 0) {
            if ($service->variable_type == 'Options') {
                $price = array();
                foreach ($partners as $partner) {
                    $min = min((array)json_decode($partner->pivot->prices));
                    array_push($price, (float)$min);
                }
                array_add($service, 'start_price', min($price));
//            $prices = (array)(json_decode($service->variables)->min_prices);
//            $min = (min($prices));
//            $prices = (array)(json_decode($service->variables)->max_prices);
//            $max = (max($prices));
//            array_add($service, 'end_price', $max);
            } elseif ($service->variable_type == 'Fixed') {
                $price = array();
                foreach ($partners as $partner) {
                    array_push($price, (float)$partner->pivot->prices);
                }
                array_add($service, 'start_price', min($price));
//            array_add($service, 'start_price', json_decode($service->variables)->min_price);
//            array_add($service, 'end_price', json_decode($service->variables)->max_price);
//            array_add($service, 'end_price', json_decode($service->variables)->max_price);
            }
            array_forget($service, 'partners');
        }
        return $service;
    }

    public function getReviews($service)
    {
        // review count of this service
        $review = $service->reviews()->where('review', '<>', '')->count('review');
        array_add($service, 'review_count', $review);
        //rating count of this service
        $total_rating = $service->reviews()->where('rating', '<>', '')->count('rating');
        array_add($service, 'rating_count', $total_rating);
        //avg rating of this service
        $rating = $service->reviews()->avg('rating');
        array_add($service, 'rating', round($rating, 1));
        return $service;
    }
}