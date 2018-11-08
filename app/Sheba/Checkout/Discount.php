<?php

namespace App\Sheba\Checkout;

use App\Models\PartnerServiceDiscount;
use App\Models\PartnerServiceSurcharge;
use App\Repositories\PartnerServiceRepository;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Sheba\Checkout\Services\ServiceObject;

class Discount
{
    protected $discount = 0;
    protected $min_price = 0;
    protected $base_quantity = 0;
    protected $base_price = 0;
    protected $discounted_price;
    protected $unit_price;
    protected $quantity;
    protected $original_price;
    protected $sheba_contribution = 0;
    protected $partner_contribution = 0;
    protected $discount_percentage = 0;
    protected $isDiscountPercentage = 0;
    protected $surchargePercentage = 0;
    protected $amount = 0;
    protected $cap = null;
    protected $discount_id = null;
    protected $hasDiscount = 0;
    /** @var ServiceObject */
    protected $serviceObject;
    protected $servicePivot;
    protected $partnerServiceRepository;

    public function __construct()
    {
        $this->partnerServiceRepository = new PartnerServiceRepository();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function setServiceObj(ServiceObject $serviceObject)
    {
        $this->serviceObject = $serviceObject;
        return $this;
    }

    public function setServicePivot(Pivot $servicePivot)
    {
        $this->servicePivot = $servicePivot;
        return $this;
    }

    public function setScheduleDateTime($schedule_date_time)
    {
        $surcharge = PartnerServiceSurcharge::where('partner_service_id', $this->servicePivot->id)->runningAt($schedule_date_time)->first();
        $this->surchargePercentage = $surcharge ? $surcharge->amount : 0;;
        return $this;
    }

    public function initialize()
    {
        if ($this->serviceObject->serviceModel->isOptions()) {
            $this->unit_price = $this->partnerServiceRepository->getPriceOfOptionsService($this->servicePivot->prices, $this->serviceObject->option);
            $this->min_price = empty($this->servicePivot->min_prices) ? 0 : $this->partnerServiceRepository->getMinimumPriceOfOptionsService($this->servicePivot->min_prices, $this->serviceObject->option);
            $this->base_quantity = empty($this->servicePivot->base_quantity) ? 0 : $this->partnerServiceRepository->getMinimumPriceOfOptionsService($this->servicePivot->base_quantity, $this->serviceObject->option);
            $this->base_price = empty($this->servicePivot->base_prices) ? 0 : $this->partnerServiceRepository->getMinimumPriceOfOptionsService($this->servicePivot->base_prices, $this->serviceObject->option);
        } else {
            $this->unit_price = (double)$this->servicePivot->prices;
            $this->min_price = (double)$this->servicePivot->min_prices;
            $this->base_quantity = (double)$this->servicePivot->base_quantity;
            $this->base_price = (double)$this->servicePivot->base_prices;
        }
        $this->quantity = $this->serviceObject->quantity;
        $this->calculateOriginalPrice();
        return $this;
    }

    public function calculateServiceDiscount()
    {
        if ($running_discount = PartnerServiceDiscount::where('partner_service_id', $this->servicePivot->id)->running()->first()) {
            $this->hasDiscount = 1;
            $this->discount_id = $running_discount->id;
            $this->cap = (double)$running_discount->cap;
            $this->amount = (double)$running_discount->amount;
            $this->sheba_contribution = (double)$running_discount->sheba_contribution;
            $this->partner_contribution = (double)$running_discount->partner_contribution;
            if ($running_discount->isPercentage()) {
                $this->discount_percentage = $running_discount->amount;
                $this->isDiscountPercentage = 1;
                $this->discount = ($this->original_price * $running_discount->amount) / 100;
                if ($running_discount->hasCap() && $this->discount > $running_discount->cap) $this->discount = $running_discount->cap;
            } else {
                $this->discount = $this->quantity * $running_discount->amount;
                if ($this->discount > $this->original_price) $this->discount = $this->original_price;
            }
        }
        $this->discounted_price = $this->original_price - $this->discount;
    }

    private function calculateOriginalPrice()
    {
        if ($this->isRentACar() && ($this->base_price && $this->base_quantity) && ($this->quantity >= $this->base_quantity)) {
            $this->original_price = $this->base_price + ($this->unit_price * ($this->quantity - $this->base_quantity));
        } else {
            $this->original_price = $this->unit_price * $this->quantity;
        }
        $this->original_price = $this->original_price < $this->min_price ? $this->min_price : $this->original_price;
        if ($this->surchargePercentage > 0) $this->original_price = $this->original_price + ($this->original_price * $this->surchargePercentage / 100);
    }

    private function isRentACar()
    {
        return in_array($this->serviceObject->serviceModel->category_id, array_map('intval', explode(',', env('RENT_CAR_IDS'))));
    }
}