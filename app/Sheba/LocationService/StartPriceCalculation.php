<?php

namespace Sheba\LocationService;

use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;

class StartPriceCalculation
{
    private $upsellCalculation;
    private $discountCalculation;
    private $priceCalculation;

    public function __construct(PriceCalculation $priceCalculation, UpsellCalculation $upsellCalculation, DiscountCalculation $discountCalculation)
    {
        $this->priceCalculation = $priceCalculation;
        $this->upsellCalculation = $upsellCalculation;
        $this->discountCalculation = $discountCalculation;
    }

    public function getStartPrice(Service $service)
    {
        try {
            /** @var LocationService $location_service */
            $location_service = $service->locationServices->first();
            $prices = json_decode($location_service->prices);
        }catch (\Exception $e){
            dd($service, $location_service);
            throw $e;
        }

        $this->priceCalculation->setService($service)->setLocationService($location_service)->setQuantity($service->min_quantity);
        $this->upsellCalculation->setService($service)->setLocationService($location_service)->setQuantity($service->min_quantity);

        if ($prices === null) return false;
        if ($service->variable_type == 'Options') {
            $prices = (array)$prices;
            $min_price_option = $this->getOptionOfMinPriceCombination($prices);
            if ($min_price_option === null) return false;
            $option = explode(',', $min_price_option);
            $this->priceCalculation->setOption($option);
            $this->upsellCalculation->setOption($option);
        }
        $upsell_unit_price = $this->upsellCalculation->getUpsellUnitPriceForSpecificQuantity();
        $unit_price = $upsell_unit_price ? $upsell_unit_price : $this->priceCalculation->getUnitPrice();
        $total_original_price = $service->category->isRentACar() ? $this->priceCalculation->getTotalOriginalPrice() : $unit_price * $service->min_quantity;
        $this->discountCalculation->setService($service)
            ->setLocationService($location_service)
            ->setOriginalPrice($total_original_price)
            ->setQuantity($service->min_quantity)
            ->calculateLatestDiscountedPrice();
        return $this->discountCalculation->getDiscountedPrice();
    }

    private function getOptionOfMinPriceCombination($prices)
    {
        try {
            return array_keys($prices, min($prices))[0];
        } catch (Throwable $e) {
            return null;
        }
    }
}