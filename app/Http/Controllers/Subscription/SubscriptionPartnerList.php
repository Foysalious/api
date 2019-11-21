<?php namespace App\Http\Controllers\Subscription;

use App\Sheba\Checkout\PartnerList;
use Carbon\Carbon;
use Sheba\Checkout\PartnerPricingBreakdownCalculator;
use Sheba\Checkout\Services\ServiceWithPrice;
use Sheba\Checkout\SubscriptionPartnerPricingBreakdownCalculator;
use Sheba\Checkout\SubscriptionPrice;
use Sheba\Dal\Discount\DiscountTypes;

class SubscriptionPartnerList extends PartnerList
{
    public function __construct()
    {
        parent::__construct();
        $this->priceBreakdownCalculator = app(SubscriptionPartnerPricingBreakdownCalculator::class);
    }
}
