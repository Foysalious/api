<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CategoryTransformer;
use App\Transformers\CustomSerializer;
use App\Transformers\ServiceTransformer;
use GuzzleHttp\Client;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v1/categories');
            $data = json_decode($response->getBody());
            $categories = $data->categories;
            $fractal = new Manager();
            $resource = new Collection($categories, new CategoryTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            return response()->json(['data' => null]);
        }
    }

    public function get($category)
    {
        try {
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v1/categories/' . $category . '/secondaries');
            $data = json_decode($response->getBody());
            $category = $data->category;
            $categories = $category->secondaries;
            $fractal = new Manager();
            $resource = new Collection($categories, new CategoryTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            return response()->json(['data' => null]);
        }
    }

    public function getServices($category)
    {
        try {
            $client = new Client();
            $response = $client->get(config('sheba.api_url') . '/v1/categories/' . $category . '/services');
            $data = json_decode($response->getBody());
            $category = $data->category;
            $services = $category->services;
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($services, new ServiceTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            dd($e);
            return response()->json(['data' => null]);
        }
    }
}