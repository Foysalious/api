<?php namespace Sheba\LocationService;

use App\Models\LocationService;
use App\Models\Service;

class PriceCalculation
{
    /** @var LocationService $locationService */
    private $locationService;
    private $option;

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
     * @param array $option
     * @return $this
     */
    public function setOption(array $option)
    {
        $this->option = $option;
        return $this;
    }

    public function getUnitPrice()
    {
        if ($this->locationService->service->isFixed()) return (double)$this->locationService->prices;
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

    public function getMinPrice()
    {
        if ($this->locationService->service->isFixed()) return (double)$this->locationService->prices;
        return $this->getOptionPrice();
    }
}
