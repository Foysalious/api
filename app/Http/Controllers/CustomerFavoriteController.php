<?php

namespace App\Http\Controllers;

use App\Models\CustomerFavorite;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;

class CustomerFavoriteController extends Controller
{
    public function index($customer, Request $request)
    {
        $customer = $request->customer;
        $customer->load(['favorites' => function ($q) {
            $q->with(['services', 'category' => function ($q) {
                $q->select('id', 'name', 'slug');
            }]);
        }]);
        $favorites = $customer->favorites->each(function (&$favorite, $key) {
            $services = [];
            $favorite['category_name'] = $favorite->category->name;
            $favorite['category_slug'] = $favorite->category->slug;
            $favorite->services->each(function ($service) use ($favorite, &$services) {
                $pivot = $service->pivot;
                $pivot['variables'] = json_decode($pivot['variables']);
                $pivot['picture'] = $service->thumb;
                $pivot['unit'] = $service->unit;
                array_push($services, $pivot);
            });
            removeRelationsAndFields($favorite);
            $favorite['services'] = $services;
        });
        if (count($customer->favorites) > 0) {
            return api_response($request, null, 200, ['favorites' => $favorites]);
        } else {
            return api_response($request, null, 404);
        }
    }

    public function store($customer, Request $request)
    {
        try {
            if ($response = $this->save(json_decode($request->data), $request->customer)) {
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function save($data, $customer)
    {
        try {
            DB::transaction(function () use ($data, $customer) {
                foreach ($data as $category) {
                    $favorite = new CustomerFavorite(['category_id' => (int)$category->category, 'name' => $category->name, 'additional_info' => $category->additional_info]);
                    $customer->favorites()->save($favorite);
                    $this->saveServices($favorite, $category->services);
                }
            });
            return true;
        } catch (QueryException $e) {
            return false;
        }
    }

    private function saveServices($favorite, $services)
    {
        foreach ($services as $service_info) {
            $service = Service::published()->where('id', (int)$service_info->id)->first();
            if ($service->category_id == (int)$favorite->category_id) {
                $favorite->services()->attach($service->id, [
                    'name' => $service->name, 'variable_type' => $service->variable_type,
                    'variables' => $service->isOptions() ? $service->getVariablesOfOptionsService($service_info->option) : '[]',
                    'option' => json_encode($service_info->option),
                    'quantity' => (double)$service->min_quantity <= (double)$service_info->quantity ? $service_info->quantity : $service->min_quantity
                ]);
            }
        }
    }

    public function update($customer, Request $request)
    {
        try {
            $favorites = json_decode($request->data);
            if ($response = $this->updateFavorite($customer, $favorites)) {
                return api_response($request, null, 200);
            } else {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function updateFavorite($customer, $favorites)
    {
        try {
            DB::transaction(function () use ($favorites, $customer) {
                foreach ($favorites as $favorite) {
                    $customer_favorite = CustomerFavorite::where([['id', $favorite->id], ['customer_id', (int)$customer]])->first();
                    if ($customer_favorite) {
                        $customer_favorite->name = $favorite->name;
                        $customer_favorite->additional_info = $favorite->additional_info;
                        $customer_favorite->update();
                        $this->updateServices($customer_favorite, $favorite->services);
                    }
                }
            });
            return true;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function updateServices($customer_favorite, $services)
    {
        foreach ($services as $service_info) {
            if (isset($service_info->favorite_service)) {
                $service = $customer_favorite->services()->where('customer_favourite_service.id', $service_info->favorite_service)->first();
                if ($service) {
                    if ($service->category_id == (int)$customer_favorite->category_id) {
                        $service->pivot->name = $service->name;
                        $service->pivot->variable_type = $service->variable_type;
                        $service->pivot->variables = $service->isOptions() ? $service->getVariablesOfOptionsService($service_info->option) : '[]';
                        $service->pivot->option = json_encode($service_info->option);
                        $service->pivot->quantity = (double)$service->min_quantity <= (double)$service_info->quantity ? $service_info->quantity : $service->min_quantity;
                        $service->pivot->update();
                    }
                }
            } else {
                $this->saveServices($customer_favorite, [$service_info]);
            }
        }
    }

    public function destroy($customer, $favorite, Request $request)
    {
        try {
            $customer_favorite = CustomerFavorite::where([['id', $favorite], ['customer_id', (int)$customer]])->first();
            if ($customer_favorite) {
                $customer_favorite->delete();
                return api_response($request, null, 200);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

}