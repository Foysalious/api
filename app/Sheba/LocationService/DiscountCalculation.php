<?php namespace Sheba\LocationService;


use App\Models\LocationService;
use Sheba\Dal\LocationServiceDiscount\Model as LocationServiceDiscount;

class DiscountCalculation
{
    /** @var LocationService */
    private $locationService;
    /** @var LocationServiceDiscount */
    private $locationServiceDiscount;
    private $originalPrice;
    private $discountedPrice;
    private $discount;
    private $isDiscountPercentage;
    private $cap;
    private $shebaContribution;
    private $partnerContribution;

    public function setLocationService($location_service)
    {
        $this->locationService = $location_service;
        return $this;
    }

    public function setOriginalPrice($original_price)
    {
        $this->originalPrice = $original_price;
        $this->discountedPrice = $original_price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountedPrice()
    {
        return $this->discountedPrice;
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

    /**
     * @return LocationServiceDiscount
     */
    public function getLocationServiceDiscount()
    {
        return $this->locationServiceDiscount;
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
        return $this->locationServiceDiscount ? $this->locationServiceDiscount->id : null;
    }

    public function calculate()
    {
        $this->locationServiceDiscount = $this->locationService->discounts()->running()->first();
        if (!$this->locationServiceDiscount) return;
        $this->discountedPrice = $this->calculateDiscountedPrice();
        $this->discountedPrice = $this->discountedPrice < 0 ? 0 : $this->discountedPrice;
        $this->setDiscountedPriceUptoCap();
    }

    private function calculateDiscountedPrice()
    {
        $this->discount = $this->locationServiceDiscount->amount;
        $this->isDiscountPercentage = $this->locationServiceDiscount->is_percentage;
        $this->shebaContribution = $this->locationServiceDiscount->sheba_contribution;
        $this->partnerContribution = $this->locationServiceDiscount->partner_contribution;
        if (!$this->locationServiceDiscount->isPercentage()) return $this->originalPrice - $this->discount;
        return $this->originalPrice - (($this->originalPrice * $this->discount) / 100);
    }

    private function setDiscountedPriceUptoCap()
    {
        $this->cap = $this->locationServiceDiscount->cap;
        $this->discountedPrice = ($this->cap && $this->discountedPrice > $this->cap) ? $this->cap : $this->discountedPrice;
    }
}