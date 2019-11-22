<?php namespace App\Http\Controllers\Subscription;

use App\Sheba\Checkout\PartnerList;
use Sheba\Checkout\PriceBreakdownCalculators\SubscriptionPartnerPricingBreakdownCalculator;

class SubscriptionPartnerList extends PartnerList
{
    public function __construct()
    {
        parent::__construct();
        $this->priceBreakdownCalculator = app(SubscriptionPartnerPricingBreakdownCalculator::class);
    }
}
