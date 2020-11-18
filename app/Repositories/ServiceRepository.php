<?php namespace App\Repositories;

use Sheba\Dal\PartnerService\PartnerService;
use Sheba\Dal\Service\Service;
use App\Sheba\Partner\v1\PartnerAvailable;
use Carbon\Carbon;

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
            array_add($partner, 'ongoing_jobs', $partner->jobs()->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']])->count());
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
            array_add($partner, 'ongoing_jobs', $partner->jobs()->whereIn('status', [constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Schedule_Due']])->count());
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
            ->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet', 'prices')
            ->with(['basicInformations' => function ($q) {
                $q->select('id', 'partner_id', 'working_days', 'working_hours');
            }, 'walletSetting'])->where([
                ['partners.status', 'Verified'],
                ['is_verified', 1],
                ['is_published', 1]
            ])->whereHas('locations', function ($query) use ($location) {
                $query->where('id', $location);
            })->whereHas('categories', function ($query) use ($location, $service) {
                $query->where('categories.id', $service->category_id)->where('is_verified', 1);
            })->get();
    }

    public function partnerSelect($service)
    {
        return $service->partners()->with(['locations' => function ($query) {
            $query->select('id', 'name');
        }])->with(['basicInformations' => function ($q) {
            $q->select('id', 'partner_id', 'working_days', 'working_hours');
        }, 'walletSetting'])->where([
            ['is_verified', 1],
            ['is_published', 1],
            ['partners.status', 'Verified']
        ])->select('partners.id', 'partners.name', 'partners.sub_domain', 'partners.description', 'partners.logo', 'partners.wallet', 'prices')->get();
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
     * @return mixed
     */
    public function getStartPrice($service)
    {
        $partner_services = $service->partnerServices;
        if ($service->variable_type == 'Options') {
            $price = array();
            foreach ($partner_services as $partner_service) {
                if ($partner_service->partner == null) {
                    continue;
                };
                $min = count((array)json_decode($partner_service->prices)) > 0 ? min((array)json_decode($partner_service->prices)) : null;
                $partner_service['prices'] = (float)$min;
                if (count($partner_service->discounts) == 0) {
                    $partner_service['discount'] = null;
                } else {
                    $partner_service['discount'] = $partner_service->discounts->first();
                }
                $calculate_partner = $this->discountRepository->addDiscountToPartnerForService($partner_service, $partner_service['discount']);
                array_push($price, $calculate_partner['discounted_price']);
            }
            if (count($price) > 0) {
                array_add($service, 'start_price', min($price) * $service->min_quantity);
            } else {
                array_add($service, 'start_price', 0);
            }
        } elseif ($service->variable_type == 'Fixed') {
            $price = array();
            foreach ($partner_services as $partner_service) {
                if ($partner_service->partner == null) {
                    continue;
                };
                $partner_service['prices'] = (float)$partner_service->prices;
                if (count($partner_service->discounts) == 0) {
                    $partner_service['discount'] = null;
                } else {
                    $partner_service['discount'] = $partner_service->discounts->first();
                }
                $calculate_partner = $this->discountRepository->addDiscountToPartnerForService($partner_service, $partner_service['discount']);
                array_push($price, $calculate_partner['discounted_price']);
            }
            if (count($price) > 0) {
                array_add($service, 'start_price', min($price) * $service->min_quantity);
            } else {
                array_add($service, 'start_price', 0);
            }
        }
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
            ['review', ' <> ', ''],
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
        $service_partners = $this->getPartnersWithAppropriateCreditLimit($service_partners);
        return array($this->_filterPartnerOnWorkingHourDayLeave($service_partners), []);
    }

    private function getPartnersWithAppropriateCreditLimit($partners)
    {
        return $partners->reject(function ($item, $key) {
            return (double)$item->wallet < (double)$item->walletSetting->min_wallet_threshold;
        })->each(function ($partner, $key) {
            array_forget($partner, 'walletSetting');
            array_forget($partner, 'wallet');
        });
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

    public function addServiceInfo($services, array $scope)
    {
        foreach ($services as $service) {
            if (count($scope) > 0) $service->partnerServices = $this->_filterByPartnerAndWallet($service->partnerServices);

            if (array_search('start_price', $scope) !== false) {
                $service = $this->getStartPrice($service);
            }
            if (array_search('discount', $scope) !== false) {
                $service = $this->_getDiscount($service);
            }
            if (array_search('reviews', $scope) !== false) {
                $this->reviewRepository->getGeneralReviewInformation($service);
            }
            $this->removeRelationsFromModel($service, $service->getRelations());
        }
        return $services;
    }

    public function removeRelationsFromModel($model, $relations)
    {
        foreach ($relations as $key => $relation) {
            array_forget($model, $key);
        }
    }

    private function _filterByPartnerAndWallet($partnerServices)
    {
        return $partnerServices->filter(function ($partner_service, $key) {
            if ($partner_service->partner != null) {

                return $partner_service->partner->hasAppropriateCreditLimit();
            }
            return false;
        });
    }

    public function getPartnerServicesAndPartners($services, $location)
    {
        return $services->load(['partners' => function ($q) use ($location) {
            $q->published()->whereNotNull('geo_informations')/*->whereHas('locations', function ($query) use ($location) {
                $query->where('id', $location);
            })*/
            ;
        }]);
    }

    public function getpartnerServicePartnerDiscount($services)
    {
        return $services->load(['partnerServices' => function ($q) {
            $q->published()
                ->with(['partner' => function ($q) {
                    $q->published()->with('walletSetting');
                }])
                ->with(['discounts' => function ($q) {
                    $q->where([
                        ['start_date', '<=', Carbon::now()],
                        ['end_date', '>=', Carbon::now()]
                    ]);
                }]);
        }]);
    }

    private function _getDiscount($service)
    {
        $service['discount'] = false;
        $partner_services = $service->partnerServices;
        foreach ($partner_services as $partner_service) {
            if ($partner_service->partner != null) {
                if (count($partner_service->discounts) != 0) {
                    $service['discount'] = true;
                    break;
                }
            }
        }
        return $service;
    }

    public function getServiceScope($scope)
    {
        $final_scope = [];
        $scope_requested = explode(',', $scope);
        if (count($scope_requested) != 0) {
            foreach ($scope_requested as $key => $value) {
                if (in_array(trim($value), ['reviews', 'start_price', 'discount'])) {
                    array_push($final_scope, $scope_requested[$key]);
                }
            }
        }
        return $final_scope;
    }

    public function getFirstOption($service)
    {
        $variables = json_decode($service->variables);
        $first_option = array_map('intval', explode(',', key($variables->prices)));
        return $first_option;
    }
}
