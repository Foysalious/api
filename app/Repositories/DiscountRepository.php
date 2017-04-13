<?php

namespace App\Repositories;

use App\Models\PartnerService;

class DiscountRepository
{
    public function addDiscountToPartnerForService($partner)
    {
        /**
         * partner service has no discount
         */
        if (($discount = PartnerService::find($partner->pivot->id)->discount()) == null) {
            //initially discount set to zero
            array_add($partner, 'discount_price', 0);
            array_add($partner, 'discounted_price', $partner->prices);
        } /**
         * partner service has discount
         */
        else {
            /**
             * discount is in percentage
             */
            if ($discount->is_amount_percentage) {
                $amount = ((float)$partner->prices * $discount->amount) / 100;
                $partner['discount_price'] = $amount;
                $partner['discounted_price'] = $partner->prices - $amount;
            } else {
                $partner['discount_price'] = $discount->amount;
                $partner['discounted_price'] = $partner->prices - $discount->amount;
                $partner['discount_id'] = $discount->id;
            }
            $partner['discount_id'] = $discount->id;
        }
        return $partner;
    }

    /**
     * get discount amount for service or voucher
     * @param $hasPercentage
     * @param $partnerPrice
     * @param $discountValue
     * @return float
     */
    public function getDiscountAmount($hasPercentage, $partnerPrice, $discountValue)
    {
        if ($hasPercentage) {
            return ((float)$partnerPrice * $discountValue) / 100;
        } else {
            return $discountValue;
        }
    }

}