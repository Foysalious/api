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

        $location_service = $hyper_location ? LocationService::where('location_id', $hyper_location->location_id)->where('service_id', $service->id)->first() : null;

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

    public function instructions(Request $request, $serviceId)
    {
        $service = Service::select('id', 'name', 'description_bn')->find($serviceId);
        if (!$service) return api_response($request, '', 404, ['message' => 'Service not found']);
        $instructions = $this->getServiceInstructions($service);

        $data = [
            'instructions' => $instructions
        ];
        return api_response($request, $data, 200, ['data' => $data]);
    }

    private function getServiceInstructions(Service $service) : array
    {
        $instructions[] = config('spro.instructions.work_start');

        $serviceDescriptionBn = json_decode($service->description_bn);
        if (count($serviceDescriptionBn) > 0) {
            config()->set('spro.instructions.service_details.list', $serviceDescriptionBn);
            $instructions[] = config('spro.instructions.service_details');
        }

        $instructions[] = config('spro.instructions.work_end');
        return $instructions;
    }
}
