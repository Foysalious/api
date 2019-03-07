<?php

namespace App\Sheba\Checkout;

use App\Models\Partner;
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
    protected $partner;
    protected $servicePivot;
    /** @var $runningDiscount PartnerServiceDiscount */
    protected $runningDiscount;
    protected $scheduleDateTime;
    protected $partnerServiceRepository;

    public function __construct()
    {
        $this->partnerServiceRepository = new PartnerServiceRepository();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function setDiscounts($discounts)
    {
        $this->calculateKey($discounts, 'discounts');
        return $this;
    }

    public function setSurcharges($surcharges)
    {
        $this->calculateKey($surcharges, 'surcharges');
        return $this;
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
        $this->scheduleDateTime = $schedule_date_time;
        return $this;
    }

    public function initialize()
    {
        $this->calculateRunningSurcharge();
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
        $this->calculateServiceDiscount();
        return $this;
    }

    protected function calculateServiceDiscount()
    {
        $this->calculateRunningDiscount();
        if ($this->runningDiscount) {
            $this->hasDiscount = 1;
            $this->discount_id = $this->runningDiscount->id;
            $this->cap = (double)$this->runningDiscount->cap;
            $this->amount = (double)$this->runningDiscount->amount;
            $this->sheba_contribution = (double)$this->runningDiscount->sheba_contribution;
            $this->partner_contribution = (double)$this->runningDiscount->partner_contribution;
            if ($this->runningDiscount->isPercentage()) {
                $this->discount_percentage = $this->runningDiscount->amount;
                $this->isDiscountPercentage = 1;
                $this->discount = ($this->original_price * $this->runningDiscount->amount) / 100;
                if ($this->runningDiscount->hasCap() && $this->discount > $this->runningDiscount->cap) $this->discount = $this->runningDiscount->cap;
            } else {
                $this->discount = $this->quantity * $this->runningDiscount->amount;
                if ($this->discount > $this->original_price) $this->discount = $this->original_price;
            }
        }
        $this->discounted_price = $this->original_price - $this->discount;
    }

    private function calculateOriginalPrice()
    {
        $rent_a_car_price_applied = 0;
        if ($this->isRentACar() && ($this->base_price && $this->base_quantity)) {
            $extra_price_after_base_quantity = ($this->quantity > $this->base_quantity) ? ($this->unit_price * ($this->quantity - $this->base_quantity)) : 0;
            $this->original_price = $this->base_price + $extra_price_after_base_quantity;
            $rent_a_car_price_applied = 1;
        } else {
            $this->original_price = $this->unit_price * $this->quantity;
        }
        if ($this->original_price < $this->min_price) {
            $this->original_price = $this->min_price;
        } elseif ($rent_a_car_price_applied) {
            $this->min_price = $this->original_price;
        }
        if ($this->surchargePercentage > 0) $this->original_price = $this->original_price + ($this->original_price * $this->surchargePercentage / 100);
    }

    private function isRentACar()
    {
        return in_array($this->serviceObject->serviceModel->category_id, array_map('intval', explode(',', env('RENT_CAR_IDS'))));
    }


    private function calculateKey($collection, $key)
    {
        if (!$collection) return;
        $object = $collection->where('partner_service_id', $this->servicePivot->id)->first();
        if ($key == 'surcharges') $this->surchargePercentage = $object ? $object->amount : 0;
        elseif ($key == 'discounts') $this->runningDiscount = $object ? $object : 0;
    }

    private function calculateRunningDiscount()
    {
        if ($this->runningDiscount === null) $this->runningDiscount = PartnerServiceDiscount::where('partner_service_id', $this->servicePivot->id)->running()->first();
    }

    private function calculateRunningSurcharge()
    {
        if ($this->surchargePercentage === null) {
            $surcharge = PartnerServiceSurcharge::where('partner_service_id', $this->servicePivot->id)->runningAt($this->scheduleDateTime)->first();
            $this->surchargePercentage = $surcharge ? $surcharge->amount : 0;;
        }
    }
}