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
use Carbon\Carbon;
use Sheba\Dal\JobService\JobService;
use Sheba\Jobs\JobStatuses;
use DB;

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
        $date_start = Carbon::now()->subMonth(6);

        $service_orders = JobService::join('jobs as j1','j1.id', '=', 'job_id')
            ->where('job_service.service_id',$service->id)
            ->where('job_service.created_at','>=',$date_start)
            ->where('j1.status', JobStatuses::SERVED)
            ->selectRaw("count(*) as count")->first()->count;

        $fractal = new Manager();
        if($location_service) $service_transformer->setLocationService($location_service);
        else return api_response($request, null, 404);
        $resource = new Item($service, $service_transformer);
        $data = $fractal->createData($resource)->toArray()['data'];
        $data['order_count'] = $service_orders;

        return api_response($request, $data, 200, ['service' => $data]);
    }

    public function getSuggestions(Request $request)
    {
        $categories = Service::published()->select('id', 'name', 'bn_name')->get();

        return count($categories) > 0 ? api_response($request, $categories, 200, ['service' => $categories]) : api_response($request, null, 404);
    }
}
