<?php namespace Sheba\Checkout\PriceBreakdownCalculators;


use Sheba\Checkout\DeliveryCharge;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Checkout\Services\ServicePricingAndBreakdown;
use Sheba\JobDiscount\JobDiscountHandler;

abstract class PriceBreakdownCalculator
{
    /** @var JobDiscountHandler */
    protected $jobDiscountHandler;
    /** @var DeliveryCharge */
    protected $deliveryCharge;

    /** @var PartnerListRequest */
    protected $request;

    public function __construct(JobDiscountHandler $job_discount_handler, DeliveryCharge $delivery_charge)
    {
        $this->jobDiscountHandler = $job_discount_handler;
        $this->deliveryCharge = $delivery_charge;
    }

    public function setPartnerListRequest(PartnerListRequest $request)
    {
        $this->request = $request;
        $this->deliveryCharge->setCategory($this->request->selectedCategory);
        return $this;
    }

    /**
     * @return ServicePricingAndBreakdown
     */
    abstract public function calculate();
}
