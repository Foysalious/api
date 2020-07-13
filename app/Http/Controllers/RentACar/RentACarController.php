<?php namespace App\Http\Controllers\RentACar;

use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HyperLocal;
use App\Models\LocationService;
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

    public function getOptions(Request $request, ServiceRequest $service_request, PriceCalculation $price_calculation, DiscountCalculation $discount_calculation)
    {
        $options = [
            [
                'name' => 'Budget',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/sedan.png',
                'number_of_seats' => 4,
                'info' => 'Model below 2009',
                'discounted_price' => 2990,
                'original_price' => 2990,
                'discount' => 0,
                'quantity' => 1,
                'is_surcharge_applied' => 1,
                'surcharge_percentage' => 20
            ],
            [
                'name' => 'Premium',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/sedan.png',
                'number_of_seats' => 4,
                'info' => 'Newer economy cars',
                'discounted_price' => 3700,
                'original_price' => 4000,
                'discount' => 0,
                'quantity' => 1,
                'is_surcharge_applied' => 0,
                'surcharge_percentage' => 30
            ],
            [
                'name' => 'Family',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/noah.png',
                'number_of_seats' => 7,
                'info' => 'Model below 2009',
                'discounted_price' => 6000,
                'original_price' => 6000,
                'discount' => 0,
                'quantity' => 1,
                'is_surcharge_applied' => 1,
                'surcharge_percentage' => 25
            ],
            [
                'name' => 'Premium Family',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/noah.png',
                'number_of_seats' => 7,
                'info' => 'Model above 2010',
                'discounted_price' => 9000,
                'original_price' => 10000,
                'discount' => 0,
                'quantity' => 1,
                'is_surcharge_applied' => 0,
                'surcharge_percentage' => 30
            ],
            [
                'name' => 'Group',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/hiace.png',
                'number_of_seats' => 12,
                'info' => 'Model above 2010',
                'discounted_price' => 12000,
                'original_price' => 12000,
                'discount' => 0,
                'quantity' => 1,
                'is_surcharge_applied' => 1,
                'surcharge_percentage' => 30
            ]
        ];
        return api_response($request, null, 200, ['options' => $options]);
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

    private function resolvePriceCalculation(Category $category)
    {
        $priceCalculationFactory = new PriceCalculationFactory();
        $priceCalculationFactory->setCategory($category);
        return $priceCalculationFactory->get();
    }
}
