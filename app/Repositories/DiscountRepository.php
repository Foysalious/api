<?php

namespace App\Repositories;

use App\Models\PartnerService;

class DiscountRepository
{
    public function addDiscountToPartnerForService($partner)
    {

        if (($discount = PartnerService::find($partner->pivot->id)->discount()) == null) {
            //initially discount set to zero
            array_add($partner, 'discount_price', 0);
            array_add($partner, 'discounted_price', 0);
        } else {
            $partner['discount_price']=$discount->amount;
            $partner['discounted_price']=$partner->prices - $discount->amount;
            $partner['discount_id']=$discount->id;
        }
        return $partner;
    }

}