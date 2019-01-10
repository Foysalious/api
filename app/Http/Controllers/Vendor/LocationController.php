<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CityTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use App\Transformers\CustomSerializer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    use Helpers;

    public function index(Request $request)
    {
        try {
            $cities = $this->api->get('v2/locations');
            if ($cities) {
                $fractal = new Manager();
                $fractal->setSerializer(new CustomSerializer());
                $resource = new Collection($cities, new CityTransformer());
                return response()->json($fractal->createData($resource)->toArray());
            } else {
                app('sentry')->captureException(new \Exception('partner fetch wrong'));
                return response()->json(['data' => null]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}