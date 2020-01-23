<?php namespace Sheba\Checkout\PriceBreakdownCalculators;

use App\Models\CategoryPartner;
use App\Models\Partner;
use App\Sheba\Checkout\Discount;
use Carbon\Carbon;
use Sheba\Checkout\Services\ServicePricingAndBreakdown;
use Sheba\Checkout\Services\ServiceWithPrice;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Dal\Discount\InvalidDiscountType;
use Sheba\JobDiscount\JobDiscountCheckingParams;

class PartnerPricingBreakdownCalculator extends PriceBreakdownCalculator
{
    /** @var Partner */
    protected $partner;
    /** @var CategoryPartner */
    protected $categoryPartner;

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        $this->categoryPartner = $this->partner->categories->first()->pivot;
        $this->deliveryCharge->setCategoryPartnerPivot($this->categoryPartner);
        return $this;
    }

    /**
     * @return ServicePricingAndBreakdown
     * @throws InvalidDiscountType
     */
    public function calculate()
    {
        $price = new ServicePricingAndBreakdown();
        foreach ($this->request->selectedServices as $selected_service) {
            $service = $this->partner->services->where('id', $selected_service->id)->first();
            $schedule_date_time = Carbon::parse($this->request->scheduleDate[0] . ' ' . $this->request->scheduleStartTime);
            $discount = new Discount();
            $discount->setServiceObj($selected_service)->setServicePivot($service->pivot)->setScheduleDateTime($schedule_date_time)
                ->setDiscounts($this->partner->discounts)->setSurcharges($this->partner->surcharges)->initialize();

            $service = (new ServiceWithPrice($selected_service->serviceModel))
                ->setDiscount($discount->discount)->setCap($discount->cap)
                ->setIsPercentage($discount->isDiscountPercentage)->setDiscountedPrice(floor($discount->discounted_price))
                ->setOriginalPrice(floor($discount->original_price))->setMinPrice($discount->min_price)
                ->setUnitPrice(floor($discount->unit_price))->setAmount($discount->amount)
                ->setShebaContribution($discount->sheba_contribution)->setPartnerContribution($discount->partner_contribution)
                ->setIsMinPriceApplied($discount->original_price == $discount->min_price ? 1 : 0)
                ->setQuantity($selected_service->quantity)->setOption($selected_service->option);

            $price->addService($service);
        }

        list($original_delivery_charge, $discounted_delivery_charge) = $this->getDeliveryCharges($price->getDiscountedPrice());
        $price->setDeliveryCharge($original_delivery_charge)->setDiscountedDeliveryCharge($discounted_delivery_charge)
            ->setHasHomeDelivery((int)$this->categoryPartner->is_home_delivery_applied ? 1 : 0)
            ->setHasPremiseAvailable((int)$this->categoryPartner->is_partner_premise_applied ? 1 : 0);
        return $price;
    }

    /**
     * @param $total_price
     * @return array
     * @throws InvalidDiscountType
     */
    protected function getDeliveryCharges($total_price)
    {
        $original_delivery_charge = $this->deliveryCharge->get();
        $discount_amount = 0;
        $discount_checking_params = (new JobDiscountCheckingParams())
            ->setDiscountableAmount($original_delivery_charge)->setOrderAmount($total_price);
        $this->jobDiscountHandler->setType(DiscountTypes::DELIVERY)->setCategory($this->request->selectedCategory)
            ->setPartner($this->partner)->setCheckingParams($discount_checking_params)->calculate();

        if ($this->jobDiscountHandler->hasDiscount()) {
            $discount_amount += $this->jobDiscountHandler->getApplicableAmount();
        }

        $discounted_delivery_charge = $original_delivery_charge - $discount_amount;

        return [$original_delivery_charge, $discounted_delivery_charge];
    }
}
