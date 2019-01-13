<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CategoryTransformer;
use App\Transformers\CustomSerializer;
use App\Transformers\ServiceTransformer;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'location' => 'sometimes|numeric',
                'lat' => 'sometimes|numeric',
                'lng' => 'required_with:lat'
            ]);
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v1/categories?location=' . $request->locatrion . '&lat=' . $request->lat . '&lng=' . $request->lng);
            $data = json_decode($response->getBody());
            $categories = $data->categories;
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
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v1/categories/' . $category . '/secondaries?location=' . $request->locatrion . '&lat=' . $request->lat . '&lng=' . $request->lng);
            $data = json_decode($response->getBody());
            $category = $data->category;
            $categories = $category->secondaries;
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
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v1/categories/' . $category . '/services?location=' . $request->location . '&lat=' . $request->lat . '&lng=' . $request->lng);
            $data = json_decode($response->getBody());
            $category = $data->category;
            $services = $category->services;
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