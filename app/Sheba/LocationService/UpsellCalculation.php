<?php namespace Sheba\LocationService;

use App\Models\LocationService;
use App\Models\Service;

class UpsellCalculation
{
    private $option;
    private $quantity;
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

    public function getAllUpsellWithMinMaxQuantity()
    {
        $service = $this->getService();
        if ($service->isFixed()) return $this->getFixedServiceUpsell();
        return $this->getOptionServiceUpsell();
    }

    public function getUpsellUnitPriceForSpecificQuantity()
    {
        $service = $this->getService();
        $upsell_prices = $service->isFixed() ? $this->getFixedServiceUpsell() : $this->getOptionServiceUpsell();
        if (!$upsell_prices) return null;
        foreach ($upsell_prices as $upsell_price) {
            if ($this->quantity >= $upsell_price['min'] && $this->quantity <= $upsell_price['max']) return $upsell_price['price'];
        }
    }

    private function getFixedServiceUpsell()
    {
        if(!$this->locationService) return null;
        $upsell_prices = $this->locationService->upsell_price;
        if (!$upsell_prices) return null;

        foreach ($upsell_prices as $key => $prices_with_min_max_quantity) {
            return array_map(function ($item) {
                return [
                    'min' => (double)$item->min,
                    'max' => (double)$item->max,
                    'price' => (double)$item->price
                ];
            }, $prices_with_min_max_quantity);
        }
    }

    private function getOptionServiceUpsell()
    {
        if(!$this->locationService) return null;
        $option = implode(',', $this->option);
        $upsell_prices = $this->locationService->upsell_price;
        if (!$upsell_prices) return null;

        foreach ($upsell_prices as $key => $prices_with_min_max_quantity) {
            if ($key == $option) {
                return array_map(function ($item) {
                    return [
                        'min' => (double)$item->min,
                        'max' => (double)$item->max,
                        'price' => (double)$item->price
                    ];
                }, $prices_with_min_max_quantity);
            }
        }
    }

    /**
     * @return Service
     */
    private function getService()
    {
        if ($this->service) return $this->service;
        else return $this->locationService ? $this->locationService->service : null;
    }

}
