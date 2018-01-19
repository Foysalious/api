<?php

namespace App\Http\Controllers;

use App\Models\CustomerFavorite;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;

class CustomerFavoriteController extends Controller
{
    public function store($customer, Request $request)
    {
        try {
            if ($response = $this->save(json_decode($request->data), $request->customer)) {
                return api_response($request, null, 200);
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
                    $favorite = new CustomerFavorite(['category_id' => $category->category, 'name' => $category->name, 'additional_info' => $category->additional_info]);
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
            if ($service->parent_category == (int)$favorite->category_id) {
                $favorite->services()->attach($service->id, [
                    'name' => $service->name, 'variable_type' => $service->variable_type,
                    'variables' => $service->isOptions() ? $service->getVariablesOfOptionsService($service_info->option) : '[]',
                    'option' => json_encode($service_info->option), 'quantity' => $service_info->quantity
                ]);
            }
        }
    }
}