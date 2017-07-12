<?php

namespace App\Repositories;


use App\Models\PartnerService;
use App\Models\Service;
use Carbon\Carbon;

class ServiceRepository
{
    private $discountRepository;
    private $_serviceRequest = [];

    public function __construct()
    {
        $this->discountRepository = new DiscountRepository();
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
     * @param $request
     * @return mixed
     */
    public function getStartPrice($service, $request)
    {
        $location = $request->location;
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
//                    array_push($price, (float)$min);
                }
                array_add($service, 'start_price', min($price));
            } elseif ($service->variable_type == 'Fixed') {
                $price = array();
                foreach ($partners as $partner) {
                    $partner['prices'] = (float)$partner->pivot->prices;
                    $discount = PartnerService::find($partner->pivot->id)->discount();
                    $calculate_partner = $this->discountRepository->addDiscountToPartnerForService($partner, $discount);
                    array_push($price, $calculate_partner['discounted_price']);
//                    array_push($price, (float)$partner->pivot->prices);
//                    array_push($price, (float)$min);
                }
                array_add($service, 'start_price', min($price));
            }
            array_forget($service, 'partners');
        }
        return $service;
//        $partners = $service->partners()->where([
//            ['is_published', 1],
//            ['is_verified', 1]
//        ])->get();
//        if (count($partners) > 0) {
//            if ($service->variable_type == 'Options') {
//                $price = array();
//                foreach ($partners as $partner) {
//                    $min = min((array)json_decode($partner->pivot->prices));
//                    array_push($price, (float)$min);
//                }
//                array_add($service, 'start_price', min($price));
//            } elseif ($service->variable_type == 'Fixed') {
//                $price = array();
//                foreach ($partners as $partner) {
//                    array_push($price, (float)$partner->pivot->prices);
//                }
//                array_add($service, 'start_price', min($price));
//            }
//            array_forget($service, 'partners');
//        }
//        return $service;
//        $partners = $service->partners()->where([
//            ['is_published', 1],
//            ['is_verified', 1]
//        ])->get();
    }

    /**
     * @param $service
     * @param $partner
     * @return mixed
     */
    private
    function getPartnerRatingReviewCount($service, $partner)
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
        return array($this->_filterPartnerOnWorkingHourAndDay($service_partners), []);
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
     * @param null $request
     * @return mixed
     */
    private function _filterPartnerOnWorkingHourAndDay($service_partners)
    {
        foreach ($service_partners as $key => $partner) {
            if ($this->_worksAtThisTime($partner) == false) {
                array_forget($service_partners, $key);
            }
        }
        return $service_partners;
    }

    /**
     * @param $partner
     * @return int
     */
    private function _filterPartnerOnWorkingHour($partner)
    {
        $working_hours = json_decode($partner->basicInformations->working_hours);
        if (array_key_exists('time', $this->_serviceRequest)) {
            if ($this->_serviceRequest !== '') {
                //If customer sets a time then check availability of partner otherwise don't check anyone
                if (array_has(constants('JOB_PREFERRED_TIMES'), $this->_serviceRequest['time']) && $this->_serviceRequest['time'] != 'Anytime') {
                    return $this->_betweenWorkingHours($working_hours, constants('JOB_WORKING_HOURS')[$this->_serviceRequest['time']]);
                }
            }
        }
//        else {
//            $time = strtotime(Carbon::now()->format('h:i A'));
//            return strtotime($working_hours->day_start) <= $time && $time <= strtotime($working_hours->day_end) ? 1 : 0;
//        }
        return 1;
    }

    private function _betweenWorkingHours($working_hours, $times)
    {
        $fail = 0;
        foreach ($times as $time) {
            $time = strtotime($time);
            // time doesn't fall int working hour
            if (!(strtotime($working_hours->day_start) <= $time && $time <= strtotime($working_hours->day_end))) {
                $fail++;
            }
        }
        // If both times don't fall into working hour return false
        return $fail == 2 ? false : true;
    }

    /**
     * @param $partner
     * @return bool
     */
    private function _filterPartnerOnWorkingDay($partner)
    {
        if (array_key_exists('day', $this->_serviceRequest)) {
            if ($this->_serviceRequest !== '') {
                $day = date('l', strtotime($this->_serviceRequest['day']));
            }
        } else
            $day = date('l');
        return array_search($day, json_decode($partner->basicInformations->working_days));
    }

    /**
     * @param $partner
     * @return mixed
     */
    private function _worksAtThisTime($partner)
    {
        if ($this->_filterPartnerOnWorkingDay($partner) == 0) {
            return false;
        }
        if ($this->_filterPartnerOnWorkingHour($partner) == 0) {
            return false;
        }
        return true;
    }
}