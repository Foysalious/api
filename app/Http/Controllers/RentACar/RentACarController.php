<?php namespace App\Http\Controllers\RentACar;

use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\CarRentalPrice;
use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\LocationService;
use App\Models\ServiceSurcharge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\FromGeo;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\PriceCalculation\PriceCalculationFactory;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;
use Throwable;

class RentACarController extends Controller
{
    public function getPrices(Request $request, ServiceRequest $service_request, DiscountCalculation $discount_calculation)
    {
        try {
            $this->validate($request, ['services' => 'required|string', 'lat' => 'required|numeric', 'lng' => 'required|numeric']);
            $services = json_decode($request->services, 1);
            /** @var ServiceRequestObject[] $services */
            $services = $service_request_object = $service_request->setServices($services)->get();
            $service = $services[0];
            $service_model = $service->getService();
            $price_calculation = $this->resolvePriceCalculation($service_model->category);
            $location_service = LocationService::where([['location_id', $service->getHyperLocal()->location_id], ['service_id', $service->getServiceId()]])->first();
            if (!$location_service) return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);
            $price_calculation->setService($service_model)->setOption($service->getOption())->setQuantity($service->getQuantity());
            $service_model->category->isRentACarOutsideCity() ? $price_calculation->setPickupThanaId($service->getPickupThana()->id)->setDestinationThanaId($service->getDestinationThana()->id) : $price_calculation->setLocationService($location_service);
            $original_price = $price_calculation->getTotalOriginalPrice();
            $discount_calculation->setService($service_model)->setLocationService($location_service)->setOriginalPrice($original_price)->calculate();
            return api_response($request, null, 200, ['price' => [
                'discounted_price' => $discount_calculation->getDiscountedPrice(),
                'original_price' => $original_price,
                'discount' => $discount_calculation->getDiscount(),
                'quantity' => $service->getQuantity()
            ]]);
        } catch (InsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'Please try with outside city for this location.', 'code' => 700]);
        } catch (OutsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);
        } catch (DestinationCitySameAsPickupException $e) {
            return api_response($request, null, 400, ['message' => 'Please try with inside city for this location.', 'code' => 702]);
        }
    }

    private function resolvePriceCalculation(Category $category)
    {
        $priceCalculationFactory = new PriceCalculationFactory();
        $priceCalculationFactory->setCategory($category);
        return $priceCalculationFactory->get();
    }

    public function getCars(Request $request, ServiceRequest $service_request, PriceCalculation $price_calculation, DiscountCalculation $discount_calculation)
    {
        try {
            $this->validate($request, ['services' => 'required|string', 'lat' => 'required|numeric', 'lng' => 'required|numeric']);
            $services = json_decode($request->services, 1);
            $services = $service_request_object = $service_request->setServices($services)->get();
            $service = $services[0];
            $service_model = $service->getService();

            $location_service = LocationService::where([['location_id', $service->getHyperLocal()->location_id], ['service_id', $service->getServiceId()]])->first();
            if (!$location_service) return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);

            $cars = $service_model->category->isRentACarOutsideCity()
                ? $this->getCarsForOutsideCity($service, $discount_calculation)
                : $this->getCarsForInsideCity($service, $discount_calculation);

            return api_response($request, null, 200, ['cars' => $cars]);
        } catch (InsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'Please try with outside city for this location.', 'code' => 700]);
        } catch (OutsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);
        } catch (DestinationCitySameAsPickupException $e) {
            return api_response($request, null, 400, ['message' => 'Please try with inside city for this location.', 'code' => 702]);
        }

    }

    public function getCarsForOutsideCity($service, $discount_calculation)
    {
        $service_model = $service->getService();
        $variables = json_decode($service->getService()->variables, true);
        $car_types = $this->getCarTypes($variables);
        $car_prices = $this->getOptionUnitPricesOfServiceForOutsideCity($service_model, $service->getPickupThana(), $service->getDestinationThana());
        $cars = [];
        $surcharge = $this->getSurchargeOfService($service_model);

        $location_service = LocationService::where([['location_id', $service->getHyperLocal()->location_id], ['service_id', $service->getServiceId()]])->first();

        $price_calculation = $this->resolvePriceCalculation($service_model->category);


        foreach ($car_types as $key => $car) {
            $option = [$key];
            $price_calculation->setService($service_model)->setOption($option)->setQuantity($service->getQuantity());
            $price_calculation->setPickupThanaId($service->getPickupThana()->id)->setDestinationThanaId($service->getDestinationThana()->id);
            $original_price = $price_calculation->getTotalOriginalPrice();
            $discount_calculation->setService($service_model)->setLocationService($location_service)->setOriginalPrice($original_price)->calculate();
            $discounted_price =  $discount_calculation->getDiscountedPrice();
            $surcharge_amount = $surcharge
                ? $surcharge->is_amount_percentage
                    ? ($discounted_price / 100) * $surcharge->amount
                    : $surcharge->amount
                : null;


            $answer = [
                'name' => $car,
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/'.$variables['helpers']['assets'][$key].'.png',
                'number_of_seats' => $variables['helpers']['capacity'][$key],
                'info' => $variables['helpers']['descriptions'][$key],
                'discounted_price' => $discounted_price,
                'original_price' => $original_price,
                'discount' => $discount_calculation->getDiscount(),
                'quantity' => $service->getQuantity(),
                'is_surcharge_applied' => !!($surcharge) ? 1 : 0,
                'surcharge_percentage' => $surcharge ? $surcharge->amount : null,
                'surcharge_amount' => $surcharge_amount,
                'unit_price' => $car_prices[$key] ? (double) $car_prices[$key] : null
            ];
            $cars[] = $answer;
        }

        return $cars;
    }

    public function getCarsForInsideCity($service, $discount_calculation)
    {
        $service_model = $service->getService();
        $variables = json_decode($service->getService()->variables, true);
        $car_types = $this->getCarTypes($variables);

        $cars = [];
        $surcharge = $this->getSurchargeOfService($service_model);

        $location_service = LocationService::where([['location_id', $service->getHyperLocal()->location_id], ['service_id', $service->getServiceId()]])->first();
        $car_prices = $location_service ? json_decode($location_service->prices, true) : [];


        $price_calculation = $this->resolvePriceCalculation($service_model->category);

        foreach ($car_types as $key => $car) {
            $option = [$key];
            $price_calculation->setService($service_model)->setOption($option)->setQuantity($service->getQuantity());
            $price_calculation->setLocationService($location_service);
            $original_price = $price_calculation->getTotalOriginalPrice();
            $discount_calculation->setService($service_model)->setLocationService($location_service)->setOriginalPrice($original_price)->calculate();
            $discounted_price =  $discount_calculation->getDiscountedPrice();
            $surcharge_amount = $surcharge
                ? $surcharge->is_amount_percentage
                    ? ($discounted_price / 100) * $surcharge->amount
                    : $surcharge->amount
                : null;
            $answer = [
                'name' => $car,
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/'.$variables['helpers']['assets'][$key].'.png',
                'number_of_seats' => $variables['helpers']['capacity'][$key],
                'info' => $variables['helpers']['descriptions'][$key],
                'discounted_price' => $discounted_price,
                'original_price' => $original_price,
                'discount' => $discount_calculation->getDiscount(),
                'quantity' => $service->getQuantity(),
                'is_surcharge_applied' => !!($surcharge) ? 1 : 0,
                'surcharge_percentage' => $surcharge ? $surcharge->amount : null,
                'surcharge_amount' => $surcharge_amount,
                'unit_price' => $car_prices[$key]
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

    public function getOptionUnitPricesOfServiceForOutsideCity($service, $pickup_thana, $destination_thana)
    {
        $car_rental_prices = CarRentalPrice::where([
            ['service_id', $service->id],
            ['pickup_thana_id',  $pickup_thana->id],
            ['destination_thana_id', $destination_thana->id]
        ])->first();
        return $car_rental_prices ? json_decode($car_rental_prices->prices, true) : null;
    }

    public function getSurchargeOfService($service)
    {
        return ServiceSurcharge::where('service_id', $service->id)->first();
    }

    public function getPickupAndDestinationThana(Request $request, FromGeo $fromGeo)
    {
        $pickup_lat = $request->pickup_lat;
        $pickup_lng = $request->pickup_lng;
        $destination_lat = $request->destination_lat;
        $destination_lng = $request->destination_lng;

        $pickup_thana = ($pickup_lat && $pickup_lng) ? $fromGeo->setThanas()->getThana($pickup_lat, $pickup_lng) : null;
        $pickup_thana = $pickup_thana ? [
            'id' => $pickup_thana->id,
            'name' => $pickup_thana->name,
            'location_id' => $pickup_thana->location_id,
            'lat' => $pickup_thana->lat,
            'lng' => $pickup_thana->lng,
        ] : null;
        $destination_thana = ($destination_lat && $destination_lng) ? $fromGeo->setThanas()->getThana($destination_lat, $destination_lng) : null;
        $destination_thana = $destination_thana ? [
            'id' => $destination_thana->id,
            'name' => $destination_thana->name,
            'location_id' => $destination_thana->location_id,
            'lat' => $destination_thana->lat,
            'lng' => $destination_thana->lng,
        ] : null;

        return api_response($request, null, 200, ['pickup_thana' => $pickup_thana, 'destination_thana' => $destination_thana]);
    }
}
