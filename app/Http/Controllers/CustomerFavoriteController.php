<?php

namespace App\Http\Controllers;

use App\Models\CustomerFavorite;
use App\Models\Job;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;

class CustomerFavoriteController extends Controller
{
    public function index($customer, Request $request)
    {
        $customer = $request->customer;
        list($offset, $limit) = calculatePagination($request);
        $customer->load(['favorites' => function ($q) use($offset, $limit) {
            $q->with(['services', 'partner' => function ($q) {
                $q->select('id', 'name', 'logo');
            }, 'category' => function ($q) {
                $q->select('id', 'name', 'slug', 'icon', 'icon_color');
            }])->skip($offset)->take($limit);
        }]);
        $favorites = $customer->favorites->each(function (&$favorite, $key) {
            $services = [];
            $favorite['category_name'] = $favorite->category->name;
            $favorite['category_slug'] = $favorite->category->slug;
            $favorite['category_icon'] = $favorite->category->icon;
            $favorite['icon_color'] = $favorite->category->icon_color;
            $favorite->services->each(function ($service) use ($favorite, &$services) {
                $pivot = $service->pivot;
                $pivot['variables'] = json_decode($pivot['variables']);
                $pivot['picture'] = $service->thumb;
                $pivot['unit'] = $service->unit;
                $pivot['app_thumb'] = $service->app_thumb;
                array_push($services, $pivot);
            });
            $partner = $favorite->partner;
            $favorite['total_price'] = $favorite->total_price;
            $favorite['partner_id'] = $partner ? $partner->id : null;
            $favorite['partner_name'] = $partner ? $partner->name : null;
            $favorite['partner_logo'] = $partner ? $partner->logo : null;
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
            $this->validate($request, [
                'job_id' => 'unique:customer_favourites'
            ]);
            if ($request->job_id){
                $job = Job::find($request->job_id);
                $response = $this->saveFromJOb($job, $request->customer);
            } else{
                $data = json_decode($request->data);
                foreach ($data as $category) {
                    if (count($category->services) == 0) {
                        return api_response($request, null, 400);
                    }
                }
                $response = $this->save(json_decode($request->data), $request->customer);
            }

            if ($response) {
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
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

    private function saveFromJOb(Job $job, $customer)
    {
        try {
            DB::transaction(function () use ($job, $customer) {
                $favorite = new CustomerFavorite([
                    'category_id' => (int)$job->category_id,
                    'name' => $job->category->name,
                    'job_id' => $job->id,
                    'partner_id' => $job->partnerOrder->partner_id,
                    'additional_info' => $job->job_additional_info,
                    'preferred_time' => $job->preferred_time,
                    'schedule_date' => $job->schedule_date,
                    'total_price' => $job->partnerOrder->calculate(true)->totalPrice,
                    'delivery_address_id' => $job->partnerOrder->order->delivery_address_id ?: $job->partnerOrder->order->findDeliveryIdFromAddressString(),
                    'location_id' => $job->partnerOrder->order->location_id,

                ]);
                $customer->favorites()->save($favorite);
                $this->saveServicesFromJobServices($favorite, $job->jobServices);
            });
            return true;
        } catch (QueryException $e) {
            return false;
        }
    }

    private function saveServicesFromJobServices($favorite, $job_services)
    {
        foreach ($job_services as $job_service) {
            $favorite->services()->attach($job_service->service_id, [
                'name' => $job_service->name,
                'variable_type' => $job_service->variable_type,
                'variables' => $job_service->variables,
                'option' => $job_service->option,
                'quantity' => $job_service->quantity
            ]);
        }
    }

    private function saveServices($favorite, $services)
    {
        foreach ($services as $service_info) {
            $service = Service::published()->where('id', (int)$service_info->id)->first();
            if ($service->category_id == (int)$favorite->category_id) {
                $favorite->services()->attach($service->id, [
                    'name' => $service->name,
                    'variable_type' => $service->variable_type,
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