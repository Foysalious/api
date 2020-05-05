<?php namespace Sheba\Checkout\PriceBreakdownCalculators;


use App\Models\LocationService;
use App\Models\ServiceSubscriptionDiscount;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Checkout\Services\ServiceWithPrice;
use Sheba\Checkout\Services\SubscriptionServicePricingAndBreakdown;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;

class SubscriptionPriceBreakdownCalculator extends PriceBreakdownCalculator
{
    /** @var PriceCalculation */
    private $priceCalculator;

    public function __construct(JobDiscountHandler $job_discount_handler, DeliveryCharge $delivery_charge,
                                PriceCalculation $price_calculator)
    {
        parent::__construct($job_discount_handler, $delivery_charge);
        $this->priceCalculator = $price_calculator;
    }

    /**
     * @return SubscriptionServicePricingAndBreakdown
     */
    public function calculate()
    {
        $price = new SubscriptionServicePricingAndBreakdown();
        $date_count = count($this->request->scheduleDate);
        foreach ($this->request->selectedServices as $selected_service) {
            $unit_price = $this->getUnitPrice($selected_service);
            $original_price = $unit_price * $selected_service->quantity * $date_count;
            /** @var ServiceSubscriptionDiscount $discount */
            $discount = $selected_service->serviceModel->subscription->getDiscount($this->request->subscriptionType, $date_count);
            $discounted_amount = !$discount ? 0 : $discount->getApplicableAmount($original_price, $selected_service->quantity);
            $discounted_price = $original_price - $discounted_amount;

            $service = (new ServiceWithPrice($selected_service->serviceModel));
            $service->setOption($selected_service->option)->setQuantity($selected_service->quantity)
                ->setDiscount($discounted_amount);

            if($discount) {
                $service->setCap($discount->cap)->setAmount($discount->discount_amount)
                    ->setIsPercentage($discount->isPercentage() ? 1 : 0)
                    ->setShebaContribution($discount->sheba_contribution)->setPartnerContribution($discount->partner_contribution);
            } else {
                $service->setCap(0)->setAmount(0)->setIsPercentage(0)
                    ->setShebaContribution(0)->setPartnerContribution(0);
            }

            $service->setDiscountedPrice($discounted_price)->setOriginalPrice($original_price)->setUnitPrice($unit_price)
                ->setMinPrice(0)->setIsMinPriceApplied(0);

            $price->addService($service);
        }

        $price->setTotalQuantity(count($this->request->scheduleDate))
            ->setHasHomeDelivery((int)$this->request->selectedCategory->is_home_delivery_applied ? 1 : 0)
            ->setHasPremiseAvailable((int)$this->request->selectedCategory->is_partner_premise_applied ? 1 : 0);

        return $price;
    }

    private function getUnitPrice($selected_service)
    {
        $location_service = LocationService::where([
            ['service_id', $selected_service->id], ['location_id', $this->request->getLocationId()]
        ])->first();
        $this->priceCalculator->setLocationService($location_service)->setOption($selected_service->option);
        return $this->priceCalculator->getUnitPrice();
    }
}
