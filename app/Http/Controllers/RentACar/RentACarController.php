<?php namespace App\Http\Controllers\RentACar;

use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\FromGeo;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\ServiceRequest\ServiceRequest;
use Sheba\ServiceRequest\ServiceRequestObject;
use Throwable;

class RentACarController extends Controller
{
    public function getPrices(Request $request, ServiceRequest $service_request, PriceCalculation $price_calculation, DiscountCalculation $discount_calculation)
    {
        try {
            $this->validate($request, ['services' => 'required|string', 'lat' => 'required|numeric', 'lng' => 'required|numeric']);
            $services = json_decode($request->services, 1);
            /** @var ServiceRequestObject[] $services */
            $services = $service_request_object = $service_request->setServices($services)->get();
            $service = $services[0];
            $location_service = LocationService::where([['location_id', $service->getHyperLocal()->location_id], ['service_id', $service->getServiceId()]])->first();
            if (!$location_service) return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);
            $price_calculation->setLocationService($location_service)->setOption($service->getOption())->setQuantity($service->getQuantity());
            $original_price = $price_calculation->getTotalOriginalPrice();
            $discount_calculation->setLocationService($location_service)->setOriginalPrice($original_price)->calculate();
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
                'price' => 4000.00
            ],
            [
                'name' => 'Premium',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/sedan.png',
                'number_of_seats' => 4,
                'info' => 'Newer economy cars',
                'price' => 6000.00
            ],
            [
                'name' => 'Family',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/noah.png',
                'number_of_seats' => 7,
                'info' => 'Model below 2009',
                'price' => 8000.00
            ],
            [
                'name' => 'Premium Family',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/noah.png',
                'number_of_seats' => 7,
                'info' => 'Model above 2010',
                'price' => 10000.00
            ],
            [
                'name' => 'Group',
                'image' => 'https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/hiace.png',
                'number_of_seats' => 12,
                'info' => 'Model above 2010',
                'price' => 12000.00
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
}
