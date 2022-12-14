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
        $this->validate($request, [
            'location' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat'
        ]);
        $categories = $this->api->get('/v1/categories?location=' . $request->locatrion . '&lat=' . $request->lat . '&lng=' . $request->lng);
        if (!$categories) return response()->json(['data' => null]);
        $fractal = new Manager();
        $resource = new Collection($categories, new CategoryTransformer());
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function get($category, Request $request)
    {
        $this->validate($request, [
            'location' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat'
        ]);
        $category = $this->api->get('/v1/categories/' . $category . '/secondaries?location=' . $request->locatrion . '&lat=' . $request->lat . '&lng=' . $request->lng);
        if (!$category || !isset($category['secondaries'])) return response()->json(['data' => null]);
        $categories = $category['secondaries'];
        $fractal = new Manager();
        $resource = new Collection($categories, new CategoryTransformer());
        return response()->json($fractal->createData($resource)->toArray());
    }

    public function getServices($category, Request $request)
    {
        $this->validate($request, [
            'location' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'required_with:lat'
        ]);
        $category = $this->api->get('/v1/categories/' . $category . '/services?location=' . $request->location . '&lat=' . $request->lat . '&lng=' . $request->lng);
        if (!$category || !isset($category['services'])) return response()->json(['data' => null]);
        $services = $category['services'];
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Collection($services, new ServiceTransformer());
        return response()->json($fractal->createData($resource)->toArray());
    }
}