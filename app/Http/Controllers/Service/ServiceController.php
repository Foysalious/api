<?php namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use App\Models\LocationService;
use App\Models\Service;
use App\Transformers\Service\ServiceTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;

class ServiceController extends Controller
{
    /**
     * @param $service
     * @param Request $request
     * @param ServiceTransformer $service_transformer
     * @return JsonResponse
     */
    public function show($service, Request $request, ServiceTransformer $service_transformer)
    {
        $this->validate($request, ['lat' => 'required|numeric', 'lng' => 'required|numeric']);
        $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
        if (!$hyper_location)
            return api_response($request, null, 404);

        /** @var Service $service */
        $service = Service::find($service);
        if (!$service) return api_response($request, null, 404);
        $location_service = LocationService::where('location_id', $hyper_location->location_id)->where('service_id', $service->id)->first();
        if (!$location_service)
            return api_response($request, null, 404);

        $fractal = new Manager();
        $service_transformer->setLocationService($location_service);
        $resource = new Item($service, $service_transformer);
        $data = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $data, 200, ['service' => $data]);
    }
}
