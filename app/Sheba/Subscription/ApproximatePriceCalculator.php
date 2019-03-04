<?php namespace Sheba\Subscription;

use App\Models\Service;
use App\Models\ServiceSubscription;
use App\Models\ServiceSubscriptionDiscount;
use App\Sheba\Checkout\Discount;

class ApproximatePriceCalculator extends Discount
{
    /**
     * @var ServiceSubscription $subscription
     */
    private $subscription;

    /**
     * @var Service $service
     */
    private $service;

    /**
     * @param ServiceSubscription $subscription
     * @throws \Exception
     */
    public function setSubscription($subscription)
    {
        if($subscription->service) {
            $this->subscription = $subscription;
            $this->setService($subscription->service);
            return $this;
        }
        throw new \Exception('Subscription has no services');
    }

    /**
     * @param Service $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    public function getPriceRange()
    {
        $partner_price_range_for_service = $this->getMinMaxPartnerPrice();
        $subscription_type = $this->getPreferredSubscriptionType();
        $min_subscription_quantity = $this->getMinSubscriptionQuantity($subscription_type);
        $discounted_subscription_price = $this->calculateServiceDiscount();
        $price_list = [
            'min_price' => (( $partner_price_range_for_service[1] - $discounted_subscription_price ) * $min_subscription_quantity * $this->service->min_quantity),
            'max_price' => (( $partner_price_range_for_service[0] - $discounted_subscription_price ) * $min_subscription_quantity * $this->service->min_quantity),
            'price_applicable_for' => $subscription_type
        ];
        return $price_list;
    }

    protected function calculateServiceDiscount()
    {
        /** @var  $service_subscription ServiceSubscription */
        $service_subscription = $this->service->subscription;
        /** @var  $discount ServiceSubscriptionDiscount */
        $discount = $service_subscription->discounts()->where('subscription_type', 'monthly')->valid()->first();
       // dd($discount);
        if ($discount) {
            $this->hasDiscount = 1;
            $this->cap = (double)$discount->cap;
            $this->amount = (double)$discount->discount_amount;
            $this->sheba_contribution = (double)$discount->sheba_contribution;
            $this->partner_contribution = (double)$discount->partner_contribution;
            if ($discount->isPercentage()) {
                $this->discount_percentage = (double)$discount->discount_amount;
                $this->isDiscountPercentage = 1;
                $this->discount = ($discount->discount_amount) / 100;
                if ($discount->hasCap() && $this->discount > $discount->cap) $this->discount = $discount->cap;
            } else {
                $this->discount = $discount->discount_amount;
            }
        }
        return $this->discount;
    }

    private function getPreferredSubscriptionType()
    {
        if($this->subscription->is_weekly)
            return 'weekly';
        else
            return 'monthly';
    }

    private function getMinSubscriptionQuantity($subscription_type)
    {
        if($subscription_type === 'weekly')
            return $this->subscription->min_weekly_qty;
        else
            return $this->subscription->min_monthly_qty;
    }

    private function getMinMaxPartnerPrice()
    {
        try {
            $max_price = [];
            $min_price = [];
            if ($this->service->partners->count() == 0) return array(0, 0);
            foreach ($this->service->partners->where('status', 'Verified') as $partner) {
                $partner_service = $partner->pivot;
                if (!($partner_service->is_verified && $partner_service->is_published)) continue;
                $prices = (array)json_decode($partner_service->prices);
                $max = max($prices);
                $min = min($prices);
                array_push($max_price, $max);
                array_push($min_price, $min);
            }
            return array((double)max($max_price) * $this->service->min_quantity, (double)min($min_price) * $this->service->min_quantity);
        } catch (\Throwable $e) {
            return array(0, 0);
        }
    }
}