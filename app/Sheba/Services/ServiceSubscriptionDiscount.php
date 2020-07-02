<?php namespace Sheba\Services;

use App\Models\ServiceSubscriptionDiscount as ServiceSubscriptionDiscountModel;

class ServiceSubscriptionDiscount
{
    private $serviceSubscriptionDiscount;

    public function setServiceSubscriptionDiscount(ServiceSubscriptionDiscountModel $serviceSubscriptionDiscount)
    {
        $this->serviceSubscriptionDiscount = $serviceSubscriptionDiscount;
        return $this;
    }

    public function getDiscountText()
    {
        $discount_text = "Save ";
        $discount_amount = $this->serviceSubscriptionDiscount->is_discount_amount_percentage ? $this->serviceSubscriptionDiscount->discount_amount . '%' : 'BDT ' . $this->serviceSubscriptionDiscount->discount_amount;
        $discount_text .= $discount_amount;
        if($this->serviceSubscriptionDiscount->cap != 0) $discount_text .= " upto BDT " .$this->serviceSubscriptionDiscount->cap;
        $discount_text .= " on every order!";
        return $discount_text;
    }
}