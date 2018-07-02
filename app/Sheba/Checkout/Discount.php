<?php

namespace App\Sheba\Checkout;

use App\Models\PartnerServiceDiscount;

class Discount
{
    private $discount = 0;
    private $min_price = 0;
    private $discounted_price;
    private $unit_price;
    private $quantity;
    private $original_price;
    private $sheba_contribution = 0;
    private $partner_contribution = 0;
    private $discount_percentage = 0;
    private $amount = 0;
    private $cap = null;
    private $discount_id = null;
    private $hasDiscount = 0;

    public function __construct($unit_price, $quantity = 1, $min_price = 0)
    {
        $this->unit_price = (double)$unit_price;
        $this->quantity = (double)$quantity;
        $this->min_price = (double)$min_price;
        $total = $this->unit_price * $this->quantity;
        $this->original_price = $total < $min_price ? $min_price : $total;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function calculateServiceDiscount(PartnerServiceDiscount $running_discount = null)
    {
        if ($running_discount) {
            $this->hasDiscount = 1;
            $this->discount_id = $running_discount->id;
            $this->cap = (double)$running_discount->cap;
            $this->amount = (double)$running_discount->amount;
            $this->sheba_contribution = (double)$running_discount->sheba_contribution;
            $this->partner_contribution = (double)$running_discount->partner_contribution;
            if ($running_discount->isPercentage()) {
                $this->discount_percentage = 1;
                $this->discount = ($this->original_price * $running_discount->amount) / 100;
                if ($running_discount->hasCap() && $this->discount > $running_discount->cap) {
                    $this->discount = $running_discount->cap;
                }
            } else {
                $this->discount = $this->quantity * $running_discount->amount;
                if ($this->discount > $this->original_price) {
                    $this->discount = $this->original_price;
                }
            }
        }
        $this->discounted_price = $this->original_price - $this->discount;
    }
}