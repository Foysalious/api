<?php

namespace App\Repositories;

use App\Models\PartnerService;

class DiscountRepository
{
    public function addDiscountToPartnerForService($partner_service, $discount)
    {
        /**
         * partner service has no discount
         */
        if ($discount == null) {
            //initially discount set to zero
            $partner_service['discount_price'] = 0;
            $partner_service['discounted_price'] = $partner_service->prices;
        } /**
         * partner service has discount
         */
        else {
            /**
             * discount is in percentage
             */
            if ($discount->is_amount_percentage) {
                $amount = ((float)$partner_service->prices * $discount->amount) / 100;
                if ($discount->cap != 0 && $amount > (float)$discount->cap) {
                    $amount = $discount->cap;
                }
                $partner_service['cap'] = $discount->cap;
                $partner_service['discount_price'] = $amount;
                $partner_service['discounted_price'] = $partner_service->prices - $amount;
            } else {
                $partner_service['cap'] = null;
                $partner_service['discount_price'] = $discount->amount;
                $partner_service['discounted_price'] = $partner_service->prices - $discount->amount;
                $partner_service['discount_id'] = $discount->id;
            }
            if ($partner_service['discounted_price'] < 0) {
                $partner_service['discounted_price'] = 0;
            }
            $partner_service['discount_id'] = $discount->id;
        }
        return $partner_service;
    }

    /**
     * get discount amount for service or voucher
     * @param $discount
     * @param $partnerPrice
     * @param $quantity
     * @return float
     * @internal param $hasPercentage
     * @internal param $discountValue
     */
    public function getDiscountAmount($discount, $partnerPrice, $quantity)
    {
        if ($discount['is_percentage']) {
            $amount = ((float)$partnerPrice * $quantity) * ($discount['voucher']['amount'] / 100);
            if ($discount['voucher']->cap != 0 && $amount > $discount['voucher']->cap) {
                $amount = $discount['voucher']->cap;
            }
            return $amount;
        } else {
            return $this->validateDiscountValue($partnerPrice * $quantity, $discount['voucher']['amount']);
        }
    }

    public function validateDiscountValue($service_price, $discountValue)
    {
        return $service_price < $discountValue ? $service_price : $discountValue;
    }

    public function getServiceDiscountAmount($discount, $partnerPrice, $quantity)
    {
        if ($discount->is_amount_percentage) {
            $amount = ((float)$partnerPrice * $quantity * $discount->amount) / 100;
            if ($discount->cap != 0 && $amount > $discount->cap) {
                $amount = $discount->cap;
            }
            return $amount;
        } else {
            return $this->validateDiscountValue($partnerPrice * $quantity, $discount->amount * $quantity);
        }
    }

}