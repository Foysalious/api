<?php namespace Sheba\Service;

use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;

class MinMaxPrice
{
    /** @var LocationService */
    private $locationService;
    /** @var Service */
    private $service;

    public function setLocationService(LocationService $location_service)
    {
        $this->locationService = $location_service;
        return $this;
    }

    public function setService(Service $service)
    {
        $this->service = $service;
        return $this;
    }

    public function getMax()
    {
        return $this->calculate('max');
    }

    private function calculate($condition)
    {
        if ($this->service->isFixed()) return (double)$this->locationService->prices;
        $prices = (array)json_decode($this->locationService->prices);
        return $condition == 'min' ? (double)min($prices) : (double)max($prices);
    }

    public function getMin()
    {
        return $this->calculate('min');
    }
}
