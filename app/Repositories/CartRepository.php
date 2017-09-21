<?php

namespace App\Repositories;


use App\Models\PartnerService;
use App\Models\Quotation;

class CartRepository
{
    private $discountRepository;

    public function __construct()
    {
        $this->discountRepository = new DiscountRepository();
    }

    public function checkValidation($cart, $location)
    {
        $items = $cart->items;
        foreach ($items as $item) {
            if (!$this->_validTime($item->time)) {
                return array(false, 'Time is not valid');
            }
            if (!$this->_validDate($item->date)) {
                return array(false, 'Date is not valid');
            }
            if (($partner_service = $this->_validPartnerService($item)) == null) {
                return array(false, 'Partner Service not valid');
            }
            unset($item->service);
            $item->service = $partner_service->service;
            if ($partner_service->partner == null) {
                return array(false, 'Partner not Verified!');
            }
            if (!$this->_validPartnerLocation($location, $partner_service->partner)) {
                return array(false, 'Partner Location not valid');
            }
            if ($partner_service->service->variable_type == 'Custom') {
                $price = $this->_validateQuotePrice($item->partner->quote_id);
                if ($price != false) {
                    $item->partner->prices = $price;
                } else {
                    return array(false, 'Invalid Quotation');
                }
            } else {
                $price = $partner_service->prices;
                if ($partner_service->service->variable_type == 'Options') {
                    $price = $this->_validOption($item->serviceOptions, $partner_service);
                    if (!$price) {
                        return array(false, 'Service Option not valid');
                    }
                }
                unset($item->partner);
                $item->partner = $this->_validatePartnerPrice($price, $partner_service);
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
        if ($date == '')
            return true;
        if (is_object($date)) {
            $date = $date->time;
        }
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

    private function _validatePartnerPrice($price, $partner_service)
    {
        $partner = $partner_service->partner;
        $partner['prices'] = $price;
        $partner['discount'] = $partner_service->discount();
        return $this->discountRepository->addDiscountToPartnerForService($partner, $partner_service->discount());
    }

    private function _validPartnerService($item)
    {
        return PartnerService::with(['partner' => function ($q) {
            $q->select('*')->where('status', 'Verified');
        }])->with(['service' => function ($q) {
            $q->select('*')->publishedForAll();
        }])->with('discounts')->where([
            ['service_id', $item->service->id],
            ['partner_id', $item->partner->id],
            ['is_published', 1]
        ])->first();

    }

    private function _validateQuotePrice($quote_id)
    {
        $quotation = Quotation::find($quote_id);
        return $quotation != null ? $quotation->proposed_price : false;
    }

    public function getPartnerPrice($item)
    {
        $partner_service = $this->_validPartnerService($item);
        if ($partner_service->service->variable_type == 'Custom') {
            $price = $this->_validateQuotePrice($item->partner->quote_id);
            if ($price != false) {
                $item->partner['prices'] = $price;
            }
            return $item->partner;
        } else {
            $price = $partner_service->prices;
            if ($partner_service->service->variable_type == 'Options') {
                $price = $this->_validOption($item->serviceOptions, $partner_service);
            }
            return $this->_validatePartnerPrice($price, $partner_service);
        }

    }

}