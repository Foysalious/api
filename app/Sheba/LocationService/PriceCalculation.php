<?php namespace Sheba\LocationService;

use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use phpDocumentor\Reflection\Types\Iterable_;
use Sheba\PriceCalculation\PriceCalculationAbstract;
use stdClass;

class PriceCalculation extends PriceCalculationAbstract
{
    /** @var LocationService $locationService */
    protected $locationService;

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
     * @throws CorruptedPriceStructureException
     */
    public function getMinPrice()
    {
        $this->getTotalOriginalPrice();
        return $this->minPrice;
    }

    /**
     * @return float|int|null
     * @throws CorruptedPriceStructureException
     */
    public function getTotalOriginalPrice()
    {
        $unit_price = $this->upsellUnitPrice ? $this->upsellUnitPrice : $this->getUnitPrice();
        $surcharge = $this->getSurcharge();
        $surcharge_amount = $surcharge ? ($surcharge->isPercentage() ? ($unit_price * $surcharge->amount) / 100 : $surcharge->amount) : 0;
        $unit_price_with_surcharge = $unit_price + $surcharge_amount;
        $min_price = $this->getMinPriceFromDB();
        $this->setMinPrice($min_price);
        $service = $this->getService();
        $rent_a_car_price_applied = 0;
        if ($service->category->isRentACar() && ($this->locationService->base_prices && $this->locationService->base_quantity)) {
            $base_quantity = $this->getBaseQuantity();
            $extra_price_after_base_quantity = ($this->quantity > $base_quantity) ? ($unit_price_with_surcharge * ($this->quantity - $base_quantity)) : 0;
            $original_price = $this->getBasePrice() + $extra_price_after_base_quantity;
            $rent_a_car_price_applied = 1;
        } else {
            $original_price = $unit_price_with_surcharge * $this->quantity;
        }
        if ($original_price < $min_price) {
            $original_price = $min_price;
        } elseif ($rent_a_car_price_applied) {
            $this->setMinPrice($original_price);
        }
        return $original_price;

    }

    public function getSurchargeAmount()
    {
        $unit_price = $this->upsellUnitPrice ? $this->upsellUnitPrice : $this->getUnitPrice();
        $surcharge = $this->getSurcharge();
        $surcharge_amount = $surcharge ? ($surcharge->isPercentage() ? ($unit_price * $surcharge->amount) / 100 : $surcharge->amount) : 0;
        return $surcharge_amount * $this->quantity;
    }

    /**
     * @return float|null
     * @throws CorruptedPriceStructureException
     */
    public function getUnitPrice()
    {
        $service = $this->getService();
        if ($service->isFixed()) return $this->getFixedPrice($this->locationService->prices);
        return $this->getOptionPrice($this->locationService->prices);
    }

    /**
     * @param $prices
     * @return float
     * @throws CorruptedPriceStructureException
     */
    protected function getFixedPrice($prices)
    {
        if ($prices instanceof stdClass) $this->throwPriceStructureException();
        return (double)$this->locationService->prices;
    }


    /**
     * @param $prices
     * @return float|null
     * @throws CorruptedPriceStructureException
     */
    protected function getOptionPrice($prices)
    {
        $option = implode(',', $this->option);
        $prices = json_decode($prices);
        if (!$prices instanceof stdClass) $this->throwPriceStructureException();
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return (double)$price;
            }
        }
        return null;
    }

    /**
     * @throws CorruptedPriceStructureException
     */
    protected function throwPriceStructureException()
    {
        throw new CorruptedPriceStructureException('Price mismatch in Service #' . $this->locationService->service_id . ' and Location #' . $this->locationService->location_id, 400);
    }

    public function getMinPriceFromDB()
    {
        $service = $this->getService();
        if (!$this->locationService->min_prices) return 0;
        if ($service->isFixed()) return (double)$this->locationService->min_prices;
        return $this->getOptionPrice($this->locationService->min_prices);
    }

    public function getBasePrice()
    {
        $service = $this->getService();
        if (!$this->locationService->base_prices) return null;
        if ($service->isFixed()) return (double)$this->locationService->base_prices;
        return $this->getOptionPrice($this->locationService->base_prices);
    }

    protected function getBaseQuantity()
    {
        $service = $this->getService();
        if (!$this->locationService->base_quantity) return null;
        if ($service->isFixed()) return (double)$this->locationService->base_quantity;
        return $this->getOptionPrice($this->locationService->base_quantity);
    }

    /**
     * @return Service
     */
    protected function getService()
    {
        if ($this->service) return $this->service;
        else return $this->locationService->service;
    }
}
