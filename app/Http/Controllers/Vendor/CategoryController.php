<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CategoryTransformer;
use App\Transformers\CustomSerializer;
use App\Transformers\ServiceTransformer;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class CategoryController extends Controller
{
    use Helpers;

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $categories = $this->api->get('/v1/categories?location=' . $request->locatrion . '&lat=' . $request->lat . '&lng=' . $request->lng);
            $fractal = new Manager();
            $resource = new Collection($categories, new CategoryTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }

    public function get($category, Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $category = $this->api->get('/v1/categories/' . $category . '/secondaries?location=' . $request->locatrion . '&lat=' . $request->lat . '&lng=' . $request->lng);
            $categories = $category['secondaries'];
            $fractal = new Manager();
            $resource = new Collection($categories, new CategoryTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }

    public function getServices($category, Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $category = $this->api->get('/v1/categories/' . $category . '/services?location=' . $request->location . '&lat=' . $request->lat . '&lng=' . $request->lng);
            $services = $category['services'];
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($services, new ServiceTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }
}