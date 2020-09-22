<?php namespace Sheba\Checkout\PriceBreakdownCalculators;

use Carbon\Carbon;
use Sheba\Checkout\Services\ServiceWithPrice;
use Sheba\Checkout\Services\SubscriptionServicePricingAndBreakdown;
use Sheba\Checkout\SubscriptionPrice;
use Sheba\Dal\Discount\InvalidDiscountType;

class SubscriptionPartnerPricingBreakdownCalculator extends PartnerPricingBreakdownCalculator
{
    /**
     * @return SubscriptionServicePricingAndBreakdown
     * @throws InvalidDiscountType
     */
    public function calculate()
    {
        $price = new SubscriptionServicePricingAndBreakdown();
        $date_count = count($this->request->scheduleDate);
        foreach ($this->request->selectedServices as $selected_service) {
            $service = $this->partner->services->where('id', $selected_service->id)->first();
            $service_pivot = $service->pivot;
            foreach($this->request->scheduleDate as $date) {
                $discount = new SubscriptionPrice();
                $schedule_date_time = Carbon::parse($date . ' ' . $this->request->scheduleStartTime);
                $discount->setType($this->request->subscriptionType)->setServiceObj($selected_service)
                    ->setServicePivot($service_pivot)->setScheduleDateTime($schedule_date_time)->setScheduleDateQuantity($date_count)->initialize();

                $service = (new ServiceWithPrice($selected_service->serviceModel))
                    ->setDiscount($discount->discount)->setCap($discount->cap)->setAmount($discount->amount)
                    ->setIsPercentage($discount->isDiscountPercentage)->setDiscountedPrice($discount->discounted_price)
                    ->setOriginalPrice($discount->original_price)->setMinPrice($discount->min_price)->setUnitPrice($discount->unit_price)
                    ->setShebaContribution($discount->sheba_contribution)->setPartnerContribution($discount->partner_contribution)
                    ->setIsMinPriceApplied($discount->original_price == $discount->min_price ? 1 : 0);
            }
            $service->setOption($selected_service->option)->setQuantity($selected_service->quantity);
            $price->addService($service);
        }

        list($original_delivery_charge, $discounted_delivery_charge) = $this->getDeliveryCharges($price->getDiscountedPrice());
        $price->setDeliveryCharge($original_delivery_charge)->setDiscountedDeliveryCharge($discounted_delivery_charge)
            ->setTotalQuantity(count($this->request->scheduleDate))
            ->setHasHomeDelivery((int)$this->categoryPartner->is_home_delivery_applied ? 1 : 0)
            ->setHasPremiseAvailable((int)$this->categoryPartner->is_partner_premise_applied ? 1 : 0);

        return $price;
    }
}
