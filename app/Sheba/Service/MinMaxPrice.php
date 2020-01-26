<?php namespace Sheba\Service;


use App\Models\LocationService;
use App\Models\Service;

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

    public function getMin()
    {
        return $this->calculate('min');
    }

    private function calculate($condition)
    {
        if ($this->service->isFixed()) return (double)$this->locationService->prices;
        $prices = (array)json_decode($this->locationService->prices);
        return $condition == 'min' ? (double)min($prices) : (double)max($prices);
    }

}