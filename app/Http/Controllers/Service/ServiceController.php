<?php namespace App\Http\Controllers\Service;


use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\Location;
use App\Models\LocationService;
use App\Models\Service;
use App\Transformers\Service\ServiceTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Checkout\DeliveryCharge;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;

class ServiceController extends Controller
{
    /**
     * @todo Code refactor
     */
    public function show($service, Request $request, ServiceTransformer $service_transformer, PriceCalculation $price_calculation, UpsellCalculation $upsell_calculation, DeliveryCharge $delivery_charge, JobDiscountHandler $job_discount_handler)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $hyperLocation = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        if (!$hyperLocation) return api_response($request, null, 404);
        /** @var Service $service */
        $service = Service::find($service);
        if (!$service) return api_response($request, null, 404);
        /** @var Location $location */
        $location = $hyperLocation->location;
        $location_service = LocationService::where('location_id', $location->id)->where('service_id', $service->id)->first();
        $fractal = new Manager();
        $service_transformer->setLocationService($location_service);
        $resource = new Item($service, $service_transformer);
        $data = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, $data, 200, ['service' => $data]);
    }

    /**
     * @param PriceCalculation $price_calculation
     * @param $prices
     * @param UpsellCalculation $upsell_calculation
     * @param LocationService $location_service
     * @return Collection
     */
    private function formatOptionWithPrice(PriceCalculation $price_calculation, $prices,
                                           UpsellCalculation $upsell_calculation, LocationService $location_service)
    {
        $options = collect();
        foreach ($prices as $key => $price) {
            $option_array = explode(',', $key);
            $options->push([
                'option' => collect($option_array)->map(function ($key) {
                    return (int)$key;
                }),
                'price' => $price_calculation->setOption($option_array)->getUnitPrice(),
                'upsell_price' => $upsell_calculation->setOption($option_array)->getAllUpsellWithMinMaxQuantity()
            ]);
        }
        return $options;
    }

}