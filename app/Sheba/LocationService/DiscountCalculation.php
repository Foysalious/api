<?php namespace Sheba\LocationService;

use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;

class DiscountCalculation
{
    /** @var LocationService $locationService */
    private $locationService;
    /** @var ServiceDiscount $serviceDiscount */
    private $serviceDiscount;
    private $originalPrice;
    private $discountedPrice;
    private $discount;
    private $isDiscountPercentage;
    private $cap;
    private $shebaContribution;
    private $partnerContribution;
    private $quantity;
    private $service;

    public function __construct()
    {
        $this->shebaContribution = 0;
        $this->partnerContribution = 0;
        $this->discount = 0;
        $this->quantity = 1;
    }

    /**
     * @param LocationService $location_service
     * @return $this
     */
    public function setLocationService(LocationService $location_service)
    {
        $this->locationService = $location_service;
        return $this;
    }

    /**
     * @param Service $service
     * @return $this
     */
    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountedPrice()
    {
        return (double)$this->discountedPrice;
    }

    /**
     * @return mixed
     */
    public function getDiscount()
    {
        return (double)$this->discount;
    }

    /**
     * @return mixed
     */
    public function getCalculatedDiscount()
    {
        if (!$this->serviceDiscount->isPercentage())
            return $this->discount;
        $this->cap = (double)$this->serviceDiscount->cap;
        $discount = ($this->originalPrice * $this->discount) / 100;
        $discount = ($this->cap && $discount > $this->cap) ? $this->cap : $discount;
        return $discount;
    }

    /**
     * @return mixed
     */
    public function getIsDiscountPercentage()
    {
        return $this->isDiscountPercentage;
    }

    /**
     * @return mixed
     */
    public function getCap()
    {
        return $this->cap;
    }

    /**
     * @return mixed
     */
    public function getOriginalPrice()
    {
        return (double)$this->originalPrice;
    }

    public function setOriginalPrice($original_price)
    {
        $this->originalPrice = $original_price;
        $this->discountedPrice = $original_price;
        return $this;
    }


    /**
     * @return ServiceDiscount
     */
    public function getLocationServiceDiscount()
    {
        return $this->serviceDiscount;
    }

    /**
     * @return mixed
     */
    public function getShebaContribution()
    {
        return $this->shebaContribution;
    }

    /**
     * @return mixed
     */
    public function getPartnerContribution()
    {
        return $this->partnerContribution;
    }

    public function getDiscountId()
    {
        return $this->serviceDiscount ? $this->serviceDiscount->id : null;
    }

    public function getTotalDiscountAmount()
    {
        return $this->originalPrice - $this->discountedPrice;
    }

    public function calculate()
    {
        $this->serviceDiscount = $this->locationService->discounts()->running()->first();
        $this->serviceDiscount = $this->serviceDiscount ? $this->serviceDiscount : $this->service->serviceDiscounts()->running()->first();
        if (!$this->serviceDiscount) return;
        $this->discountedPrice = $this->calculateDiscountedPrice();
        $this->discountedPrice = $this->discountedPrice < 0 ? 0 : $this->discountedPrice;
    }

    private function calculateDiscountedPrice()
    {
        $this->discount = $this->serviceDiscount->amount;
        $this->isDiscountPercentage = $this->serviceDiscount->is_percentage;
        $this->shebaContribution = $this->serviceDiscount->sheba_contribution;
        $this->partnerContribution = $this->serviceDiscount->partner_contribution;
        $discount = $this->getCalculatedDiscount();
        return $this->originalPrice - $discount;
    }

    private function setDiscountedPriceUptoCap()
    {
        $this->cap = (double)$this->serviceDiscount->cap;
        $this->discountedPrice = ($this->cap && $this->discountedPrice > $this->cap) ? $this->cap : $this->discountedPrice;
    }

    public function getJobServiceDiscount()
    {
        if (!$this->serviceDiscount) return 0;
        if (!$this->serviceDiscount->isPercentage()) return (double)$this->serviceDiscount->amount * $this->quantity;
        $discount = ($this->originalPrice * $this->serviceDiscount->amount) / 100;
        if ($this->serviceDiscount->cap && $discount > $this->serviceDiscount->cap) $discount = $this->serviceDiscount->cap;
        return $discount;
    }
}
