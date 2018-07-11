<?php

namespace App\Sheba\Partner;


use App\Models\Partner;

class PartnerSubscriptionBillingCycle
{
    private $partner;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function run()
    {
        if ($this->partner->verified_at == null) {
            $partner_subscription = $this->partner->subscription;
            $billing_type = $this->partner->billing_type;
            $package_price = (double)json_decode($partner_subscription->rules)->fee->$billing_type;
            if ($this->partner->discount_id) {
                $subsciption_discount = $this->partner->subscriptionDiscount;
                if ($subsciption_discount->is_percentage) {
                    $discount = $package_price * ($subsciption_discount->amount / 100);
                } else {
                    $discount = $subsciption_discount->amount;
                }
                $package_price -= $discount;
            }
            dd($package_price);
        }
    }
}