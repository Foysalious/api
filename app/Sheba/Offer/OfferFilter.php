<?php

namespace Sheba\Offer;

use App\Models\Customer;
use App\Models\OfferShowcase;
use Sheba\Voucher\PromotionList;

class OfferFilter
{
    private $offers;
    private $customer;
    private $category;

    public function __construct($offers)
    {
        $this->offers = $offers;
    }

    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function filter()
    {
        foreach ($this->offers as $offer) {
            /** @var OfferShowcase $offer */
            if ($this->customer && $offer->isVoucher()) {
//                $promotion_list = new PromotionList($this->customer);
//                dd($promotion_list->canAdd($offer->voucher));
            }
        }
        return $this->offers;
    }
}