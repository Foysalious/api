<?php

namespace App\Repositories;

use Sheba\Dal\PartnerService\PartnerService;
use App\Models\PartnerServiceDiscount;

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
            $partner_service['discounted_price'] = (double)$partner_service->prices;
            $partner_service['cap'] = 0;
            $partner_service['discount_id'] = null;
        } /**
         * partner service has discount
         */
        else {
            /**
             * discount is in percentage
             */
            if ($discount->is_amount_percentage) {
                $amount = ((double)$partner_service->prices * $discount->amount) / 100;
                if ($discount->cap != 0 && $amount > (double)$discount->cap) {
                    $amount = $discount->cap;
                }
                $partner_service['cap'] = (double)$discount->cap;
                $partner_service['discount_price'] = (double)$amount;
                $partner_service['discounted_price'] = (double)($partner_service->prices - $amount);
            } else {
                $partner_service['cap'] = 0;
                $partner_service['discount_price'] = (double)$discount->amount;
                $partner_service['discounted_price'] = (double)($partner_service->prices - $discount->amount);
            }
            if ($partner_service['discounted_price'] < 0) {
                $partner_service['discounted_price'] = 0;
            }
            $partner_service['discount_id'] = $discount->id;
            $partner_service['sheba_contribution'] = $discount->sheba_contribution;
            $partner_service['partner_contribution'] = $discount->partner_contribution;
        }
        return $partner_service;
    }

    /**
     * get discount amount for service or voucher
     * @param $discount
     * @param $partnerPrice
     * @param $quantity
     * @return double
     * @internal param $hasPercentage
     * @internal param $discountValue
     */
    public function getDiscountAmount($discount, $partnerPrice, $quantity)
    {
        if ($discount['is_percentage']) {
            $amount = ((double)$partnerPrice * $quantity) * ($discount['voucher']['amount'] / 100);
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

    public function getServiceDiscountAmount(PartnerServiceDiscount $discount, $partnerPrice, $quantity)
    {
        if ($discount->is_amount_percentage) {
            $amount = ((double)$partnerPrice * $quantity * $discount->amount) / 100;
            if ($discount->cap != 0 && $amount > $discount->cap) {
                $amount = $discount->cap;
            }
            return $amount;
        } else {
            return $this->validateDiscountValue($partnerPrice * $quantity, $discount->amount * $quantity);
        }
    }

    public function getServiceDiscountValues($discount, float $price, float $quantity)
    {
        $discount_price = 0;
        $priceWithDiscount = $priceWithoutDiscount = $price * $quantity;
        if ($discount) {
            if ($discount->is_amount_percentage) {
                $discount_price = ($price * $quantity * $discount->amount) / 100;
                if ($discount->cap != 0 && $discount_price > $discount->cap) {
                    $discount_price = $discount->cap;
                }
                $priceWithDiscount = ($price - $discount_price);
            } else {
                $discount_price = $this->validateDiscountValue($price * $quantity, $discount->amount * $quantity);
                $priceWithDiscount = $price - $discount_price;
            }
        }
        return array((double)$discount_price, (double)$priceWithDiscount, (double)$priceWithoutDiscount);
    }
}