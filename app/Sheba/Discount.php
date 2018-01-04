<?php

namespace App\Sheba;

use App\Models\PartnerServiceDiscount;

class Discount
{
    public $discount;
    public $discounted_price;
    private $unit_price;
    private $quantity;
    private $total_price;

    public function __construct($unit_price, $quantity = 1)
    {
        $this->unit_price = (double)$unit_price;
        $this->quantity = (double)$quantity;
        $this->total_price = $this->unit_price * $this->quantity;
    }

    public function calculateServiceDiscount(PartnerServiceDiscount $running_discount)
    {
        if ($running_discount->isPercentage()) {
            $this->discount = ($this->unit_price * $this->quantity * $running_discount->amount) / 100;
            if ($running_discount->hasCap() && $this->discount > $running_discount->cap) {
                $this->discount = $running_discount->cap;
            }
        } else {
            $this->discount = $this->quantity * $running_discount->amount;
            if ($this->discount > $this->total_price) {
                $this->discount = $this->total_price;
            }
        }
        $this->discounted_price = $this->total_price - $this->discount;
    }


}