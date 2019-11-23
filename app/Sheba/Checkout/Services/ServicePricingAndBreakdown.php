<?php namespace Sheba\Checkout\Services;

class ServicePricingAndBreakdown
{
    protected $discount = 0;
    protected $discountedPrice = 0;
    protected $originalPrice = 0;
    protected $isMinPriceApplied = 0;
    protected $isDiscountPercentage = 0;
    /** @var ServiceWithPrice[] */
    protected $breakdown = [];
    protected $deliveryCharge = 0;
    protected $discountedDeliveryCharge = 0;
    protected $hasHomeDelivery;
    protected $hasPremiseAvailable;

    public function __construct($data = [])
    {
        if(array_key_exists("discount", $data)) $this->discount = $data['discount'];
        if(array_key_exists("discounted_price", $data)) $this->discountedPrice = $data['discounted_price'];
        if(array_key_exists("original_price", $data)) $this->originalPrice = $data['original_price'];
        if(array_key_exists("is_min_price_applied", $data)) $this->isMinPriceApplied = $data['is_min_price_applied'];
        if(array_key_exists("delivery_charge", $data)) $this->deliveryCharge = $data['delivery_charge'];
        if(array_key_exists("discounted_delivery_charge", $data)) $this->discountedDeliveryCharge = $data['discounted_delivery_charge'];
        if(array_key_exists("has_home_delivery", $data)) $this->hasHomeDelivery = $data['has_home_delivery'];
        if(array_key_exists("has_premise_available", $data)) $this->hasPremiseAvailable = $data['has_premise_available'];
        if(array_key_exists("breakdown", $data)) {
            foreach ($data['breakdown'] as $item) {
                $this->breakdown[] = new ServiceWithPrice(null, $item);
            }
        }
    }

    public function addService(ServiceWithPrice $service)
    {
        $this->breakdown[] = $service;

        $this->isMinPriceApplied = $service->getIsMinPriceApplied();
        $this->discount += $service->getDiscount();
        $this->isDiscountPercentage += $service->getIsPercentage();
        $this->discountedPrice += $service->getDiscountedPrice();
        $this->originalPrice += $service->getOriginalPrice();

        return $this;
    }

    public function setDeliveryCharge($amount)
    {
        $this->deliveryCharge = $amount;
        return $this;
    }

    public function setDiscountedDeliveryCharge($amount)
    {
        $this->discountedPrice -= $this->discountedDeliveryCharge;
        $this->originalPrice -= $this->discountedDeliveryCharge;
        $this->discountedDeliveryCharge = $amount;
        $this->discountedPrice += $this->discountedDeliveryCharge;
        $this->originalPrice += $this->discountedDeliveryCharge;
        return $this;
    }

    public function setHasHomeDelivery($has_home_delivery)
    {
        $this->hasHomeDelivery = $has_home_delivery;
        return $this;
    }

    public function setHasPremiseAvailable($has_premise_available)
    {
        $this->hasPremiseAvailable = $has_premise_available;
        return $this;
    }

    public function getDiscountedPrice()
    {
        return $this->discountedPrice;
    }

    public function getServices()
    {
        return $this->breakdown;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function getIsDiscountPercentage()
    {
        return $this->isDiscountPercentage;
    }

    /**
     * @return int|mixed
     */
    public function getDeliveryCharge()
    {
        return $this->deliveryCharge;
    }

    public function toArray()
    {
        return [
            'discount' => (int)$this->discount,
            'discounted_price' => $this->discountedPrice,
            'original_price' => $this->originalPrice,
            'is_min_price_applied' => $this->isMinPriceApplied,
            'delivery_charge' => $this->deliveryCharge,
            'discounted_delivery_charge' => $this->discountedDeliveryCharge,
            'has_home_delivery' => $this->hasHomeDelivery,
            'has_premise_available' => $this->hasPremiseAvailable,
            'breakdown' => array_map(function(ServiceWithPrice $service) {
                return $service->toArray();
            }, $this->breakdown)
        ];
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
