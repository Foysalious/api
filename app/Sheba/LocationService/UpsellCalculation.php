<?php namespace Sheba\LocationService;

use App\Models\LocationService;
use App\Models\Service;

class UpsellCalculation
{
    private $option;
    private $quantity;
    /** @var LocationService $locationService */
    private $locationService;

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
        if ($this->locationService->service->isFixed()) return $this->getFixedServiceUpsell();
        return $this->getOptionServiceUpsell();
    }

    private function getFixedServiceUpsell()
    {
        $upsell_prices = $this->locationService->upsell_price;
        foreach ($upsell_prices as $key => $prices_with_min_max_quantity) {
            return array_map(function ($item) {
                return [
                    'min'   => (double)$item->min,
                    'max'   => (double)$item->max,
                    'price' => (double)$item->price
                ];
            }, $prices_with_min_max_quantity);
        }

        return null;
    }

    private function getOptionServiceUpsell()
    {
        $option = implode(',', $this->option);
        $upsell_prices = $this->locationService->upsell_price;
        foreach ($upsell_prices as $key => $prices_with_min_max_quantity) {
            if ($key == $option) {
                return array_map(function ($item) {
                    return [
                        'min'   => (double)$item->min,
                        'max'   => (double)$item->max,
                        'price' => (double)$item->price
                    ];
                }, $prices_with_min_max_quantity);
            }
        }

        return null;
    }
}
