<?php

namespace App\Repositories;


use App\Models\PartnerService;

class CartRepository
{
    public function checkValidation($cart, $location)
    {
        $items = $cart->items;
        foreach ($items as $item) {
            if (!$this->_validTime($item->time)) {
                return array(false, 'Time is not valid');
            }
            if (!$this->_validDate($item->date->time)) {
                return array(false, 'Date is not valid');
            }
            $partner_service = PartnerService::with('partner', 'service', 'discounts')->where([
                ['service_id', $item->service->id],
                ['partner_id', $item->partner->id],
            ])->first();
            if ($partner_service == null) {
                return array(false, 'Partner Service not valid');
            }
            unset($item->service);
            $item->service = $partner_service->service;
            if (!$this->_validPartnerLocation($location, $partner_service->partner)) {
                return array(false, 'Partner Location not valid');
            }
            if (count($item->serviceOptions) > 0) {
                if (!$this->_validOption($item->serviceOptions, $partner_service)) {
                    return array(false, 'Service Option not valid');
                }
            } else {
                if (!$this->_validPrice($item->partner, $partner_service)) {
                    return array(false, 'Price Not Valid');
                }
            }
        }
        return $items;
    }

    private function _validTime($time)
    {
        $times = constants('JOB_PREFERRED_TIMES');
        return array_has($times, $time) ? true : false;
    }

    private function _validDate($date)
    {
        $today = date('Y-m-d');
        return $date >= $today ? true : false;
    }

    /**
     * check partner gives service in the location
     * @param $location
     * @param $partner
     * @return bool
     */
    private function _validPartnerLocation($location, $partner)
    {
        $locations = $partner->locations->pluck('id')->toArray();
        return in_array($location, $locations) ? true : false;
    }

    private function _validOption($option, $partner_service)
    {
        $prices = json_decode($partner_service->prices);
        $option = implode(',', $option);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return $price;
            }
        }
        return false;
    }

    private function _validPrice($cart_partner, $partner_service)
    {
        return $cart_partner->prices == $partner_service->prices ? true : false;
    }

}