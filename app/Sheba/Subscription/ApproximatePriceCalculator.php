<?php namespace Sheba\Subscription;

use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Dal\ServiceSubscription\ServiceSubscription;
use Sheba\Dal\ServiceSubscriptionDiscount\ServiceSubscriptionDiscount;
use App\Sheba\Checkout\Discount;
use Exception;
use Sheba\Service\MinMaxPrice;
use Throwable;

class ApproximatePriceCalculator extends Discount
{
    /**
     * @var ServiceSubscription $subscription
     */
    private $subscription;
    private $locationService;

    /**
     * @var Service $service
     */
    private $service;


    /**
     * @param ServiceSubscription $subscription
     * @return ApproximatePriceCalculator
     * @throws Exception
     */
    public function setSubscription($subscription)
    {
        if ($subscription->service) {
            $this->subscription = $subscription;
            $this->setService($subscription->service);
            return $this;
        }
        throw new Exception('Subscription has no services');
    }

    /**
     * @param Service $service
     * @return ApproximatePriceCalculator
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function setLocationService(LocationService $location_service)
    {
        $this->locationService = $location_service;
        return $this;
    }

    public function getDiscountAmount()
    {
        return $this->calculateServiceDiscount();
    }

    public function getSubscriptionType()
    {
        return $this->getPreferredSubscriptionType();
    }

    public function getMinSubscriptionQuantityBySubscriptionType()
    {
        $subscription_type = $this->getPreferredSubscriptionType();
        return $this->getMinSubscriptionQuantity($subscription_type);
    }

    public function getPriceRange()
    {
        $partner_price_range_for_service = $this->getMaxMinForService();
        $subscription_type = $this->getPreferredSubscriptionType();
        $min_subscription_quantity = $this->getMinSubscriptionQuantity($subscription_type);
        $discounted_subscription_price = $this->calculateServiceDiscount();
        return [
            'min_price' => ($partner_price_range_for_service[1] - $discounted_subscription_price),
            'max_price' => ($partner_price_range_for_service[0] - $discounted_subscription_price),
            'price_applicable_for' => $subscription_type
        ];
    }

    protected function calculateServiceDiscount()
    {
        /** @var  $service_subscription ServiceSubscription */
        $service_subscription = $this->service->subscription;
        /** @var  $discount ServiceSubscriptionDiscount */
        $discount = $service_subscription->discounts()->where('subscription_type', $this->getPreferredSubscriptionType())->valid()->first();
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
        if ($this->subscription->is_weekly)
            return 'weekly';
        else if ($this->subscription->is_yearly)
            return 'yearly';
        else
            return 'monthly';
    }

    private function getMinSubscriptionQuantity($subscription_type)
    {
        if ($subscription_type === 'weekly')
            return $this->subscription->min_weekly_qty;
        else if ($subscription_type === 'yearly')
            return $this->subscription->min_yearly_qty;
        else
            return $this->subscription->min_monthly_qty;
    }

    public function getMinMaxPartnerPrice()
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
            return array((double)max($max_price), (double)min($min_price));
        } catch (Throwable $e) {
            return array(0, 0);
        }
    }

    public function getMaxMinForService()
    {
        $max_min = new MinMaxPrice();
        $max_min->setService($this->service)->setLocationService($this->locationService);
        $max = $max_min->getMax();
        $min = $max_min->getMin();
        return array($max, $min);
    }

}
