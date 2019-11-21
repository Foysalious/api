<?php namespace Sheba\LocationService;


use App\Models\LocationService;
use App\Models\Service;

class PriceCalculation
{
    /** @var LocationService */
    private $locationService;
    /** @var Service */
    private $service;
    private $option;

    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function setLocationService($location_service)
    {
        $this->locationService = $location_service;
        return $this;
    }

    public function setOption($option)
    {
        $this->option = $option;
        return $this;
    }

    public function getPrice()
    {
        if ($this->service->isFixed()) return (double)$this->locationService->prices;
        return $this->getOptionPrice();
    }

    public function getMinPrice()
    {
        if ($this->service->isFixed()) return (double)$this->locationService->prices;
        return $this->getOptionPrice();
    }

    private function getOptionPrice()
    {
        $option = implode(',', $this->option);
        $prices = json_decode($this->locationService->prices);
        foreach ($prices as $key => $price) {
            if ($key == $option) {
                return (double)$price;
            }
        }
        return null;
    }


}