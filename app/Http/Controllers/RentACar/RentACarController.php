<?php namespace App\Http\Controllers\RentACar;

use App\Exceptions\HyperLocationNotFoundException;
use App\Exceptions\RentACar\DestinationCitySameAsPickupException;
use App\Exceptions\RentACar\InsideCityPickUpAddressNotFoundException;
use App\Exceptions\RentACar\OutsideCityPickUpAddressNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\CarRentalPrice;
use Sheba\Dal\Category\Category;
use App\Models\HyperLocal;
use Sheba\Dal\LocationService\LocationService;
use App\Models\ServiceSurcharge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Location\FromGeo;
use Sheba\LocationService\DiscountCalculation;
use Sheba\LocationService\PriceCalculation;
use Sheba\PriceCalculation\PriceCalculationFactory;
use Sheba\RentACar\Cars;
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
            $services[0]['option'] = [0]; //Static option set to make service object
            $services = $service_request_object = $service_request->setServices($services)->get();
            $service = $services[0];

            $location_service = LocationService::where([['location_id', $service->getHyperLocal()->location_id], ['service_id', $service->getServiceId()]])->first();
            if (!$location_service) return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);

            $cars = (new Cars($discount_calculation, $location_service, $service))->getCars();

            return api_response($request, null, 200, ['cars' => $cars]);
        } catch (InsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'Please try with outside city for this location.', 'code' => 700]);
        } catch (OutsideCityPickUpAddressNotFoundException $e) {
            return api_response($request, null, 400, ['message' => 'This service isn\'t available at this location.', 'code' => 701]);
        } catch (DestinationCitySameAsPickupException $e) {
            return api_response($request, null, 400, ['message' => 'Please try with inside city for this location.', 'code' => 702]);
        } catch (HyperLocationNotFoundException $e) {
            return api_response($request, null, 200, ['message' => $e->getMessage(), 'code' => $e->getCode()]);
        }

    }

    public function getPickupAndDestinationThana(Request $request, FromGeo $fromGeo)
    {
        $pickup_lat = $request->pickup_lat;
        $pickup_lng = $request->pickup_lng;
        $destination_lat = $request->destination_lat;
        $destination_lng = $request->destination_lng;
        $fromGeo->setThanas();

        $pickup_thana = ($pickup_lat && $pickup_lng) ? $fromGeo->getThana($pickup_lat, $pickup_lng) : null;
        $pickup_thana = $pickup_thana ? [
            'id' => $pickup_thana->id,
            'name' => $pickup_thana->name,
            'location_id' => $pickup_thana->location_id,
            'lat' => $pickup_thana->lat,
            'lng' => $pickup_thana->lng,
        ] : null;
        $destination_thana = ($destination_lat && $destination_lng) ? $fromGeo->getThana($destination_lat, $destination_lng) : null;
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
