<?php namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\HyperLocal;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
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

        /** @var Service $service */
        $service = Service::find($service);
        if (!$service) return api_response($request, null, 404);

        $hyper_location = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();

        if($hyper_location) $location_service = LocationService::where('location_id', $hyper_location->location_id)->where('service_id', $service->id)->first();

        $fractal = new Manager();
        if($location_service) $service_transformer->setLocationService($location_service);
        $resource = new Item($service, $service_transformer);
        $data = $fractal->createData($resource)->toArray()['data'];

        return api_response($request, $data, 200, ['service' => $data]);
    }

    public function getSuggestions(Request $request)
    {
        $categories = Service::published()->select('id', 'name', 'bn_name')->get();

        return count($categories) > 0 ? api_response($request, $categories, 200, ['service' => $categories]) : api_response($request, null, 404);
    }
}
