<?php namespace Sheba\LocationService;

use App\Models\LocationService;
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

    public function __construct()
    {
        $this->shebaContribution = 0;
        $this->partnerContribution = 0;
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
        return $this->discount;
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
        return $this->originalPrice;
    }

    public function setOriginalPrice($original_price)
    {
        $this->originalPrice = $original_price;
        $this->discountedPrice = $original_price;
        return $this;
    }

    public function setQuantity($quantity = 1)
    {
        $this->quantity = $quantity;
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

    public function calculate()
    {
        $this->serviceDiscount = $this->locationService->discounts()->running()->first();
        if (!$this->serviceDiscount) return;
        $this->discountedPrice = $this->calculateDiscountedPrice();
        $this->discountedPrice = $this->discountedPrice < 0 ? 0 : $this->discountedPrice;
        $this->setDiscountedPriceUptoCap();
    }

    private function calculateDiscountedPrice()
    {
        $this->discount = $this->serviceDiscount->amount;
        $this->isDiscountPercentage = $this->serviceDiscount->is_percentage;
        $this->shebaContribution = $this->serviceDiscount->sheba_contribution;
        $this->partnerContribution = $this->serviceDiscount->partner_contribution;
        $this->originalPrice = $this->originalPrice * $this->quantity;

        if (!$this->serviceDiscount->isPercentage())
            return $this->originalPrice - $this->discount;

        return $this->originalPrice - (($this->originalPrice * ($this->discount * $this->quantity)) / 100);
    }

    private function setDiscountedPriceUptoCap()
    {
        $this->cap = $this->serviceDiscount->cap;
        $this->discountedPrice = ($this->cap && $this->discountedPrice > $this->cap) ? $this->cap : $this->discountedPrice;
    }
}
