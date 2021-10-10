<?php namespace App\Http\Controllers;

use App\Models\CustomerFavorite;
use App\Models\HyperLocal;
use App\Models\Job;
use App\Models\Location;
use Sheba\Dal\LocationService\LocationService;
use Sheba\Dal\Service\Service;
use App\Transformers\ServiceV2DeliveryChargeTransformer;
use App\Transformers\ServiceV2MinimalTransformer;
use App\Transformers\ServiceV2Transformer;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use DB;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Checkout\DeliveryCharge;
use Sheba\JobDiscount\JobDiscountHandler;
use Sheba\Location\LocationSetter;
use Sheba\LocationService\PriceCalculation;
use Sheba\LocationService\UpsellCalculation;
use Sheba\Order\Policy\FavoriteService;
use Throwable;

class CustomerFavoriteController extends Controller
{
    use LocationSetter;

    public function index($customer, Request $request, FavoriteService $favoriteService,
                          PriceCalculation $price_calculation, DeliveryCharge $delivery_charge,
                          JobDiscountHandler $job_discount_handler, UpsellCalculation $upsell_calculation, ServiceV2MinimalTransformer $service_transformer)
    {
        $this->validate($request, [
            'location' => 'sometimes|numeric', 'lat' => 'sometimes|numeric', 'lng' => 'required_with:lat'
        ]);
        $customer = $request->customer;
        list($offset, $limit) = calculatePagination($request);
        if (!$this->location) $this->location = 4;
        $customer->load([
            'favorites' => function ($q) use ($offset, $limit) {
                $q->whereHas('services', function ($q) {
                    $q->published()->whereHas('locations', function ($q) {
                        $q->where('locations.id', $this->location);
                    });
                });
                $q->whereHas('category', function ($q) {
                    $q->published()->whereHas('locations', function ($q) {
                        $q->where('locations.id', $this->location);
                    });
                });
                $q->with([
                    'job', 'services', 'partner' => function ($q) {
                        $q->select('id', 'name', 'logo');
                    }, 'category' => function ($q) {
                        if ($this->location) $q->select('id', 'parent_id', 'name', 'slug', 'icon_png', 'icon_color', 'delivery_charge', 'min_order_amount', 'max_order_amount', 'is_vat_applicable')->with('parent');
                    }
                ])->orderBy('id', 'desc')->skip($offset)->take($limit);
            }
        ]);

        /** @var Manager $manager */
        $manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $favorites = $customer->favorites->each(function (&$favorite, $key) use ($manager, $favoriteService, $price_calculation, $delivery_charge, $job_discount_handler, $upsell_calculation, $service_transformer) {
            $services = [];
            $favorite['category_name'] = $favorite->category->name;
            $favorite['category_slug'] = $favorite->category->slug;
            $favorite['category_icon'] = $favorite->category->icon_png;
            $favorite['master_category_id'] = $favorite->category && $favorite->category->master ? $favorite->category->master->id : null;
            $favorite['master_category_name'] = $favorite->category && $favorite->category->master ? $favorite->category->master->name : null;
            $favorite['min_order_amount'] = $favorite->category->min_order_amount;
            $favorite['icon_color'] = isset(config('sheba.category_colors')[$favorite->category->parent->id]) ? config('sheba.category_colors')[$favorite->category->parent->id] : null;
            $favorite['rating'] = $favorite->job->review ? $favorite->job->review->rating : 0.00;
            $favorite['is_vat_applicable'] = $favorite->category ? $favorite->category['is_vat_applicable'] : null;
            $favorite['max_order_amount'] = $favorite->category ? (double)$favorite->category['max_order_amount'] : null;
            $location_services = LocationService::where('location_id', $this->location)
                ->whereIn('service_id', $favorite->services->pluck('id')->toArray())->get();
            $favorite->services->each(function ($service) use ($favorite, &$services, $manager, $location_services, $price_calculation, $delivery_charge, $job_discount_handler, $upsell_calculation, $service_transformer) {
                $location_service = $location_services->where('service_id', $service->id)->first();
                $pivot = $service->pivot;
                $upsell_calculation->setService($service)
                    ->setLocationService($location_service)
                    ->setOption(json_decode($pivot->option, true))
                    ->setQuantity($pivot->quantity);
                $upsell_price = $upsell_calculation->getAllUpsellWithMinMaxQuantity();

                $selected_service = [
                    "option" => json_decode($pivot->option, true),
                    "variable_type" => $pivot->variable_type
                ];
                if ($location_service) {
                    $service_transformer->setLocationService($location_service);
                    if ($pivot->variable_type != $location_service->service->variable_type) $favorite['is_same_service'] = 0;
                }
                $resource = new Item($selected_service, $service_transformer);
                $price_data = $manager->createData($resource)->toArray();

                $pivot['variables'] = json_decode($pivot['variables']);
                $pivot['picture'] = $service->thumb;
                $pivot['unit'] = $service->unit;
                $pivot['min_quantity'] = $service->min_quantity;
                $pivot['app_thumb'] = $service->app_thumb;
                $pivot['publication_status'] = $service->publication_status;
                $pivot['upsell_price'] = $upsell_price;

                $service_data_with_price_and_discount = $pivot->toArray() + $price_data;

                array_push($services, $service_data_with_price_and_discount);
            });;
            $partner = $favorite->partner;
            $favorite['total_price'] = $favorite->total_price;
            $favorite['partner_id'] = $partner ? $partner->id : null;
            $favorite['partner_name'] = $partner ? $partner->name : null;
            $favorite['partner_logo'] = $partner ? $partner->logo : null;
            $favorite['is_same_service'] = $favoriteService->setFavoriteServices($favorite->services)->canOrder();
            $favorite['is_inspection_service'] = $favorite->services[0]->is_inspection_service;

            $resource = new Item($favorite->category,
                new ServiceV2DeliveryChargeTransformer($delivery_charge, $job_discount_handler, Location::find($this->location))
            );
            $delivery_charge_discount_data = $manager->createData($resource)->toArray();
            $favorite['delivery_charge'] = $delivery_charge_discount_data['delivery_charge'];
            $favorite['delivery_discount'] = $delivery_charge_discount_data['delivery_discount'];

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
            if ($request->job_id) {
                $job = Job::find($request->job_id);
                $response = $this->saveFromJOb($job, $request->customer);
            } else {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
            return api_response($request, null, 500);
        }
    }
}
