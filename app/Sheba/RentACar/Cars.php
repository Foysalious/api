<?php


namespace Sheba\RentACar;


use Sheba\Dal\Category\Category;
use Sheba\Dal\LocationService\LocationService;
use Sheba\LocationService\DiscountCalculation;
use Sheba\PriceCalculation\PriceCalculationFactory;

class Cars
{
    private $discount_calculation;
    private $location_service;
    private $service;

    public function __construct(DiscountCalculation $discount_calculation, LocationService $location_service, $service)
    {
        $this->discount_calculation = $discount_calculation;
        $this->location_service = $location_service;
        $this->service = $service;
    }

    public function getCars()
    {
        $service_model = $this->service->getService();
        $variables = json_decode($this->service->getService()->variables, true);
        $car_types = $this->getCarTypes($variables);

        $cars = [];
        $price_calculation = $this->resolvePriceCalculation($service_model->category);



        foreach ($car_types as $key => $car) {
            $option = [$key];
            $price_calculation->setService($service_model)->setOption($option)->setQuantity($this->service->getQuantity());
            $service_model->category->isRentACarOutsideCity() ? $price_calculation->setPickupThanaId($this->service->getPickupThana()->id)->setDestinationThanaId($this->service->getDestinationThana()->id) : $price_calculation->setLocationService($this->location_service);
            $original_price = $price_calculation->getTotalOriginalPrice(false);
            $this->discount_calculation->setService($service_model)->setLocationService($this->location_service)->setOriginalPrice($original_price)->calculate();
            $discounted_price =  $this->discount_calculation->getDiscountedPrice();
            $unit_price = $price_calculation->getUnitPrice();
            $surcharge = $price_calculation->getSurcharge();
            $surcharge_amount =  $surcharge
                ? $surcharge->is_amount_percentage
                    ? ($unit_price / 100) * $surcharge->amount
                    : $surcharge->amount
                : null;
            $answer = [
                'name' => $car,
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/'.$variables['helpers']['assets'][$key].'.png',
                'number_of_seats' => $variables['helpers']['capacity'][$key],
                'info' => $variables['helpers']['descriptions'][$key],
                'discounted_price' => $discounted_price,
                'original_price' => $original_price,
                'discount' => $this->discount_calculation->getDiscount(),
                'quantity' => $this->service->getQuantity(),
                'is_surcharge_applied' => !!($surcharge) ? 1 : 0,
                'is_vat_applicable' => $service_model->category->is_vat_applicable ? 1 : 0,
                'vat_percentage' => $service_model->category->is_vat_applicable ? config('sheba.category_vat_in_percentage') : 0,
                'surcharge_percentage' => $surcharge ? $surcharge->amount : null,
                'surcharge_amount' => $surcharge_amount,
                'unit_price' => $unit_price,
                'sheba_contribution' => $this->discount_calculation->getShebaContribution(),
                'partner_contribution' => $this->discount_calculation->getPartnerContribution(),
                'is_discount_percentage' => $this->discount_calculation->getIsDiscountPercentage() ? 1 : 0
            ];
            $cars[] = $answer;
        }

        return $cars;
    }

    public function getCarTypes($variables)
    {
        $car_type_option = $variables['options'][0];
        return $car_type_option ? explode(',', $car_type_option['answers']) : null;
    }

    private function resolvePriceCalculation(Category $category)
    {
        $priceCalculationFactory = new PriceCalculationFactory();
        $priceCalculationFactory->setCategory($category);
        return $priceCalculationFactory->get();
    }
}
