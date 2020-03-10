<?php namespace Sheba\LocationService;

use App\Models\LocationService;
use App\Models\Service;

class PriceCalculation
{
    private $option;
    private $quantity;
    private $minPrice;
    /** @var LocationService $locationService */
    private $locationService;
    /** @var Service */
    private $service;

    public function __construct()
    {
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

    /**
     * @param array $option
     * @return $this
     */
    public function setOption(array $option)
    {
        $this->option = $option;
        return $this;
    }


    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

    private function setMinPrice($price)
    {
        $this->minPrice = $price;
        return $this;
    }

    public function getMinPrice()
    {
        $this->getTotalOriginalPrice();
        return $this->minPrice;
    }

    public function getTotalOriginalPrice()
    {
        $unit_price = $this->getUnitPrice();
        $min_price = $this->getMinPriceFromDB();
        $this->setMinPrice($min_price);
        $service = $this->getService();
        $rent_a_car_price_applied = 0;
        if ($service->category->isRentACar() && ($this->locationService->base_prices && $this->locationService->base_quantity)) {
            $base_quantity = $this->getBaseQuantity();
            $extra_price_after_base_quantity = ($this->quantity > $base_quantity) ? ($unit_price * ($this->quantity - $base_quantity)) : 0;
            $original_price = $this->getBasePrice() + $extra_price_after_base_quantity;
            $rent_a_car_price_applied = 1;
        } else {
            $original_price = $unit_price * $this->quantity;
        }
        if ($original_price < $min_price) {
            $original_price = $min_price;
        } elseif ($rent_a_car_price_applied) {
            $this->setMinPrice($original_price);
        }
        return $original_price;

    }

    public function getUnitPrice()
    {
        $service = $this->getService();
        if ($service->isFixed()) return (double)$this->locationService->prices;
        return $this->getOptionPrice($this->locationService->prices);
    }

    private function getOptionPrice($prices)
    {
        $option = implode(',', $this->option);
        $prices = json_decode($prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return (double)$price;
            }
        }
        return null;
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

    private function getBaseQuantity()
    {
        $service = $this->getService();
        if (!$this->locationService->base_quantity) return null;
        if ($service->isFixed()) return (double)$this->locationService->base_quantity;
        return $this->getOptionPrice($this->locationService->base_quantity);
    }

    /**
     * @return Service
     */
    private function getService()
    {
        if ($this->service) return $this->service;
        else return $this->locationService->service;
    }
}
